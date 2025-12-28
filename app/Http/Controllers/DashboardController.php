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

        // 1. Получаем меню
        $menu = config('b2b_menu');

        // 2. Статус организации
        $orgStatus = $this->getOrgStatus($user);

        // 3. Логика даты предыдущего входа
        $prevLoginDate = $user->previous_login;
        if ($prevLoginDate) {
            $lastLoginText = \Carbon\Carbon::parse($prevLoginDate)
                ->timezone('Europe/Moscow')
                ->format('d.m.Y в H:i');
        } else {
            $lastLoginText = 'только что';
        }

        // 4. Человекопонятное название роли
        $roleNames = [
            'admin'   => 'Администратор',
            'manager' => 'Менеджер',
            'partner' => 'Партнёр'
        ];
        $roleName = $roleNames[$user->role] ?? $user->role;

        // Отдаем всё во вьюху
        return view('dashboard', [
            'menu'          => $menu,
            'org_status'    => $orgStatus,
            'lastLoginText' => $lastLoginText,
            'roleName'      => $roleName
        ]);
    }

    /**
     * Вспомогательный метод для формирования текста статуса
     */
    private function getOrgStatus($user)
    {
        // Считаем количество организаций пользователя
        // Используем связь organizations(), чтобы Eloquent сам учел user_id
        $count = $user->organizations()->count();

        if ($count === 0) {
            $url = route('organizations.create');
            return [
                'state' => 'no_org',
                'text'  => '<a href="' . $url . '">Создайте организацию</a> для использования всех возможностей сервиса.',
            ];
        }

        // Проверяем, выбрана ли организация в профиле
        $selectedId = $user->selected_org_id;

        if (!$selectedId) {
            $url = route('organizations.index');
            return [
                'state' => 'need_select',
                'text'  => '<a href="' . $url . '">Выберите организацию</a> чтобы создавать заказы, получать документы и пользоваться всеми возможностями сервиса.',
            ];
        }

        // Пытаемся найти эту организацию среди организаций пользователя
        // Метод find() внутри связи organizations() гарантирует, 
        // что мы не найдем чужую организацию, даже если подставим чужой ID.
        $org = $user->organizations()->find($selectedId);

        // Если организация не найдена (например, была удалена или ID неверный)
        if (!$org) {
            return [
                'state' => 'need_select',
                'text'  => 'Выберите организацию для использования сервиса.',
            ];
        }

        // Формируем текст уведомления
        // Если ИП -> показываем ОГРН, Если Юрлицо -> КПП
        $extra = ($org->type === 'ip') 
            ? 'ОГРНИП: ' . ($org->ogrn ?: '—') 
            : 'КПП: ' . ($org->kpp ?: '—');

        return [
            'state' => 'selected',
            'text'  => "Выбрана организация: <strong>{$org->name}</strong>, ИНН: {$org->inn}, {$extra}",
        ];
    }
}