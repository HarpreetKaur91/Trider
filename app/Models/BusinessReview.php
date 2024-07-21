<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessReview extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','business_id','rating','comment'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
