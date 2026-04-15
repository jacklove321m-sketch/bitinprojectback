<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CurrencyMatch extends Model
{
	public $timestamps = false;
	protected $appends = ["legal_name", "currency_name", "market_from_name", "type", "is_class", "change", "volume", "now_price", "rmb_relation", "logo", "category_text", "content", "issue_num", "sell_status","real_name"];
	public function getSellStatusAttribute()
	{
		return $this->currency()->value("sell_status");
	}
	
	public function getRealNameAttribute()
	{
		return $this->currency()->value("real_name");
	}
	public function getIssueNumAttribute()
	{
		return $this->currency()->value("issue_num");
	}
	
	public function getTypeAttribute()
	{
		return $this->currency()->value("type");
	}
	
	public function getIsClassAttribute()
	{
		return $this->currency()->value("is_class");
	}
	public function getContentAttribute()
	{
		return $this->currency()->value("content");
	}
	public function getRmbRelationAttribute()
	{
		return $this->currency()->value("rmb_relation");
	}
	protected static $marketFromNames = ["无", "交易所", "A股", "B股", "港股", "NASDA", "股票", "币安", "欧易", "ETF", "H股"];
	protected function getLogoAttribute()
	{
		return $this->currency()->value("logo");
	}
	protected function getCategoryTextAttribute()
	{
		switch ($this->attributes["category"]) {
			case 1:
				return "主流区";
				break;
			case 2:
				return "创新区";
				break;
			default:
				return "未知区";
		}
	}
	public function legal()
	{
		return $this->belongsTo(Currency::class, "legal_id", "id")->withDefault();
	}
	public function currency()
	{
		return $this->belongsTo(Currency::class, "currency_id", "id")->withDefault();
	}
	public static function enumMarketFromNames()
	{
		return self::$marketFromNames;
	}
	public function getSymbolAttribute()
	{
		return $this->getCurrencyNameAttribute() . "/" . $this->getLegalNameAttribute();
	}
	public function getMatchNameAttribute()
	{
		return strtolower($this->getCurrencyNameAttribute() . $this->getLegalNameAttribute());
	}
	public function getLegalNameAttribute()
	{
		return $this->legal()->value("name");
	}
	public function getCurrencyNameAttribute()
	{
		return $this->currency()->value("name");
	}
	public function getMarketFromNameAttribute($value)
	{
		return self::$marketFromNames[$this->attributes["market_from"]];
	}
	public function getCreateTimeAttribute($value)
	{
		return $value === null ? "" : date("Y-m-d H:i:s", $value);
	}
	public function getDaymarketAttribute()
	{
		$JZCwucJ = $this->attributes["legal_id"];
		$PslPuOv = $this->attributes["currency_id"];
		CurrencyQuotation::unguard();
		$NiSCQSJ = CurrencyQuotation::firstOrCreate(["legal_id" => $JZCwucJ, "currency_id" => $PslPuOv], ["match_id" => $this->attributes["id"], "change" => "", "volume" => 0, "now_price" => 0, "add_time" => time()]);
		CurrencyQuotation::reguard();
		return $NiSCQSJ;
	}
	public function getChangeAttribute()
	{
		return $this->getDaymarketAttribute()->change;
	}
	public function getVolumeAttribute()
	{
		return $this->getDaymarketAttribute()->volume;
	}
	public function getNowPriceAttribute()
	{
		return $this->getDaymarketAttribute()->now_price;
	}
	public function quotation()
	{
		return $this->hasOne("App\\CurrencyQuotation", "legal_id", "legal_id");
	}
	public static function getHuobiMatchs()
	{
		$qBXDpPQ = self::with(["legal", "currency"])->where("market_from", 2)->get();
		$huobi_symbols = HuobiSymbol::pluck("symbol")->all();
		$qBXDpPQ->transform(function ($item, $key) {
			$item->addHidden("currency");
			$item->addHidden("legal");
			$item->append("match_name");
			return $item;
		});
		$qBXDpPQ = $qBXDpPQ->filter(function ($value, $key) use($huobi_symbols) {
			return in_array($value->match_name, $huobi_symbols);
		});
		return $qBXDpPQ;
	}
	
	public static function forward()
    {
        $currency_match = self::with(['legal', 'currency'])
            ->where('market_from', 3)
            ->get();
        $currency_match->transform(function ($item, $key) {
            $item->addHidden('currency');
            $item->addHidden('legal');
            $item->append('match_name');
            return $item;
        });
        return $currency_match;
    }
    
    
    public static function forward_etf()
    {
        $info = DB::table('currency_matches')->where('market_from',9)->paginate(100);
        $info = json_encode($info);
        $info = json_decode($info,true);
        $info = $info['data'][rand(0,count($info['data'])-1)];
        
        $currency_quotation = DB::table('currency_quotation')->where('currency_id',$info['currency_id'])->first();
        $currency_quotation = json_encode($currency_quotation);
        $currency_quotation = json_decode($currency_quotation,true);
        
        $currency = DB::table('currency')->where('id',$info['currency_id'])->first();
        $currency = json_encode($currency);
        $currency = json_decode($currency,true);
        $data['match_id'] = $info['id'];//交易对id
        $data['currency_id'] = $info['currency_id'];//币id
        $data['open'] = $currency_quotation['open'];//开
        $data['close'] = $currency_quotation['now_price'];
        $data['high'] = $currency_quotation['high'];
        $data['low'] = $currency_quotation['low'];
        $data['name'] = $currency['name'];
        $data['volume'] = $currency_quotation['volume'];
        return $data;
    }
    
    
    
    
    
    public static function forward_gp()
    {
        $info = DB::table('currency_matches')->where('market_from',6)->paginate(100);
        $info = json_encode($info);
        $info = json_decode($info,true);
        $info = $info['data'][rand(0,count($info['data'])-1)];
        
        $currency_quotation = DB::table('currency_quotation')->where('currency_id',$info['currency_id'])->first();
        $currency_quotation = json_encode($currency_quotation);
        $currency_quotation = json_decode($currency_quotation,true);
        
        $currency = DB::table('currency')->where('id',$info['currency_id'])->first();
        $currency = json_encode($currency);
        $currency = json_decode($currency,true);
         $data['match_id'] = $info['id'];//交易对id
        $data['currency_id'] = $info['currency_id'];//币id
        $data['open'] = $currency_quotation['open'];//开
        $data['close'] = $currency_quotation['now_price'];
        $data['high'] = $currency_quotation['high'];
        $data['low'] = $currency_quotation['low'];
        $data['name'] = $currency['name'];
        $data['volume'] = $currency_quotation['volume'];
        return $data;
    }
    
    
    
    public static function forward_sj()
    {
        $info = DB::table('currency_matches')->where('market_from',3)->paginate(100);
        $info = json_encode($info);
        $info = json_decode($info,true);
        $info = $info['data'][rand(0,count($info['data'])-1)];
        
        $currency_quotation = DB::table('currency_quotation')->where('currency_id',$info['currency_id'])->first();
        $currency_quotation = json_encode($currency_quotation);
        $currency_quotation = json_decode($currency_quotation,true);
        
        $currency = DB::table('currency')->where('id',$info['currency_id'])->first();
        $currency = json_encode($currency);
        $currency = json_decode($currency,true);
        $data['match_id'] = $info['id'];//交易对id
        $data['currency_id'] = $info['currency_id'];//币id
        $data['open'] = $currency_quotation['open'];//开
        $data['close'] = $currency_quotation['now_price'];
        $data['high'] = $currency_quotation['high'];
        $data['low'] = $currency_quotation['low'];
        $data['name'] = $currency['name'];
        $data['volume'] = $currency_quotation['volume'];
        return $data;
    }
    
    public static function foreign_sj()
    {
        $info = DB::table('currency_matches')->where('market_from',0)->paginate(100);
        $info = json_encode($info);
        $info = json_decode($info,true);
        $info = $info['data'][rand(0,count($info['data'])-1)];
        
        $currency_quotation = DB::table('currency_quotation')->where('currency_id',$info['currency_id'])->first();
        $currency_quotation = json_encode($currency_quotation);
        $currency_quotation = json_decode($currency_quotation,true);
        
        $currency = DB::table('currency')->where('id',$info['currency_id'])->first();
        $currency = json_encode($currency);
        $currency = json_decode($currency,true);
         $data['match_id'] = $info['id'];//交易对id
        $data['currency_id'] = $info['currency_id'];//币id
        $data['open'] = $currency_quotation['open'];//开
        $data['close'] = $currency_quotation['now_price'];
        $data['high'] = $currency_quotation['high'];
        $data['low'] = $currency_quotation['low'];
        $data['name'] = $currency['name'];
        $data['volume'] = $currency_quotation['volume'];
        return $data;
    }
	
	
	public function getRiskGroupResultNameAttribute()
	{
		$wrVJFiQ = [-1 => "亏损", 0 => "无", 1 => "盈利"];
		$pklTcwv = $this->attributes["risk_group_result"] ?? 0;
		return $wrVJFiQ[$pklTcwv];
	}
}