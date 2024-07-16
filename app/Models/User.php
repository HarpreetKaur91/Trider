<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    public function otp()
    {
        return $this->hasOne('App\Models\Otp');
    }

    public function provider_address()
    {
        return $this->hasOne('App\Models\ProviderAddress');
    }

    public function provider_business_profile()
    {
        return $this->hasOne('App\Models\ProviderBusinessProfile');
    }

    public function provider_availability()
    {
        return $this->hasOne('App\Models\ProviderAvailability');
    }

    public function favourite_providers()
    {
        return $this->hasMany('App\Models\FavouriteProvider');
    }

    public function provider_services()
    {
        return $this->hasMany('App\Models\ProviderService');
    }

    public function provider_reviews()
    {
        return $this->hasMany('App\Models\ProviderReview','provider_id','id');
    }
}
