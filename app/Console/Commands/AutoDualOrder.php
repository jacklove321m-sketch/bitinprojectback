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
use App\DualCurrency;
use App\DualOrder;
use App\AccountLog;
use App\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoDualOrder extends Command
{
	protected $signature = "auto_dual_order";
	protected $description = "结算双币订单";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function handle()
	{
	    $nowday = date('Ymd',time()); 
	   
	   // ->where('startdate','<=',date('Y-m-d',time()))
	 //  where('expire','>=',date('Y-m-d',time()))
	 $dual_orderend = DualOrder::where('expire','<=',date('Y-m-d H:i:s',time()))->where('status',0)->where('today','<>',$nowday)->get();
	     foreach ($dual_orderend as $dual_order){
	         $DualCurrency = DualCurrency::where('id',$dual_order->dual_id)->first();
	         $currency_id = $DualCurrency->currency_id;
	         $exercise_price = $DualCurrency->exercise_price;
	         $type = $DualCurrency->type;
	         $now_price = Currency::where('id',$currency_id)->pluck('price')->first();//查询最新价
	         
	         $amount      = $dual_order->amount; 
	         $totalincome = $dual_order->totalincome;
	         $rates        = explode('-',$dual_order->rate);
	           
             $rate = DualOrder::random_float($rates[0],$rates[1]); // 生成一个介于0到1之间的随机小数
 
 
        
	         
	           $dual_order->todayincome = bcdiv($rate*$amount,100,2);
	         
	           $dual_order->totalincome = $totalincome+$dual_order->todayincome;
	          
	         
	       
	           //   $money = $amount + $totalincome+$dual_order->todayincome;
	             
	              $money = $amount + $dual_order->todayincome;
	              $user_walllet=UsersWallet::where("user_id",$dual_order->user_id)->where("currency",3)->first();
	              change_wallet_balance($user_walllet , 2 , $money , AccountLog::USER_DUAL_ORDER_RETURN,'组合投资理财结算');// . $user_walllet->name
	              
	              $data['user_id'] = $dual_order->user_id;
	              $data['order_id'] = $dual_order->id;
	              $data['day'] = $dual_order->day;
	              $data['rate'] = $dual_order->order_rate;
	              $data['amount']  = $dual_order->todayincome;
	              $data['addtime']  = time();
	              DB::table('dual_list')->insert($data);
	              
	              $dual_order->status = 1; //修改状态为已结算 
	              $dual_order->today = $nowday;
	              $dual_order->save();
	          
	      
	         
	    }
	    
	 
	 
	 
	 $dual_orders = DualOrder::where('startdate','<=',date('Y-m-d H:i:s',time()))->where('expire','>',date('Y-m-d H:i:s',time()))->where('today','<>',$nowday)->where('status',0)->get();
	 
	   
	    foreach ($dual_orders as $dual_order){
	         $DualCurrency = DualCurrency::where('id',$dual_order->dual_id)->first();
	         $currency_id = $DualCurrency->currency_id;
	         $exercise_price = $DualCurrency->exercise_price;
	         $type = $DualCurrency->type;
	         $now_price = Currency::where('id',$currency_id)->pluck('price')->first();//查询最新价
	         
	         $amount      = $dual_order->amount;
	         $dual_order->startdate = date('Y-m-d H:i:s',strtotime($dual_order->startdate)+86400);
	       //  $id          = $dual_order->id;
	         $today          = $dual_order->today;
	         $totalincome = $dual_order->totalincome;
	        // $rate        = $dual_order->order_rate;
	         
	         $rates        = explode('-',$dual_order->rate);
	           
             $rate = DualOrder::random_float($rates[0],$rates[1]); // 生成一个介于0到1之间的随机小数
	         
	         
	           $dual_order->todayincome = bcdiv($rate*$amount,100,2);
	         
	           $dual_order->totalincome = $totalincome+$dual_order->todayincome; 
	          
	          
	         $money = $dual_order->todayincome;
	              $user_walllet=UsersWallet::where("user_id",$dual_order->user_id)->where("currency",3)->first();
	              change_wallet_balance($user_walllet , 2 , $money , AccountLog::USER_DUAL_ORDER_RETURN,'组合投资理财结算');// . $user_walllet->name
	              $data['user_id'] = $dual_order->user_id;
	              $data['order_id'] = $dual_order->id;
	              $data['day'] = $dual_order->day;
	              $data['rate'] = $dual_order->order_rate;
	              $data['amount']  = $money;
	              $data['addtime']  = time();
	              DB::table('dual_list')->insert($data);
	              
	         $dual_order->today = $nowday;
	         $dual_order->save();
	      
	         
	    }
	    
	    echo '已结算 '.PHP_EOL;
	}
}