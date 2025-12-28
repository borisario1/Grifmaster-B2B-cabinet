<?php

/**
 * Название: OrganizationController
 * Дата-время: 27-12-2025 22:10
 * Описание: Управление организациями пользователя.
 * Логика полностью соответствует legacy-файлам (Organizations.php, create.php, index.php).
 */

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\DadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * 1. Список организаций (Мои организации)
     */
    public function index()
    {
        $user = Auth::user();

        // Загружаем организации.
        // Сортировка: сначала выбранная, потом новые. (раскомментировать 2 строки ->orderByRaw)
        // Пока временно без сортировок организаций. ->get();
        $organizations = $user->organizations()
            //->orderByRaw("id = ? DESC", [$user->selected_org_id])
            ->latest()
            ->get();

        return view('organizations.index', compact('organizations'));
    }

    /**
     * 2. Форма добавления организации
     */
    public function create()
    {
        return view('organizations.create');
    }

    /**
     * 3. Умный поиск по ИНН (AJAX)
     */
    public function lookup(Request $request, DadataService $dadataService)
    {
        $inn = trim($request->input('inn'));

        if (!$inn) {
            return response()->json(['ok' => false, 'error' => 'Введите ИНН']);
        }

        // А. Локальный кэш (оставляем как было)
        $cachedInfo = \App\Models\OrganizationInfo::whereHas('organization', function($q) use ($inn) {
                $q->where('inn', $inn);
            })
            ->latest()
            ->first();

        if ($cachedInfo && !empty($cachedInfo->dadata_raw)) {
            $orgName = $cachedInfo->organization->name ?? $cachedInfo->name_full;
            return response()->json([
                'ok' => true,
                'source' => 'local',
                'suggestions' => [[
                    'value' => $orgName,
                    'unrestricted_value' => $cachedInfo->name_full,
                    'data' => $cachedInfo->dadata_raw 
                ]]
            ]);
        }

        // Б. DaData + ФИЛЬТРАЦИЯ
        try {
            $allSuggestions = $dadataService->findByInn($inn);
            
            // Фильтруем: оставляем только ДЕЙСТВУЮЩИЕ (ACTIVE)
            $activeSuggestions = array_values(array_filter($allSuggestions, function($item) {
                return isset($item['data']['state']['status']) && $item['data']['state']['status'] === 'ACTIVE';
            }));

            // Если список пуст после фильтрации (значит все были закрыты)
            if (empty($activeSuggestions) && !empty($allSuggestions)) {
                return response()->json(['ok' => false, 'error' => 'По данному ИНН найдены только ликвидированные организации.']);
            }
            
            // Если вообще ничего не нашли
            if (empty($activeSuggestions)) {
                return response()->json(['ok' => false, 'error' => 'Организация не найдена в ФНС.']);
            }

            return response()->json([
                'ok' => true,
                'source' => 'dadata',
                'suggestions' => $activeSuggestions // Возвращаем только активные
            ]);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => 'Сервис поиска временно недоступен.']);
        }
    }

    /**
     * 4. Сохранение новой организации
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Валидация
        // ВАЖНО: Мы НЕ требуем поле 'type', мы его вычисляем сами ниже
        $validated = $request->validate([
            'inn' => [
                'required', 
                'string', 
                'max:12',
                // Уникальность в рамках пользователя (с учетом soft deletes)
                Rule::unique('b2b_organizations')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id)->whereNull('deleted_at');
                })
            ],
            'name' => 'required|string|max:255',
            'kpp' => 'nullable|string|max:20',
            'ogrn' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'dadata_raw' => 'nullable|json' 
        ], [
            'inn.unique' => 'Эта организация уже есть в вашем списке.'
        ]);

        // ЛОГИКА ИЗ Organizations.php: Авто-определение типа по длине ИНН
        $len = strlen($validated['inn']);
        if ($len === 10) {
            $type = 'org';
        } elseif ($len === 12) {
            $type = 'ip';
        } else {
            return back()->withErrors(['inn' => 'Неверная длина ИНН (должно быть 10 или 12 цифр)'])->withInput();
        }

        DB::transaction(function () use ($user, $validated, $type) {
            // 1. Создаем организацию
            $org = $user->organizations()->create([
                'name'    => $validated['name'],
                'inn'     => $validated['inn'],
                'type'    => $type, // Используем вычисленный тип
                'kpp'     => $validated['kpp'] ?? null,
                'ogrn'    => $validated['ogrn'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            // 2. Сохраняем расширенные данные (Info)
            if (!empty($validated['dadata_raw'])) {
                $raw = json_decode($validated['dadata_raw'], true);

                $org->info()->create([
                    'dadata_raw'    => $raw,
                    'status'        => $raw['state']['status'] ?? 'ACTIVE',
                    'branch_type'   => $raw['branch_type'] ?? 'MAIN',
                    'name_full'     => $raw['name']['full_with_opf'] ?? $validated['name'],
                    'address'       => $raw['address']['value'] ?? $validated['address'],
                    'registered_at' => isset($raw['state']['registration_date']) 
                        ? date('Y-m-d', $raw['state']['registration_date'] / 1000) 
                        : null,
                ]);
            }
        });

        return redirect()->route('organizations.index')->with('ok', 'Организация успешно добавлена');
    }

    /**
     * 5. Смена активной организации (Выбор)
     */
    public function select(Request $request, Organization $organization)
    {
        $user = Auth::user();

        // Проверка: это организация текущего пользователя?
        if ($organization->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        // Если сменили организацию — чистим корзину (Бизнес-правило)
        if ($user->selected_org_id !== $organization->id) {
            $user->update(['selected_org_id' => $organization->id]);
            DB::table('b2b_cart_items')->where('user_id', $user->id)->delete();
        }

        return redirect()->back()->with('ok', 'Выбрана организация: ' . $organization->name);
    }

    /**
     * 6. Удаление организации
     */
    public function destroy(Organization $organization)
    {
        $user = Auth::user();

        if ($organization->user_id !== $user->id) {
            abort(403);
        }

        // Если удаляем активную — сбрасываем выбор и чистим корзину
        if ($user->selected_org_id === $organization->id) {
            $user->update(['selected_org_id' => null]);
            DB::table('b2b_cart_items')->where('user_id', $user->id)->delete();
        }

        // Soft Delete
        $organization->delete(); 

        return redirect()->route('organizations.index')->with('ok', 'Организация удалена');
    }
    
    /**
     * 7. Просмотр (Show)
     */
     public function show(Organization $organization)
     {
         if ($organization->user_id !== Auth::id()) {
             abort(403);
         }
         $organization->load('info');
         
         return view('organizations.show', compact('organization'));
     }
}