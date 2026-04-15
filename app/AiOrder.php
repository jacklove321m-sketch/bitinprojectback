<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiOrder extends Model
{
    protected $table = 'ai_order';
    public $timestamps = false;
    
    
    
    
    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency_id')->value('name');
    }
    
    public function getDualNameAttribute()
    {
        return $this->hasOne('App\AiCurrency', 'id', 'ai_id')->value('name');
    }
    
     public static function random_float($min, $max) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
  }
}
