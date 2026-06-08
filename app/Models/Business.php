<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'plan',
        'email_locale',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withPivot('role_id', 'allowed_pages', 'allowed_locations')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->members();
    }
}
