<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $table = 'b2b_request_attachments';
    
    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Принадлежит сообщению
     */
    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'message_id');
    }

    /**
     * Размер файла в человекочитаемом формате
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Иконка по типу файла
     */
    public function getIconAttribute(): string
    {
        $type = explode('/', $this->mime_type)[0];
        
        return match ($type) {
            'image' => 'bi-file-image',
            'video' => 'bi-file-play',
            'application' => match (true) {
                str_contains($this->mime_type, 'pdf') => 'bi-file-pdf',
                str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'rar') => 'bi-file-zip',
                str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'bi-file-excel',
                str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => 'bi-file-word',
                default => 'bi-file-earmark',
            },
            default => 'bi-file-earmark',
        };
    }
    
    /**
     * Проверяет, является ли файл изображением
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
