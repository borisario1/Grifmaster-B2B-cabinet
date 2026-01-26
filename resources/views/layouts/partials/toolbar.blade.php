<div class="toolbar-wrapper">
    <div class="toolbar">
        @foreach($menu as $item)
            @if(isset($item['show_in']) && in_array('toolbar', $item['show_in']))
                @php
                    $currentPath = '/' . ltrim(request()->path(), '/');
                    $itemPath = '/' . ltrim(parse_url($item['url'], PHP_URL_PATH), '/');
                    
                    $isActive = ($currentPath === $itemPath) || (str_starts_with($currentPath, $itemPath . '/'));
                    
                    if ($itemPath === '/catalog') {
                        if (str_contains($currentPath, '/catalog/cart') || str_contains($currentPath, '/catalog/order')) {
                            $isActive = false;
                        }
                    }

                    if ($itemPath === '/catalog/orders') {
                        if (str_contains($currentPath, '/catalog/order') || str_contains($currentPath, '/catalog/cart')) {
                            $isActive = true;
                        }
                        if ($currentPath === '/catalog') {
                            $isActive = false;
                        }
                    }

                    $displayText = !empty($item['title_in_burger']) 
                        ? $item['title_in_burger'] 
                        : ($item['title'] ?? null);
                @endphp
                
                {{-- Используем $displayText для проверок классов и вывода текста --}}
                <a href="{{ $item['url'] }}" 
                   class="toolbar-item {{ $isActive ? 'active' : '' }} {{ empty($displayText) ? 'toolbar-icon-only' : '' }}"
                   @if(!empty($displayText)) title="{{ $displayText }}" @endif>
                    
                    <i class="bi {{ $item['icon'] }}"></i>
                    
                    @if(!empty($displayText))
                        <span>{{ $displayText }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>
</div>