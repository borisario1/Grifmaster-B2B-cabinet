<?php

if (!function_exists('bytesToHuman')) {
    /**
     * Конвертирует байты в человекочитаемый формат
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function bytesToHuman($bytes, $precision = 2)
    {
        if ($bytes === 0 || $bytes === null) {
            return '0 Б';
        }
        
        $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('getIconType')) {
    /**
     * Возвращает тип иконки Bootstrap Icons для типа ресурса
     * 
     * @param string $type
     * @return string
     */
    function getIconType($type)
    {
        return match($type) {
            'price_list' => 'excel',
            'certificate' => 'pdf',
            'catalog' => 'pdf',
            '3d_model' => 'zip',
            'video' => 'play',
            default => 'file',
        };
    }
}
