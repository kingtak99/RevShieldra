<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'rating',
        'name',
        'email',
        'message',
        'tag',
        'attachment_path',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
