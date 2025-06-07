<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_start_date',
        'subscription_end_date',
        'max_employees',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_start_date' => 'date',
            'subscription_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function canAddEmployee(): bool
    {
        return $this->employees()->count() < $this->max_employees;
    }

    public function isSubscriptionActive(): bool
    {
        return $this->is_active && $this->subscription_end_date >= now()->toDateString();
    }

    public function subscriptionNotifications()
    {
        return $this->hasMany(SubscriptionNotification::class);
    }

    public function notifications()
    {
        return $this->hasMany(SubscriptionNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->subscriptionNotifications()->unread()->orderBy('sent_at', 'desc');
    }
}
