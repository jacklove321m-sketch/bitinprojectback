<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiCurrency extends Model
{
    protected $table = 'ai_currency';
    public $timestamps = false;
  
    
    
        public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency_id')->value('name');
    }

}
