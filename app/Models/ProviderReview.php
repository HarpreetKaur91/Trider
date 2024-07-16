<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderReview extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','provider_id','rating','comment'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
