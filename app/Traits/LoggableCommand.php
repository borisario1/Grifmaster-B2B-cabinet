<?php

namespace App\Traits;

use App\Models\CommandLog;
use Illuminate\Support\Facades\Log;

trait LoggableCommand
{
    protected ?CommandLog $commandLog = null;
    protected array $logBuffer = [];

    protected function initLog(): void
    {
        $logId = $this->option('log-id');
        if ($logId) {
            $this->commandLog = CommandLog::find($logId);
            if ($this->commandLog) {
                $this->commandLog->update([
                    'status' => 'running', 
                    'started_at' => now(),
                    'pid' => getmypid()
                ]);
            }
        }
    }

    protected function logToDb(string $message, string $type = 'info'): void
    {
        // Выводим в стандартную консоль
        $this->line($message);

        // Если есть лог в БД — пишем туда
        if ($this->commandLog) {
            // Добавляем timestamp
            $line = "[" . now()->format('H:i:s') . "] " . strip_tags($message);
            
            // Можно писать сразу, или буферизировать.
            // Для "визуализации процесса" лучше писать сразу, но это нагрузка.
            // Будем дописывать в output.
            
            // Внимание:  append к longtext полям может быть накладным.
            // Но для наших объемов (сотни строк) сойдет.
            
            $currentOutput = $this->commandLog->refresh()->output ?? '';
            $this->commandLog->update([
                'output' => $currentOutput . $line . "\n"
            ]);
        }
    }

    protected function finishLog(bool $success, string $errorMessage = null): void
    {
        if ($this->commandLog) {
            $this->commandLog->update([
                'status' => $success ? 'success' : 'failed',
                'finished_at' => now(),
                'error' => $errorMessage
            ]);
        }
    }

    protected function setProgress(int $current, int $max): void
    {
        if ($this->commandLog) {
            // Чтобы не долбить БД, обновляем только если изменился процент существенно или каждые N записей
            // Но для красоты демо прогрессбара будем обновлять часто (или рассчитывать от шага)
            $this->commandLog->update([
                'progress_current' => $current,
                'progress_max' => $max > 0 ? $max : 100 // Защита от деления на 0
            ]);
        }
    }
}
