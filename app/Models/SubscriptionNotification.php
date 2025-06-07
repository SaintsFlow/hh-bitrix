<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'message',
        'is_read',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeWarning($query)
    {
        return $query->where('type', 'warning');
    }

    public function scopeExpired($query)
    {
        return $query->where('type', 'expired');
    }
}
