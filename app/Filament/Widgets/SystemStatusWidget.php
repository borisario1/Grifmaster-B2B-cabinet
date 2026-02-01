<?php

namespace App\Filament\Widgets;

use App\Models\CommandLog;
use Filament\Widgets\Widget;

class SystemStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.system-status-widget';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public ?CommandLog $lastTest = null;
    public string $status = 'unknown';

    public function mount()
    {
        $this->lastTest = CommandLog::where('command', 'like', 'system:test%')
            ->orderBy('id', 'desc')
            ->first();
            
        if ($this->lastTest) {
            $this->status = $this->lastTest->status;
        }
    }
}
