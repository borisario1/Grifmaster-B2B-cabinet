<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Настройки';
    protected static ?string $navigationGroup = 'Система';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'admin_email' => \App\Models\Setting::get('admin_email'),
            'smtpbz_api_key' => \App\Models\Setting::get('smtpbz_api_key'),
            'smtpbz_from_email' => \App\Models\Setting::get('smtpbz_from_email'),
            'smtpbz_from_name' => \App\Models\Setting::get('smtpbz_from_name'),
        ]);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Forms\Components\Tabs\Tab::make('Общие')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('admin_email')
                                    ->label('Email администратора')
                                    ->helperText('Для уведомлений о системных событиях')
                                    ->email()
                                    ->required(),
                            ]),
                        \Filament\Forms\Components\Tabs\Tab::make('Почта (SMTP.BZ)')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('smtpbz_api_key')
                                    ->label('API Key')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('smtpbz_from_email')
                                    ->label('Email отправителя')
                                    ->email()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('smtpbz_from_name')
                                    ->label('Имя отправителя')
                                    ->required(),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }

        \Filament\Notifications\Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
