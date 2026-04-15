<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Users;
use App\UserLevelModel;
use App\DualCurrency;
use App\DualOrder;
use App\DualList;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use App\CurrencyQuotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


//双币理财
class DualController  extends Controller
{
    //显示理财页面
    public function index(Request $request){
        
         $user_id =Users::getUserId();
         
          
         $user = Users::find($user_id);
        
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : 'V0';
         $data['lianghua_amount'] = 0;   //货币数量
         $data['todayincome'] = 0;  // 今日收益
         $data['totalincome'] = 0;  // 总收益
         $data['rate'] = 0.00; // 回报
         $nowday = date('Ymd',time()); 
          $nowtime = date('Y-m-d H:i:s',time()); 
        $info['data'] = DualCurrency::where('status',1)->select("id","invest","hot","days","ratemax","amount","amax","remaining_number")->orderBy('days','ASC')->get();
           $invest ="BTC+ETH+ATOM+SOL";
           $invest = explode("+",$invest);
           $arr =[];
       foreach ($invest as $k=>$v){
          
           $Currencys =  Currency::where('name',$v)->first();
           $arr[$k]['name']= $v;
           $arr[$k]['logo']= $Currencys->logo;
          
        //   $arr[$k]['change']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('change');
           $arr[$k]['now_price']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('now_price');
        }
       //   return $this->error($nowday);
         
        // $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('amount');
      /*
         $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('expire','>=',$nowtime)->where('created','<=',$nowtime)->sum('amount');
         $data['todayincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
         $data['totalincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('totalincome');
         */
         if($user_id){
         $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('status',0)->sum('amount');
         $data['todayincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
         $data['totalincome']       = DualOrder::where('user_id',$user_id)->sum('totalincome');
         
        
        if($data['lianghua_amount']>0)  $data['rate']              = round(bcdiv($data['todayincome'],$data['lianghua_amount'],4)*100,4);  // 回报率
         }  
          $data['VIP']       = $user['user_level'] > 0 ?$userLevel['name'] : 'V0';
        
        $info =  array_merge($info,$data);
        
       
        
        
       
        return $this->success(array("info"=>$info,"currency"=>$arr));
        
    }
    
    //查看我的理财订单
    public function dual_list(Request $request){
        $limit = $request->get('limit', 100);
        $status = $request->get('status',0);
       
        $user_id = Users::getUserId();
        
        $user = Users::find($user_id);
        
      
        
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : 'V0';
       // $list = DB::table('dual_order')->select('dual_order.*','dual_currency.name')->join('dual_currency', 'dual_currency.id', '=', 'dual_order.dual_id')->where('dual_order.user_id', $user_id)->where('dual_order.status', $status)->orderBy('dual_order.id', 'desc')->paginate($limit);
            
      $list = DualOrder::where('user_id', $user_id)->where('status', $status)->select('id','name','amount','totalincome','invest','startdate','expire','status')->orderBy('id', 'desc')->paginate($limit);

        return $this->success(array(
            "list" => $list->items(),
            "limit" => $limit,
            'VIP'=> $user['user_level'] > 0 ?$userLevel['name'] : 'V0'
        ));
    }
    
    
     //理财撤单
    public function Dualcancle(Request $request){
        $id =  $request->post('id');
        
        $user_id =  Users::getUserId(); 
       
        
        if(empty($id) || !is_numeric($id)){
            return $this->error('Parameter error：ID Is NULL');
        }   
     
        $dual_order = DualOrder::where('id',$id)->where('status',0)->first();
        
        $amount = $dual_order->amount;
        if(empty($amount)){
            return $this->error('Parameter error：amount Is NULL');
        }  
        $liquidateddamages = $dual_order->liquidateddamages;
        $todayincome = $dual_order->todayincome;
        $totalincome= $dual_order->totalincome; 
       
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",3)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
         
       
         //   $dual_order->remaining_number = $dual_order->remaining_number +1;
          //  $dual_order->purchased_number = $dual_order->purchased_number -1;
            $dual_order->status = 1;
            $dual_order->amount = 0;
            $dual_order->todayincome=0;
            $dual_order->totalincome=0;
            $amount = $amount - $amount*$liquidateddamages/100; 
            
           
            
            $result =  change_wallet_balance($user_walllet , 2 , $amount , AccountLog::USER_LOAN_ORDER_RETURN,'组合投资返本');
            if($result){
                $dual_order->save(); 
                return $this->success('操作成功');
                
            } 
        
    }
    
    
    
    
    
    
    //购买理财
    public function buyDual(Request $request){
        $id = $request->post('id');
        $num = $request->post('num');
        $user_id = Users::getUserId();
        $num = intval($num);
        
        if(empty($num) || !is_numeric($num)){
            return $this->error('Parameter error：num ');
        }     
        
        if(empty($id) || !is_numeric($id)){
            return $this->error('Parameter error：ID Is NULL');
        }  
        
        $user = Users::where('id', $user_id)->first();
        
        
        if ($user->frozen_funds == 1 || $user->status == 1) {
            return $this->error('Hello, Account is Locked. Please contact customer service for details.');
        }
        
      //  if(Cache::has("by_dual_order_$user_id")){
       //     return $this->error('Do not repeat the operation!'); 
       // }
      //  Cache::put("by_dual_order_$user_id", 1, Carbon::now()->addSeconds(5));//禁止重复提交
        
       // $count = DualOrder::where('user_id',$user_id)->where('dual_id',$id)->count();
        $dual_currencys = DualCurrency::where('id',$id)->first();
        if($num<$dual_currencys->amount) {
            
             return $this->error('Parameter error：num amount error');
        }
        
        if($num>$dual_currencys->amax) {
            
             return $this->error('Parameter error：num max error');
        }
        
        if($dual_currencys->status==0){
            
             return $this->error('Sold out');
        }
        
        if($num<$dual_currencys->amax) {
            
            $rate = $dual_currencys->rate;
        }
        
        if($num==$dual_currencys->amax) {
            
            $rate = $dual_currencys->ratemax;
        }
        
        
        $expire =   time()+$dual_currencys->days*24*60*60;
        //if($dual_currencys->end_time <= date('Y-m-d') || $dual_currencys->status == 0){//结束时间等于今天不可购买
       //     return $this->error('Project has ended!');
      //  }
        if(1 > $dual_currencys->remaining_number){//已经卖完
            return $this->error('Sold out!');
         }
        
      //  if($count + $num > $dual_currencys->user_limit){
       //     return $this->error('Purchase limit exceeded!');
      //  }
        
        $invest =   $invest0 = $dual_currencys->invest;
         $invest = explode("+",$invest);
           $investr ='';
       foreach ($invest as $k=>$v){
               $rate = DualOrder::random_float(10.18,25.88); // 生成一个介于0到1之间的随机小数 
                  $investr .=$v." ".number_format($rate,2).'%'."  "; 
            
               }
       
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",3)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
        if($user_walllet->change_balance < $num){
            return $this->error('Insufficient balance!');
        }
        
            
        DB::beginTransaction();
        try{
          //  $d1=strtotime($dual_currencys->end_time);
          //  $d2 = strtotime(date('Y-m-d'));
         //   $dayCount=round(($d1- $d2)/3600/24);
            $dual_order = new DualOrder();
            $dual_order->user_id = $user_id;
          //  $dual_order->currency_id = $buy_currency;//用户购买时消耗的币种
          //  $dual_order->dual_id = $dual_currencys->id;
          
            $dual_order->order_rate = $rate;
            $dual_order->dual_id = $id;
            $dual_order->name = $invest0;
            $dual_order->invest = $investr;
            $dual_order->rate = $dual_currencys->rate."-".$dual_currencys->ratemax;
            $dual_order->day = $dual_currencys->days;
            $dual_order->amount = $num;
            $dual_order->expire = date('Y-m-d H:i:s',$expire);
            $dual_order->orderid =date("YmdHis",time()).$expire; 
            $dual_order->liquidateddamages = $dual_currencys->liquidateddamages;
          //  $dual_order->price = $now_price;
          //  $dual_order->total = $total;
           $dual_order->startdate =date('Y-m-d H:i:s');
            $dual_order->created = date('Y-m-d H:i:s');
           
            
          //  $dual_currencys->refresh();
          //  if($num > $dual_currencys->remaining_number || $count + $num > $dual_currencys->user_limit){//已经卖完
          //      DB::rollBack();
          //      return $this->error('Sold out!');
          //  }
        
           $dual_currencys->remaining_number = $dual_currencys->remaining_number - 1;
           $dual_currencys->purchased_number = $dual_currencys->purchased_number + 1;
           $dual_currencys->save();
            
            $result =  change_wallet_balance($user_walllet , 2 , -$num , AccountLog::USER_DUAL_ORDER_BUY,'组合投资扣除');
            if($result) $dual_order->save();
            
            DB::commit();
            return $this->success('Successful');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    public function getDetail(Request $request){
       $dual_id = $request->get('id');
       $dual = DualCurrency::where('id',$dual_id)->select('id','invest','days','ratemax','amount','amax','commission')->first();
       $invest = explode("+",$dual->invest);
       $arr =[];
       foreach ($invest as $k=>$v){
          
           $Currencys =  Currency::where('name',$v)->first();
           $arr[$k]['name']= $v;
           $arr[$k]['logo']= $Currencys->logo;
          
           $arr[$k]['change']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('change');
            
        }
          $user_id =  Users::getUserId(); 
          $account = UsersWallet::where('user_id',$user_id)->where('currency',3)->value('change_balance')??"0.00"; //可用USDT
      // print_r($arr);exit;
          return $this->success(array(
            "detail" => $dual,
            "Currency" => $arr,
            'balance' => $account,
        ));
       // return $this->success($dual); 
    }
    
    
    
public function  order_detail(Request $request){
       $id =$request->get('id');
       $dual = DualList::where('order_id',$id)->select('id','amount','addtime')->orderBy('id','ASC')->get();
       $total=  DualList::where('order_id',$id)->sum('amount');
       foreach ($dual as $k=>$v){
       $dual[$k]['addtime']= date('Y-m-d H:i:s',$v->addtime);
       }
       return $this->success(array(
            "detail" => $dual,
            "total" => $total,
        ));
     
        return $this->success($dual); 
    }
    
    
}