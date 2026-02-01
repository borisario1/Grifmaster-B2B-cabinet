<?php

namespace App\Filament\Pages;

use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;

abstract class PlaceholderPage extends Page
{
    protected static string $view = 'filament.pages.placeholder';

    // Скрываем из навигации
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Делаем элемент меню менее ярким (приглушенный)
    public static function getNavigationBadge(): ?string
    {
        return 'Скоро';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }
}
