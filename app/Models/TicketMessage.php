<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $table = 'b2b_request_messages';
    public $timestamps = false; // В легаси только created_at

    protected $fillable = [
        'request_id', 'sender_type', 'sender_id', 'message', 'is_read', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Вложения сообщения
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'message_id');
    }

    /**
     * Тикет, к которому относится сообщение
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'request_id');
    }
}
