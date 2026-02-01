<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Информация об обращении --}}
        {{ $this->infolist }}
        
        {{-- История сообщений --}}
        <x-filament::section>
            <x-slot name="heading">
                История сообщений
            </x-slot>
            
            <div class="space-y-4 max-h-[600px] overflow-y-auto p-4">
                @forelse($this->record->messages()->with('attachments')->orderBy('created_at', 'asc')->get() as $message)
                    <div class="flex {{ $message->sender_type === 'staff' || $message->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-lg p-4 {{ 
                            $message->sender_type === 'system' ? 'bg-gray-100 dark:bg-gray-800 text-center w-full max-w-full' :
                            (in_array($message->sender_type, ['staff', 'admin']) ? 'bg-primary-500 text-white' : 'bg-gray-200 dark:bg-gray-700')
                        }}">
                            @if($message->sender_type !== 'system')
                                <div class="text-xs opacity-75 mb-1">
                                    {{ in_array($message->sender_type, ['staff', 'admin']) ? 'Поддержка' : 'Клиент' }}
                                </div>
                            @endif
                            
                            <div class="max-w-none {{ $message->sender_type === 'system' ? 'text-sm italic' : '' }}">
                                {!! nl2br(e($message->message)) !!}
                            </div>
                            
                            {{-- Вложения --}}
                            @if($message->attachments && $message->attachments->count() > 0)
                                <div class="mt-3 pt-3 border-t {{ in_array($message->sender_type, ['staff', 'admin']) ? 'border-white/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <div class="text-xs opacity-75 mb-2">📎 Вложения:</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($message->attachments as $att)
                                            <a href="{{ route('tickets.attachment', $att->id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ 
                                                   in_array($message->sender_type, ['staff', 'admin']) 
                                                       ? 'bg-white/20 hover:bg-white/30' 
                                                       : 'bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500' 
                                               }}">
                                                <span>{{ Str::limit($att->original_name, 20) }}</span>
                                                <span class="opacity-75">({{ $att->human_size }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div class="text-xs opacity-75 mt-2 {{ $message->sender_type === 'system' ? 'text-center' : '' }}">
                                {{ $message->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        Сообщений пока нет
                    </div>
                @endforelse
            </div>
        </x-filament::section>
        
        {{-- Форма ответа --}}
        @if($this->record->status !== 'closed')
            <x-filament::section>
                <x-slot name="heading">
                    Ответить на обращение
                </x-slot>
                
                <form wire:submit="sendReply">
                    {{ $this->replyForm }}
                    
                    <div class="mt-4">
                        <x-filament::button type="submit" color="primary">
                            Отправить ответ
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center text-gray-500 py-4">
                    Обращение закрыто. Ответы недоступны.
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
