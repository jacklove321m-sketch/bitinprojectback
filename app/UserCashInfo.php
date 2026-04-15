<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class UserCashInfo extends Model
{
    protected $table = 'user_cash_info';
    public $timestamps = false;
    protected $appends = ['account_number'];



    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    /*
    public function setWechatNicknameAttribute($value)
    {
        $this->attributes['wechat_nickname'] = base64_encode($value);
    }
    */

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
    
    public static function newAccount($user_id)
	{
		DB::beginTransaction();
		try {
		    $_var_5 = new UserCashInfo();
			$_var_5->user_id = $user_id;
			$_var_5->bank_name =  md5($user_id . time() . mt_rand(0, 99999));
			$_var_5->bank_dizhi = md5($user_id . time() . mt_rand(0, 99999));
			$_var_5->bank_account = md5($user_id . time() . mt_rand(0, 99999));
			$_var_5->save();
			DB::commit();
		} catch (\Exception $_var_6) {
			DB::rollBack();
			throw $_var_6;
		}
	}
}
