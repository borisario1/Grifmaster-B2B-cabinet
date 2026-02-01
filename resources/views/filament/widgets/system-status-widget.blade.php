<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div @class([
                    'flex h-12 w-12 items-center justify-center rounded-full',
                    'bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-green-400' => $status === 'success',
                    'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400' => $status === 'failed',
                    'bg-gray-100 text-gray-600 dark:bg-gray-900/50 dark:text-gray-400' => $status === 'unknown' || $status === 'pending',
                ])>
                    @if($status === 'success')
                        <x-heroicon-o-check class="h-6 w-6" />
                    @elseif($status === 'failed')
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    @else
                        <x-heroicon-o-question-mark-circle class="h-6 w-6" />
                    @endif
                </div>

                <div>
                    <h2 class="text-lg font-bold">Состояние системы</h2>
                    <p class="text-sm text-gray-500">
                        @if($lastTest)
                            Последняя диагностика: {{ $lastTest->created_at->diffForHumans() }}
                        @else
                            Диагностика еще не запускалась
                        @endif
                    </p>
                </div>
            </div>

            <div>
                 <x-filament::button
                    tag="a"
                    href="/management/testing"
                    color="gray"
                 >
                    Перейти к тестам
                 </x-filament::button>
            </div>
        </div>
        
        @if($lastTest && $status === 'failed')
             <div class="mt-4 p-2 bg-red-50 text-red-600 dark:bg-red-900/20 text-xs font-mono rounded overflow-auto max-h-32">
                 {{ Str::limit($lastTest->output, 500) }}
             </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
