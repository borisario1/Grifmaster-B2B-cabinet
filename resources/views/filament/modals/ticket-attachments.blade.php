<div class="space-y-3">
    @foreach($attachments as $attachment)
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded">
                    @if($attachment->is_image)
                        <x-heroicon-o-photo class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    @else
                        <x-heroicon-o-document class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    @endif
                </div>
                <div>
                    <div class="font-medium text-sm">{{ $attachment->original_name }}</div>
                    <div class="text-xs text-gray-500">{{ $attachment->human_size }}</div>
                </div>
            </div>
            <a href="{{ route('tickets.attachment', $attachment->id) }}" 
               target="_blank"
               class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Скачать
            </a>
        </div>
    @endforeach
    
    @if($attachments->isEmpty())
        <div class="text-center text-gray-500 py-4">
            Нет вложений
        </div>
    @endif
</div>
