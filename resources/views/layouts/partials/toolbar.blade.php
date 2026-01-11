<div class="toolbar-wrapper">
    <div class="toolbar">
        @foreach($menu as $item)
            @if(isset($item['show_in']) && in_array('toolbar', $item['show_in']))
                @php
                    $currentPath = '/' . ltrim(request()->path(), '/');
                    $itemPath = '/' . ltrim(parse_url($item['url'], PHP_URL_PATH), '/');
                    
                    $isActive = ($currentPath === $itemPath) || (str_starts_with($currentPath, $itemPath . '/'));
                    
                    if ($itemPath === '/store') {
                        if (str_contains($currentPath, '/store/cart') || str_contains($currentPath, '/store/order')) {
                            $isActive = false;
                        }
                    }

                    if ($itemPath === '/store/orders') {
                        if (str_contains($currentPath, '/store/order') || str_contains($currentPath, '/store/cart')) {
                            $isActive = true;
                        }
                        if ($currentPath === '/store') {
                            $isActive = false;
                        }
                    }
                @endphp
                
                <a href="{{ $item['url'] }}" 
                   class="toolbar-item {{ $isActive ? 'active' : '' }} {{ empty($item['title']) ? 'toolbar-icon-only' : '' }}"
                   @if(!empty($item['title'])) title="{{ $item['title'] }}" @endif>
                    
                    <i class="bi {{ $item['icon'] }}"></i>
                    
                    {{-- Выводим текст только если он есть в конфиге --}}
                    @if(!empty($item['title']))
                        <span>{{ $item['title'] }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>
</div>