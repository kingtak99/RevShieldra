<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'lemon_squeezy_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'reminder_7_days_sent',
        'reminder_1_day_sent',
        'expiry_notice_sent',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'reminder_7_days_sent' => 'boolean',
        'reminder_1_day_sent' => 'boolean',
        'expiry_notice_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isExpired(): bool
    {
        return $this->current_period_end && now()->greaterThan($this->current_period_end);
    }

    public function daysRemaining(): ?int
    {
        return $this->current_period_end ? now()->diffInDays($this->current_period_end, false) : null;
    }

    public function isTrial(): bool
    {
        return $this->lemon_squeezy_subscription_id === null;
    }
}
