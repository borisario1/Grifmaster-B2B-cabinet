<?php

/**
 * Название: DashboardController.php
 * Дата-время: 28-12-2025 12:00
 * Описание: Контроллер главной страницы (Дашборд).
 * Отвечает за сбор данных для отображения: меню, статус организации, приветствие.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;

class DashboardController extends Controller
{
    /**
     * Главная страница личного кабинета
     */
    public function index()
    {
        $user = Auth::user();
        $menu = config('b2b_menu');

        // 1. Получаем объект текущей организации (если ID выбран)
        $currentOrg = $user->selected_org_id 
            ? $user->organizations()->find($user->selected_org_id) 
            : null;

        // 2. Статус организации (передаем уже найденную орг, чтобы не искать дважды)
        $orgStatus = $this->getOrgStatus($user, $currentOrg);

        // 3. Логика даты входа
        $prevLoginDate = $user->previous_login;
        $lastLoginText = $prevLoginDate 
            ? \Carbon\Carbon::parse($prevLoginDate)->timezone('Europe/Moscow')->format('d.m.Y в H:i') 
            : 'только что';

        // 4. Роль
        $roleNames = ['admin' => 'Администратор', 'manager' => 'Менеджер', 'partner' => 'Партнёр'];
        $roleName = $roleNames[$user->role] ?? $user->role;

        return view('dashboard', [
            'menu'          => $menu,
            'org_status'    => $orgStatus,
            'currentOrg'    => $currentOrg,
            'lastLoginText' => $lastLoginText,
            'roleName'      => $roleName
        ]);
    }

    /**
     * Вспомогательный метод для формирования текста статуса
     */
    private function getOrgStatus($user, $currentOrg) // Добавили аргумент $currentOrg
    {
        // 1. Считаем общее кол-во компаний
        $count = $user->organizations()->count();

        if ($count === 0) {
            $url = route('organizations.create');
            return [
                'state' => 'no_org',
                'text'  => '<a class="link-default" href="' . $url . '">Создайте организацию</a> для использования всех возможностей сервиса.',
            ];
        }

        // 2. Если нет выбранной организации (или она не найдена в базе)
        if (!$currentOrg) {
            $url = route('organizations.index');
            return [
                'state' => 'need_select',
                'text'  => '<a class="link-default" href="' . $url . '">Выберите организацию</a> чтобы создавать заказы, получать документы и пользоваться всеми возможностями сервиса.',
            ];
        }

        // 3. Если всё хорошо
        return [
            'state' => 'selected',
            'text'  => '', // Текст пустой, так как алерт мы скроем
        ];
    }

}