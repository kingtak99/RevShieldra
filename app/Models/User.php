<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LemonSqueezy\Laravel\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public const AVAILABLE_PAGES = [
        'dashboard',
        'locations',
        'feedback',
        'reports',
    ];

    public const ALLOWED_ONLY_FOR_OWNERS = [
        'settings',
        'team',
    ];

    public function currentBusiness(): ?Business
    {
        return $this->businessesOwned()->first() ?? $this->businesses()->first();
    }

    public function currentMembership()
    {
        $business = $this->currentBusiness();

        if (!$business || $business->owner_id === $this->id) {
            return null;
        }

        return $this->businesses()->where('business_id', $business->id)->first()?->pivot;
    }

    public function allowedPages(Business $business = null): array
    {
        $business = $business ?? $this->currentBusiness();

        if (!$business) {
            return [];
        }

        if ($business->owner_id === $this->id) {
            return self::AVAILABLE_PAGES;
        }

        $membership = $this->businesses()->where('business_id', $business->id)->first();
        $pages = $membership ? json_decode($membership->pivot->allowed_pages ?? '[]', true) ?? [] : [];

        return array_values(array_intersect($pages, self::AVAILABLE_PAGES));
    }

    public function allowedLocationIds(Business $business = null): array
    {
        $business = $business ?? $this->currentBusiness();

        if (!$business) {
            return [];
        }

        if ($business->owner_id === $this->id) {
            return $business->locations()->pluck('id')->toArray();
        }

        $membership = $this->businesses()->where('business_id', $business->id)->first();

        return $membership ? json_decode($membership->pivot->allowed_locations ?? '[]', true) ?? [] : [];
    }

    public function canAccessPage(string $page, Business $business = null): bool
    {
        $business = $business ?? $this->currentBusiness();

        if (!$business) {
            return false;
        }

        if ($business->owner_id === $this->id) {
            return true;
        }

        if (in_array($page, self::ALLOWED_ONLY_FOR_OWNERS, true)) {
            return false;
        }

        return in_array($page, $this->allowedPages($business), true);
    }

    public function isOwnerOfBusiness(Business $business = null): bool
    {
        $business = $business ?? $this->currentBusiness();

        return $business ? $business->owner_id === $this->id : false;
    }

    public function businessesOwned()
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_user')
            ->withPivot('role_id', 'allowed_pages', 'allowed_locations')
            ->withTimestamps();
    }
}
