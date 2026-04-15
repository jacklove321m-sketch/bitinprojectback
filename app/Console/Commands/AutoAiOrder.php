<?php

namespace App\Console\Commands;

use App\AutoList;
use App\CurrencyQuotation;
use App\MarketHour;
use App\Setting;
use App\TransactionComplete;
use App\UsersWallet;
use Carbon\Carbon;
use Faker\Factory;
use App\Users;
use App\AiCurrency;
use App\AiOrder;
use App\AccountLog;
use App\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoAiOrder extends Command
{
	protected $signature = "auto_ai_order";
	protected $description = "AI量化订单";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function handle()
	{
	    $nowday = date('Ymd',time()); 
	   
	   // ->where('startdate','<=',date('Y-m-d',time()))
	 //  where('expire','>=',date('Y-m-d',time()))
	 $Ai_orderend = AiOrder::where('expire','<=',date('Y-m-d H:i:s',time()))->where('status',0)->where('today','<>',$nowday)->get();
	     foreach ($Ai_orderend as $Ai_order){
	         $AiCurrency = AiCurrency::where('id',$Ai_order->ai_id)->first();
	         $currency_id = $AiCurrency->currency_id;
	         $exercise_price = $AiCurrency->exercise_price;
	         $type = $AiCurrency->type;
	         $now_price = Currency::where('id',$currency_id)->pluck('price')->first();//查询最新价
	         
	         $amount      = $Ai_order->amount; 
	         $totalincome = $Ai_order->totalincome;
	         $rates        = explode('-',$Ai_order->rate);
	           
             $rate = AiOrder::random_float($rates[0],$rates[1]); // 生成一个介于0到1之间的随机小数
 
 
        
	         
	           $Ai_order->todayincome = bcdiv($rate*$amount,100,2);
	         
	           $Ai_order->totalincome = $totalincome+$Ai_order->todayincome;
	          
	         
	       
	           //   $money = $amount + $totalincome+$Ai_order->todayincome;
	             
	              $money = $amount + $Ai_order->todayincome;
	              $user_walllet=UsersWallet::where("user_id",$Ai_order->user_id)->where("currency",3)->first();
	              change_wallet_balance($user_walllet , 2 , $money , AccountLog::USER_AI_ORDER_RETURN,'AI量化结算');// . $user_walllet->name
	              
	              $data['user_id'] = $Ai_order->user_id;
	              $data['order_id'] = $Ai_order->id;
	              $data['day'] = $Ai_order->day;
	              $data['rate'] = $Ai_order->order_rate;
	              $data['amount']  = $Ai_order->todayincome;
	              $data['addtime']  = time();
	              DB::table('ai_list')->insert($data);
	              
	              $Ai_order->status = 1; //修改状态为已结算 
	              $Ai_order->today = $nowday;
	              $Ai_order->save();
	          
	      
	         
	    }
	    
	 
	 
	 
	 $Ai_orders = AiOrder::where('startdate','<=',date('Y-m-d H:i:s',time()))->where('expire','>',date('Y-m-d H:i:s',time()))->where('today','<>',$nowday)->where('status',0)->get();
	 
	   
	    foreach ($Ai_orders as $Ai_order){
	         $AiCurrency = AiCurrency::where('id',$Ai_order->ai_id)->first();
	         $currency_id = $AiCurrency->currency_id;
	         $exercise_price = $AiCurrency->exercise_price;
	         $type = $AiCurrency->type;
	         $now_price = Currency::where('id',$currency_id)->pluck('price')->first();//查询最新价
	         
	         $amount      = $Ai_order->amount;
	         $Ai_order->startdate = date('Y-m-d H:i:s',strtotime($Ai_order->startdate)+86400);
	       //  $id          = $Ai_order->id;
	         $today          = $Ai_order->today;
	         $totalincome = $Ai_order->totalincome;
	        // $rate        = $Ai_order->order_rate;
	         
	         $rates        = explode('-',$Ai_order->rate);
	           
             $rate = AiOrder::random_float($rates[0],$rates[1]); // 生成一个介于0到1之间的随机小数
	         
	         
	           $Ai_order->todayincome = bcdiv($rate*$amount,100,2);
	         
	           $Ai_order->totalincome = $totalincome+$Ai_order->todayincome; 
	          
	          
	         $money = $Ai_order->todayincome;
	              $user_walllet=UsersWallet::where("user_id",$Ai_order->user_id)->where("currency",3)->first();
	              change_wallet_balance($user_walllet , 2 , $money , AccountLog::USER_AI_ORDER_RETURN,'AI量化结算');// . $user_walllet->name
	              $data['user_id'] = $Ai_order->user_id;
	              $data['order_id'] = $Ai_order->id;
	              $data['day'] = $Ai_order->day;
	              $data['rate'] = $Ai_order->order_rate;
	              $data['amount']  = $money;
	              $data['addtime']  = time();
	              DB::table('ai_list')->insert($data);
	              
	         $Ai_order->today = $nowday;
	         $Ai_order->save();
	      
	         
	    }
	    
	    echo '已结算 '.PHP_EOL;
	}
}