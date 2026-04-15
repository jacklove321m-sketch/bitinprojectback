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
use App\BalanceList;
use App\AccountLog;
use App\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoBalance extends Command
{
	protected $signature = "auto_balance";
	protected $description = "用户资产汇总";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function handle()
	{
	    $nowday = date('Y-m-d H:i:s',time()); 
       $list = Users::where('id','>',1)->get();
	     foreach ($list as $u){
	          $currency_name='';
	    $user_id = $data['user_id']=$u->id;
	    $change_wallet['usdt_totle'] =0;
	    $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
             ->where('change_balance', '>=', 0)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
            })->get(['id', 'currency', 'change_balance', 'lock_change_balance'])
            ->toArray();
	    
	     foreach ($change_wallet['balance'] as $k => $v) {
           
            $num = $v['change_balance'] + $v['lock_change_balance'];
           
            $change_wallet['usdt_totle'] += $num * $v['usdt_price']; 
        }
	    
	     $lever_wallet['usdt_totle'] =0;
	    $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('lever_balance', '>=', 0)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
      
        
        foreach ($lever_wallet['balance'] as $k => $v) { 
         
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['usdt_totle'] += $num * $v['usdt_price'];
           
        }
        
         $micro_wallet['usdt_totle'] =0;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('micro_balance', '>=', 0)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                // $query->where("is_micro", 1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])
            ->toArray();
        foreach ($micro_wallet['balance'] as $k => $v) {
             
            $num = $v['micro_balance'] + $v['lock_micro_balance'];
           
            $micro_wallet['usdt_totle'] += $num * $v['usdt_price'];
            
        }
        
        
          $legal_wallet['usdt_totle'] =0;
           $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('legal_balance', '>=', 0)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })
            ->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
         
        
        foreach ($legal_wallet['balance'] as $k => $v) {
          
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
          
            $legal_wallet['usdt_totle'] += $num * $v['usdt_price'];
            
        }
	    $data['user_id'] =$user_id;
	    $data['amount'] = $micro_wallet['usdt_totle'] + $lever_wallet['usdt_totle'] + $change_wallet['usdt_totle'] +$legal_wallet['usdt_totle'];
	    
	    $data['addtime'] = $nowday;
	 
	   
	 //  $bl = BalanceList::where('user_id',$user_id)->where('addtime',$nowday)->first();
	 
	   //if(empty($bl)){
	     //  print_r( $data);
	     BalanceList::insert($data);
	    
	  // } //else{
	       
	       
	   //  BalanceList::where('user_id',$user_id)->where('addtime',$nowday)->update($data);  
	//   }
 
	     }
	  
	    
	    echo '已汇总 '.PHP_EOL;
	}
}