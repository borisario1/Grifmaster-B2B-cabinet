<x-filament-panels::page>

    <div wire:poll.3s>
        @if($this->activeLog)
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <x-filament::loading-indicator class="h-5 w-5 text-primary-500" />
                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                            Выполняется диагностика: <span class="text-primary-600">{{ $this->activeLog->command }}</span>
                        </h3>
                    </div>
                </div>

                @php
                    $percent = 0;
                    $hasProgress = $this->activeLog->progress_max > 0;
                    if ($hasProgress) {
                        $percent = round(($this->activeLog->progress_current / $this->activeLog->progress_max) * 100);
                    }
                @endphp
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4 overflow-hidden">
                    <div class="bg-primary-600 h-2.5 rounded-full {{ $hasProgress ? 'transition-all duration-500 ease-out' : 'animate-pulse' }}" 
                         style="width: {{ $hasProgress ? $percent . '%' : '100%' }}"></div>
                </div>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-hidden shadow-inner leading-relaxed max-h-64 overflow-y-auto">
                    @php
                        $lines = explode("\n", $this->activeLog->output ?? '');
                        // Показываем больше строк для тестов
                        $tail = array_slice($lines, -15);
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

    {{ $this->table }}
</x-filament-panels::page>
