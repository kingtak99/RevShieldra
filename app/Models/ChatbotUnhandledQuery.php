<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotUnhandledQuery extends Model
{
    protected $fillable = [
        'language',
        'query',
        'normalized_query',
        'occurrences',
        'status',
        'last_seen_at',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];
}
