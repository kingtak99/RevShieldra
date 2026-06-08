<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'interval',
        'max_branches',
        'lemon_squeezy_variant_id',
        'is_free',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'max_branches' => 'integer',
        'is_free' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
