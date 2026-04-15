<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Users;
use App\DualCurrency;
use App\DualOrder;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use App\Defi;
use App\DefiOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


//双币理财
class DefiController  extends Controller
{
    //显示理财页面
    public function index(Request $request){
        
         $user_id = Users::getUserId();
          if(!$user_id){
            return $this->error('error!'); 
          }
         $data['lianghua_amount'] = 0;   //货币数量
         $data['todayincome'] = 0;  // 今日收益
         $data['totalincome'] = 0;  // 总收益
         $data['rate'] = 0.00; // 回报
         $nowday = date('Ymd',time()); 
          $nowtime = date('Y-m-d H:i:s',time()); 
        $info['data'] = DualCurrency::where('status',1)->orderBy('days','ASC')->get();
          
       //   return $this->error($nowday);
         
        // $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('amount');
      /*
         $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('expire','>=',$nowtime)->where('created','<=',$nowtime)->sum('amount');
         $data['todayincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
         $data['totalincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('totalincome');
         */
         $data['lianghua_amount']   = DualOrder::where('user_id',$user_id)->where('status',0)->sum('amount');
         $data['todayincome']       = DualOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
         $data['totalincome']       = DualOrder::where('user_id',$user_id)->sum('totalincome');
         
         
        if($data['lianghua_amount']>0)  $data['rate']              = round(bcdiv($data['todayincome'],$data['lianghua_amount'],4)*100,4);  // 回报率
           
        
         
        $info =  array_merge($info,$data);
        
       
      
        
       
        return $this->success($info);
        
    }  
    
    // Request $request
      public function defiList(Request $request){
           $limit = $request->get('limit', 100);
           $user_id = Users::getUserId();
          $data =  Defi::where('is_del',0)->orderBy('days', 'asc')->select('id','days','bilv','percent','fwamount')->paginate($limit);
       
       
       
        $account = UsersWallet::where('user_id',$user_id)->where('currency',3)->value('change_balance')??"0.00"; //可用USDT
        
        $amount  = DefiOrder::where('user_id',$user_id)->where('review_status',2)->sum('amount')??"0.00"; //已借USDT
        $remainamount  = DefiOrder::where('user_id',$user_id)->where('review_status',2)->where('status',0)->sum('total')??"0.00"; //已借USDT
     
       $user['usdt_balance'] = round($account,2);//$userInfo['usdt_balance'];
       $user['amount'] = $amount;//$userInfo['usdt_balance'];
       $user['percent'] = 0.3;
       $user['remainamount'] = $remainamount;//$userInfo['usdt_balance'];
       
        return $this->success(array(
            "data" => $data,
            "user" => $user,
        ));
       
    } 
    
    
    
   
     //返回质押列表
    public function orderList(Request $request)
    {
           $page = $request->get('page', 1);
           $limit = $request->get('limit',10);
           $status=$request->get('status', 0);
           $user_id = Users::getUserId();//66005883;
         //  $where['review_status'] = ['>', 0];
         //  $where['status']= $status;  
           
               
       $lists  = DefiOrder::where('status',$status)
            ->where('user_id',$user_id)
            ->paginate($limit);
         
       

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }
    
    
     //购买 $param
    public function Buy(Request $request)
    {
          $user_id = Users::getUserId();
        //
        
       // $userInfo['id']=1;
       //  $param['daysId']=3;
       //  $request->post('amount'] = 1136.5;
        
       
        
        $id = $request->post('daysId');
        $yamount = $request->post('amount'); //验资金额
        $amount = bcmul($yamount,0.30,2);//借币金额
      
        
        
        $info = Defi::find($id);
        
        
        $days        = $info['days'];//质押期限
        $lv_perday   = $info['bilv'];//日币利息
        
        $opayment    = 0;//滞纳金
        $opaymentlv  = $info['opaymentlv'];//滞纳金百分比
        $fwamount    = $info['fwamount'];//总服务费
        
        
        $lixi        = bcmul($amount*$days,$lv_perday/100,2); //利息
        
        $total       = $amount+$fwamount+$lixi; //本金+利息
        $review_status =1;
        // $account = $userInfo['account'];
        $account = UsersWallet::where('user_id',$user_id)->where('currency',3)->value('change_balance')??"0.00"; //可用USDT
        
         //return Response::fail($account);
         
        
        
        // return Response::fail($yamount);
        if ($account < $yamount) {
            return $this->error('Insufficient Balance');
        }
       
        $currentTime = date('Y-m-d H:i:s',time());
        $orderData['htcode']   = 'Y' . date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $orderData['user_id']  = $user_id;
        $orderData['amount']   = $amount;
        $orderData['yamount']  = $yamount;
        $orderData['fwamount'] = $fwamount;
        $orderData['lixi']     = $lixi;
        $orderData['total']    = $total;
        $orderData['days']     = $days;
        $orderData['create_time'] = $currentTime;
        $orderData['lv_perday'] = $lv_perday;
        $orderData['opaymentlv'] = $opaymentlv;
        
        $orderData['name'] = $request->post('name');
        $orderData['last'] = $request->post('last');
        
        $orderData['address'] = $request->post('address');
        $orderData['mail']    = $request->post('mail');
        $orderData['tel']    = $request->post('tel');
        $orderData['signature']    = $request->post('signature');
        $orderData['signatureUrl']     = $request->post('signatureUrl');
        
        
       //  file_put_contents('/www/wwwroot/nft/public/t.txt',json_encode($orderData));
        
       

         DB::beginTransaction();
        try {
            $order_id =  Db::table('defi_order')->insert($orderData);
          
           DB::commit();
            return $this->success('success');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    
      //质押详情
    public function Detail(Request $request)
    {
        $id =  $request->post('id');
        $info = DefiOrder::where('id',$id)->select('htcode','review_time','end_time','bamount','remainamount','status')->first();
            
        return $this->success($info);
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
         
       
          
            $dual_order->status = 1;
            $dual_order->amount = 0;
            $dual_order->todayincome=0;
            $dual_order->totalincome=0;
            $amount = $amount - $amount*$liquidateddamages/100; 
            
           
            
            $result =  change_wallet_balance($user_walllet , 4 , $amount , AccountLog::USER_LOAN_ORDER_RETURN,'量化返本');
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
            return $this->error('こんにちは、アカウントはロックされています。詳細はカスタマーサービスにお問い合わせください。');
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
            
             return $this->error('売り切れ');
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
       // if($num > $dual_currencys->remaining_number){//已经卖完
       //     return $this->error('Sold out!');
      //  }
        
      //  if($count + $num > $dual_currencys->user_limit){
       //     return $this->error('Purchase limit exceeded!');
      //  }
        
         
        
       
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",3)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
        if($user_walllet->micro_balance < $num){
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
            $dual_order->rate = $dual_currencys->rate."-".$dual_currencys->ratemax;
            $dual_order->day = $dual_currencys->days;
            $dual_order->amount = $num;
            $dual_order->expire = date('Y-m-d H:i:s',$expire);
            $dual_order->orderid =date("YmdHis",time()).$expire; 
            $dual_order->liquidateddamages = $dual_currencys->liquidateddamages;
          //  $dual_order->price = $now_price;
          //  $dual_order->total = $total;
           $dual_order->startdate =date('Y-m-d H:i:s',time()+86400);
            $dual_order->created = date('Y-m-d H:i:s');
           
            
          //  $dual_currencys->refresh();
          //  if($num > $dual_currencys->remaining_number || $count + $num > $dual_currencys->user_limit){//已经卖完
          //      DB::rollBack();
          //      return $this->error('Sold out!');
          //  }
        
          //  $dual_currencys->remaining_number = $dual_currencys->remaining_number - $num;
          //  $dual_currencys->purchased_number = $dual_currencys->purchased_number + $num;
          //  $dual_currencys->save();
            
            $result =  change_wallet_balance($user_walllet , 4 , -$num , AccountLog::USER_DUAL_ORDER_BUY,'量化扣除');
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
       $dual = DualCurrency::where('id',$dual_id)->first();

        return $this->success($dual); 
    }
}