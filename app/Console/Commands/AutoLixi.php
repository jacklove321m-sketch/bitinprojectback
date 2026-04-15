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
class AutoLixi extends Command
{
	protected $signature = "auto_lixi";
	protected $description = "结算量化本息";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function handle()
	{
	    $nowday = date('Ymd',time()); 
	   
	   // ->where('startdate','<=',date('Y-m-d',time()))
	 //  where('expire','>=',date('Y-m-d',time()))
	 $dual_orderend = UsersWallet::where('micro_balance','>',0)->where('today','<>',$nowday)->get();
	     foreach ($dual_orderend as $dual_order){
	          
	         
	         $amount      = $dual_order->micro_balance; 
	         
	         $rates        = 0.6;
	           
           //  $rate = DualOrder::random_float($rates[0],$rates[1]); // 生成一个介于0到1之间的随机小数
 
             $money = bcdiv($rates*$amount,100,2);
	         
	        
	          
	         
	       
	           
	             
	          
	              $user_walllet=UsersWallet::where("user_id",$dual_order->user_id)->where("currency",3)->first();
	              
	               
	             change_wallet_balance($user_walllet , 4 , $money , AccountLog::USER_DUAL_ORDER_RETURN,'earned by currency');// . $user_walllet->name
	             // $dual_order->status = 1; //修改状态为已结算 
	              $dual_order->today = $nowday;
	              $dual_order->save();
	          
	      
	         
	    }
	    
	  
	    
	    echo '已结算 '.PHP_EOL;
	}
}