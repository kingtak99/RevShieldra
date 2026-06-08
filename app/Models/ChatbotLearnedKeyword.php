<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotLearnedKeyword extends Model
{
    protected $fillable = [
        'language',
        'keyword',
        'normalized_keyword',
        'target_flow',
        'target_branch',
        'custom_response',
        'source',
        'confidence',
        'last_used_at',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'last_used_at' => 'datetime',
    ];
}
