<x-filament-panels::page>
    
    {{-- Блок "Живой" консоли --}}
    <div wire:poll.3s>
        @if($this->activeLog)
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <x-filament::loading-indicator class="h-5 w-5 text-primary-500" />
                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                            Выполняется: <span class="text-primary-600">{{ $this->activeLog->command }}</span>
                        </h3>
                    </div>
                    <span class="text-sm text-gray-500">
                        ID: {{ $this->activeLog->id }} | Начало: {{ $this->activeLog->started_at->format('H:i:s') }}
                    </span>
                </div>

                {{-- Прогресс бар (анимированный) --}}
                @php
                    $percent = 0;
                    if ($this->activeLog->progress_max > 0) {
                        $percent = round(($this->activeLog->progress_current / $this->activeLog->progress_max) * 100);
                    }
                @endphp
                <div class="flex justify-between text-xs mb-1">
                    <span>Выполнено: {{ $this->activeLog->progress_current }} / {{ $this->activeLog->progress_max }}</span>
                    <span>{{ $percent }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4 overflow-hidden">
                    <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-500 ease-out" style="width: {{ $percent }}%"></div>
                </div>

                {{-- Терминальный вывод (последние 5-10 строк) --}}
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-hidden shadow-inner leading-relaxed">
                    @php
                        $lines = explode("\n", $this->activeLog->output ?? '');
                        // Берем последние 8 строк
                        $tail = array_slice($lines, -8);
                    @endphp
                    
                    @foreach($tail as $line)
                        @if(trim($line))
                            <div>> {!! nl2br(e($line)) !!}</div>
                        @endif
                    @endforeach
                    
                    <div class="animate-pulse mt-2">_</div>
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
