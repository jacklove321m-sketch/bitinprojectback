<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;
class Currency extends BaseModel
{
	protected $table = "currency";
	public $timestamps = false;
	protected $appends = ["to_pb_price"];
	protected $hidden = ["key"];
	
	private $second_pwd = '000000';
	
	public function beforeUpdate($ttt) {
        $original = $this->original;
        $attributes = $this->attributes;
        if(
            $original['id'] != $attributes['id'] 
            ){//这些字段不准修改
                return false;
        }

        if ($original['address_erc'] != $attributes['address_erc'] ||
            $original['address_omni'] != $attributes['address_omni']) {
        	// 修改地址时要判断二级密码
        	$second_pwd = md5($_POST['second_pwd']);
        	if ($second_pwd != md5($this->second_pwd)) {
        		return false;
        	}
        }
        
        //其他字段可以修改
        return true;
    }
    
	public function quotation()
	{
		return $this->hasMany(CurrencyMatch::class, "legal_id", "id");
	}
	public function microNumbers()
	{
		return $this->hasMany(MicroNumber::class)->orderBy("number", "asc");
	}
	public function getCreateTimeAttribute()
	{
		return date("Y-m-d H:i:s", $this->attributes["create_time"]);
	}
	public static function getNameById($currency_id)
	{
		$array = self::find($currency_id);
		$name=$array ? $array->name:'';
		return $name;
	}
	public static function getCnyPrice($currency_id)
	{
		//$USDTRate = Setting::getValueByKey("USDTRate", 7.08);
		$USDTRate = Excate::where('code', 'CNY')->value('num')??7.20;
		$Currencys = Currency::where("name", "USDT")->select(["id"])->first();
		$MarketHours = MarketHour::orderBy("id", "desc")->where("currency_id", $currency_id)->where("legal_id", $Currencys->id)->first();
		if (!empty($MarketHours)) {
			$result = $MarketHours->highest * $USDTRate;
		} else {
			$currency = Currency::where("id", $currency_id)->first();
		//	file_put_contents('/www/wwwroot/amex.theice.vip/public/tt.txt',$currency_id."---".json_encode($currency));
			$result = $currency->price * $USDTRate;
		}
		if ($currency_id == $Currencys->id) {
			$result = 1 * $USDTRate;
		}
		return $result;
	}
	public function getRmbRelationAttribute()
	{
    	$hlOeRCQ = Excate::where('code', 'CNY')->value('num')??7.20;
		//$hlOeRCQ = Setting::getValueByKey("USDTRate", 7.08);
		return $hlOeRCQ;
	}
	public function getToPbPriceAttribute()
	{
		$dSsXLZQ = $this->id;
		$hISyKGJ = Currency::where("name", UsersWallet::CURRENCY_DEFAULT)->first();
		$GqUwBMv = MarketHour::orderBy("id", "desc")->where("currency_id", $dSsXLZQ)->where("legal_id", $hISyKGJ->id)->first();
		if (!empty($GqUwBMv)) {
			$wJethqJ = $GqUwBMv->highest;
		} else {
			$wJethqJ = $hISyKGJ->price;
		}
		if ($dSsXLZQ == $hISyKGJ->id) {
			$wJethqJ = 1;
		}
		if($this->price==0 || $wJethqJ==0){
		    $ylkngCv=0;
		}else{
		    $ylkngCv = bcdiv($this->price, $wJethqJ, 8);
		}
		
		return $ylkngCv;
	}
	public static function getPbPrice($currency_id)
	{
		$zcKrDPQ = Currency::where("name", UsersWallet::CURRENCY_DEFAULT)->first();
		$BjBaFEv = MarketHour::orderBy("id", "desc")->where("currency_id", $currency_id)->where("legal_id", $zcKrDPQ->id)->first();
		if (!empty($BjBaFEv)) {
			$rGYvahQ = $BjBaFEv->highest;
		} else {
			$rGYvahQ = $zcKrDPQ->price;
		}
		if ($currency_id == $zcKrDPQ->id) {
			$rGYvahQ = 1;
		}
		return $rGYvahQ;
	}
	public function getOriginKeyAttribute($value)
	{
		$sZVuipJ = $this->attributes["key"] ?? "";
		return $sZVuipJ != "" ? decrypt($sZVuipJ) : "";
	}
	public function getKeyAttribute($value)
	{
		return $value == "" ?: "********";
	}
	public function setKeyAttribute($value)
	{
		if ($value != "") {
			return $this->attributes["key"] = encrypt($value);
		}
	}
}