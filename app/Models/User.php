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

    public function business_address()
    {
        return $this->hasOne('App\Models\BusinessAddress');
    }

    public function business_profile()
    {
        return $this->hasOne('App\Models\BusinessProfile');
    }

    public function business_images()
    {
        return $this->hasOne('App\Models\BusinessImage');
    }

    public function provider_availability()
    {
        return $this->hasOne('App\Models\ProviderAvailability');
    }

    public function favourite_business()
    {
        return $this->hasMany('App\Models\FavouriteBusiness');
    }
    public function business_services()
    {
        return $this->hasMany('App\Models\businessService')->with(['service' => function ($query) {
            $query->select('id','name','image');
        }]);
    }

    public function business_reviews()
    {
        return $this->hasMany('App\Models\businessReview','business_id','id');
    }
}
