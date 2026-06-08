<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_email',
        'session_id',
        'sender',
        'message',
        'language',
        'log_type',
        'metadata',
    ];
}
