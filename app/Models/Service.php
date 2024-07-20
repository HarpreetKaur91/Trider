<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id','name','logo','description','status'];
    protected $hidden = ['created_at','updated_at'];
    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id','id');
    }

    public function children()
    {
        return $this->hasMany(self::class,'parent_id','id')->select('id','parent_id','name','description','image');
    }

    public function service_prices(){
        return $this->hasMany('App\Models\ServicePrice')->select('id','service_id','shift','price');
    }
}
