<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Users;
use App\News;
use App\UserLevelModel;
use App\AiCurrency;
use App\AiOrder;
use App\AiList;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use App\CurrencyQuotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


//AI理财
class AiController  extends Controller
{
    //显示理财页面
    public function index(Request $request){
        
         $user_id =Users::getUserId();
         
          
         $user = Users::find($user_id);
        
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : 'V0';
         $data['lianghua_amount'] = 325346.801;   //策略总收益
         $data['todayincome'] = 0;  // 今日收益
         $data['totalincome'] = 999999.99;  // 总收益
         $data['rate'] = 0.00; // 回报
         $data['earning'] = 125.87; // 累计赚取
          $data['ernning'] = 6232325.87; // 当前收益
         $data['usenum'] = 336388; // 使用人数
         $nowday = date('Ymd',time()); 
          $nowtime = date('Y-m-d H:i:s',time()); 
          $info['data'] = AiCurrency::where('status',1)->where('hot',1)->select("id","invest",'rate',"purchased_number as number")->orderBy('id','ASC')->get();
         
           foreach ($info['data'] as $k=>$v){
                      $invest = $v['invest'];
                       $arr=[];
                      $c= explode("/",$invest);
                      foreach ($c as $k1=>$v1){
                             $Currencys =  Currency::where('name',$v1)->first();
          
                              $arr[$k1][$v1]= $Currencys?$Currencys->logo:'';
                      }
           
                $info['data'][$k]['logo'] =$arr;
        }
          
          
          
           $info['square'] = AiCurrency::where('status',1)->select("id","name","invest","days",'rate',"ratemax","amount","type","purchased_number as number")->orderBy('id','ASC')->get();
           
           
             
           foreach ($info['square'] as $k=>$v){
                      $invest = $v['invest'];
                      $c= explode("/",$invest);
                      $arri=[];
                      foreach ($c as $k1=>$v1){
                             $Currencys =  Currency::where('name',$v1)->first();
          
                              $arri[$k1][$v1]= $Currencys?$Currencys->logo:'';
                      }
           
                $info['square'][$k]['logo'] =$arri;
        }
           
           
           
            $info['scroll'] = AiOrder::where('status',0)->select("id","user_id")->orderBy('id','ASC')->get();
             foreach ($info['scroll'] as $k=>$v){
                 
                 $email = Users::where('id',$v['user_id'])->value('email');
                 
               
                  
                 $info['scroll'][$k]['email'] =substr($email,0,3)."***".strstr($email,'@');
             }
           
           
           
           $invest ="BTC+ETH+ATOM";
           $invest = explode("+",$invest);
           $arr =[];
       foreach ($invest as $k=>$v){
          
           $Currencys =  Currency::where('name',$v)->first();
           $arr[$k]['name']= $v;
           $arr[$k]['logo']= $Currencys?$Currencys->logo:'';
          
        //   $arr[$k]['change']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('change');
           $arr[$k]['now_price']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('now_price');
        }
        
        
       //   return $this->error($nowday);
         
        // $data['lianghua_amount']   = AiOrder::where('user_id',$user_id)->where('today',$nowday)->sum('amount');
      /*
         $data['lianghua_amount']   = AiOrder::where('user_id',$user_id)->where('expire','>=',$nowtime)->where('created','<=',$nowtime)->sum('amount');
         $data['todayincome']       = AiOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
         $data['totalincome']       = AiOrder::where('user_id',$user_id)->where('today',$nowday)->sum('totalincome');
         */
         if($user_id){
       //  $data['lianghua_amount']   = AiOrder::where('user_id',$user_id)->where('status',0)->sum('amount');
           $data['todayincome']       = AiOrder::where('user_id',$user_id)->where('today',$nowday)->sum('todayincome');
         
        // $data['totalincome']       = AiOrder::where('user_id',$user_id)->sum('totalincome');
         }
          $data['VIP']       = $user['user_level'] > 0 ?$userLevel['name'] : 'V0';
        if($data['lianghua_amount']>0)  $data['rate']              = round(bcdiv($data['todayincome'],$data['lianghua_amount'],4)*100,4);  // 回报率
           
        
         
        $info =  array_merge($info,$data);
        
       
        
        
       
        return $this->success(array("info"=>$info,"currency"=>$arr));
        
    }
    
    //查看我的理财订单
    public function ai_list(Request $request){
        $limit = $request->get('limit', 100);
        $status = $request->get('status',0);
       
        $user_id = Users::getUserId(); //99920326;
        
        $user = Users::find($user_id);
        
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : 'V0';
       // $list = DB::table('Ai_order')->select('Ai_order.*','Ai_currency.name')->join('Ai_currency', 'Ai_currency.id', '=', 'Ai_order.Ai_id')->where('Ai_order.user_id', $user_id)->where('Ai_order.status', $status)->orderBy('Ai_order.id', 'desc')->paginate($limit);
            
      $list = AiOrder::where('user_id', $user_id)->where('status', $status)->select('id','ai_id','name','day','amount','number','total','commission','totalincome','startdate','expire','status')->orderBy('id', 'desc')->paginate($limit);
      
      
       foreach ($list as $k=>$v){
                      $invest = $v['name'];
                      $c= explode("/",$invest);
                      $arri=[];
                      foreach ($c as $k1=>$v1){
                             $Currencys =  Currency::where('name',$v1)->first();
          
                              $arri[$k1][$v1]= $Currencys?$Currencys->logo:'';
                      }
               $list[$k]['type'] = AiCurrency::where('id',$v['ai_id'])->value('type')??'Martingale';
               $list[$k]['logo'] =$arri;
        }
      
      
      
      
      

        return $this->success(array(
            "list" => $list->items(),
            "limit" => $limit,
            'VIP'=> $user['user_level'] > 0 ?$userLevel['name'] : 'V0',
        ));
    }
    
    
     //理财撤单
    public function Aicancle(Request $request){
        $id =  $request->post('id');
        
        $user_id =  Users::getUserId(); 
       
        
        if(empty($id) || !is_numeric($id)){
            return $this->error('Parameter error：ID Is NULL');
        }   
     
        $Ai_order = AiOrder::where('id',$id)->where('status',0)->first();
        
        $amount = $Ai_order->amount;
        if(empty($amount)){
            return $this->error('Parameter error：amount Is NULL');
        }  
        $liquidateddamages = $Ai_order->liquidateddamages;
        $todayincome = $Ai_order->todayincome;
        $totalincome= $Ai_order->totalincome; 
       
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",3)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
         
       
         //   $Ai_order->remaining_number = $Ai_order->remaining_number +1;
          //  $Ai_order->purchased_number = $Ai_order->purchased_number -1;
            $Ai_order->status = 1;
            $Ai_order->amount = 0;
            $Ai_order->todayincome=0;
            $Ai_order->totalincome=0;
            $amount = $amount - $amount*$liquidateddamages/100; 
            
           
            
            $result =  change_wallet_balance($user_walllet , 2 , $amount , AccountLog::USER_LOAN_ORDER_RETURN,'组合投资返本');
            if($result){
                $Ai_order->save(); 
                return $this->success('操作成功');
                
            } 
        
    }
    
    
    
    
    
    
    //购买理财
    public function buyAi(Request $request){
        $id = $request->post('id');
        $num =  $request->post('num');
        $user_id = Users::getUserId();//99920326;
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
        
      //  if(Cache::has("by_Ai_order_$user_id")){
       //     return $this->error('Do not repeat the operation!'); 
       // }
      //  Cache::put("by_Ai_order_$user_id", 1, Carbon::now()->addSeconds(5));//禁止重复提交
        
       // $count = AiOrder::where('user_id',$user_id)->where('Ai_id',$id)->count();
        $Ai_currencys = AiCurrency::where('id',$id)->first();
       
        
        $expire =   time()+$Ai_currencys->days*24*60*60;
        //if($Ai_currencys->end_time <= date('Y-m-d') || $Ai_currencys->status == 0){//结束时间等于今天不可购买
       //     return $this->error('Project has ended!');
      //  }
        
        
      //  if($count + $num > $Ai_currencys->user_limit){
       //     return $this->error('Purchase limit exceeded!');
      //  }
        
        $invest =  $Ai_currencys->invest;
         
       
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",3)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
        if($user_walllet->change_balance < $num){
            return $this->error('Insufficient balance!');
        }
        
            
        DB::beginTransaction();
        try{
          //  $d1=strtotime($Ai_currencys->end_time);
          //  $d2 = strtotime(date('Y-m-d'));
         //   $dayCount=round(($d1- $d2)/3600/24);
            $Ai_order = new AiOrder();
            $Ai_order->user_id = $user_id;
          //  $Ai_order->currency_id = $buy_currency;//用户购买时消耗的币种
          //  $Ai_order->Ai_id = $Ai_currencys->id;
          
            $Ai_order->order_rate = $Ai_currencys->rate;
            $Ai_order->Ai_id = $id;
            $Ai_order->name = $invest;
            $Ai_order->invest = $invest;
            $Ai_order->commission=$Ai_currencys->commission;
            $Ai_order->rate = $Ai_currencys->rate."-".$Ai_currencys->ratemax;
            $Ai_order->day = $Ai_currencys->days;
            $Ai_order->amount = $num;
            $Ai_order->expire = date('Y-m-d H:i:s',$expire);
            $Ai_order->orderid =date("YmdHis",time()).$expire; 
            $Ai_order->liquidateddamages = $Ai_currencys->liquidateddamages;
          //  $Ai_order->price = $now_price;
          //  $Ai_order->total = $total;
           $Ai_order->startdate =date('Y-m-d H:i:s',time());
            $Ai_order->created = date('Y-m-d H:i:s');
           
            
          //  $Ai_currencys->refresh();
          //  if($num > $Ai_currencys->remaining_number || $count + $num > $Ai_currencys->user_limit){//已经卖完
          //      DB::rollBack();
          //      return $this->error('Sold out!');
          //  }
        
         //  $Ai_currencys->remaining_number = $Ai_currencys->remaining_number - 1;
           $Ai_currencys->purchased_number = $Ai_currencys->purchased_number + 1;
           $Ai_currencys->save();
            
            $result =  change_wallet_balance($user_walllet , 2 , -$num , AccountLog::USER_AI_ORDER_BUY,'AI量化策略扣除');
            if($result) $Ai_order->save();
            
            DB::commit();
            return $this->success('Successful');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    public function getDetail(Request $request){
       $Ai_id = $request->get('id');
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'zh';
        $where['lang']=$lang;
       $Ai = AiCurrency::where('id',$Ai_id)->select('id','invest','days','rate','ratemax','netrate','amount','amax','commission','type','purchased_number as usenum')->first();
       $invest = explode("/",$Ai->invest);
       $arr =[];
       foreach ($invest as $k=>$v){
          
           $Currencys =  Currency::where('name',$v)->first();
           $arr[$k]['name']= $v;
           $arr[$k]['logo']= $Currencys?$Currencys->logo:'';
           
          
          
          // $arr[$k]['change']= CurrencyQuotation::where('currency_id',$Currencys->id)->value('change');
            
        }
          $Ai['news'] = News::where($where)->where('type',$Ai->type)->select('id','abstract','content','cover','thumbnail','title')->first();
          
          $Ai['totalamonut'] = AiOrder::where('ai_id',$Ai->id)->sum('amount')??0.00;
          
          $t3= time()-3*86400;
          $t7= time()-7*86400;
          $t10= time()-10*86400;
          $now3 = date('Y-m-d H:i:s',$t3);
          $now7 = date('Y-m-d H:i:s',$t7);
          $now10 = date('Y-m-d H:i:s',$t10);
          $Ai['day3']= number_format(AiOrder::random_float(4.18,9.88),2);//AiOrder::where('ai_id',$Ai->id)->where('created','>=',$now3)->sum('amount')??0.00;
          $Ai['day7']= number_format(AiOrder::random_float(10.18,16.88),2);//AiOrder::where('ai_id',$Ai->id)->where('created','>=',$now7)->sum('amount')??0.00;
          $Ai['day10']= number_format(AiOrder::random_float(17.18,25.88),2);//AiOrder::where('ai_id',$Ai->id)->where('created','>=',$now10)->sum('amount')??0.00;
          
          $user_id =  Users::getUserId(); 
          $account = UsersWallet::where('user_id',$user_id)->where('currency',3)->value('change_balance')??"0.00"; //可用USDT
      // print_r($arr);exit;
          return $this->success(array(
            "detail" => $Ai,
            "Currency" => $arr,
            'balance' => $account,
        ));
       // return $this->success($Ai); 
    }
    
    
    
public function  order_detail(Request $request){
       $id =$request->get('id');
       $Ai = AiList::where('order_id',$id)->select('id','amount','addtime')->orderBy('id','ASC')->get();
       $total=  AiList::where('order_id',$id)->sum('amount');
       foreach ($Ai as $k=>$v){
       $Ai[$k]['addtime']= date('Y-m-d H:i:s',$v->addtime);
       }
       return $this->success(array(
            "detail" => $Ai,
            "total" => $total,
        ));
     
        return $this->success($Ai); 
    }
    
    
}