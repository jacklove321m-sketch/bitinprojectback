<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use App\Admin;
use App\Users;
use App\Currency;
use App\UserLevelModel;
use App\DualCurrency;
use App\DualOrder;
use App\AiCurrency;
use App\AiOrder;
use Illuminate\Support\Facades\DB;

class AiController extends Controller
{
    private $Month_E = array('01' => "Jan",
        '02' => "Feb",
        '03' => "Mar",
        '04' => "Apr",
        '05' => "May",
        '06' => "Jun",
        '07' => "Jul",
        '08' => "Aug",
        '09' => "Sep",
        '10' => "Oct",
        '11' => "Nov",
        '12' => "Dec");
        
    public function index(){
        
        return view('admin.ai.index');
    }
    
    public function order(){
       
        
        return view('admin.ai.order');
    }
    
    //订单列表
    public function order_lists(Request $request){
        
        $limit = $request->get('limit', 20);
        $orderid = $request->get('orderid');
        $user_id = $request->get('user_id');
        $status =  $request->get('status');
        
        
        $list = DB::table('ai_order')->join('ai_currency', 'ai_currency.id', '=', 'ai_order.ai_id');
            
      if($orderid){
            $list->where('ai_order.orderid','LIKE','%'.$orderid.'%');
        }
        
        if($status){
            $list->where('ai_order.status',$status);
        }
        
        if($user_id){
            $list->where('ai_order.user_id',$user_id);
        }
        
        $list = $list->select('ai_order.*','ai_currency.days')->orderBy('ai_order.id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }
    
    public function add(){
          $currencies = Currency::where('is_legal', 1)->get();
          $userlevel = UserLevelModel::where('status', 1)->get();
        return view('admin.ai.add')->with('currencies', $currencies)->with('userlevel', $userlevel);
    }
    
    
    public function editAiView(Request $request){
        $id = $request->get('id');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $ai_currency =  AiCurrency::where(['id'=>$id])->first();
        
         $invest = $ai_currency->invest;
        
      
         $currencies = Currency::where('is_legal', 1)->get();
          $userlevel = UserLevelModel::where('status', 1)->get();
        // dump($dual_currency);die;
        return view('admin.ai.edit_ai',['results' => $ai_currency])->with('currencies', $currencies)->with('invest', $invest)->with('userlevel', $userlevel);
    }
    
    //编辑项目
    public function editAi(Request $request){
        
         $id = $request->post('id');
        $name = $request->post('name');
        $days = $request->post('days');
        $rate = $request->post('rate');
        $ratemax = $request->post('ratemax');
        $amount = $request->post('amount');
        $amax = $request->post('amax');
        $status = $request->post('status');
        $hot = $request->post('hot');
        $commission = $request->post('commission');
      //  $total_number = $request->post('total_number');
        $invest = $request->post('invest');
         $investtr ='';
        foreach ($invest as $k=>$v){
            
          $investtr .=$v.'+';
        }
       // $liquidateddamages = $request->post('liquidateddamages');
         
        if(empty($days)){
            return $this->error('参数错误');
         } 
         if($rate >= $ratemax){
              //  return $this->error('起始回报率不能大于最大回报率'); 
        }
         
         
        if($amount >= $amax){
              //  return $this->error('起始金额不能大于最大金额'); 
         }
           
           
       
         $dual_currency =  AiCurrency::where(['id'=>$id])->first();
          $dual_currency->invest = substr($investtr, 0, strlen($investtr) - 1);
          $dual_currency->total_number = 0;//$total_number;
          $dual_currency->remaining_number =0;// $total_number;
         $dual_currency->hot = $hot;
         $dual_currency->commission = $commission;
         $dual_currency->days = $days;
         $dual_currency->name = $name;//$days."天";
         $dual_currency->rate = $rate;
          $dual_currency->ratemax = $ratemax;
        $dual_currency->amount = $amount;
        $dual_currency->amax = $amax;
        $dual_currency->liquidateddamages = 0;//$liquidateddamages;
        $dual_currency->status = $status;
        $dual_currency->save();
        
        return $this->success('操作成功');
       // return $this->success(['data'=>$dual_currency]);
    }
    
     //删除项目
     public function del(Request $request){
        $id = $request->post('id');

// file_put_contents('/www/wwwroot/xb-dex/public/d.txt',time().$id.PHP_EOL,FILE_APPEND);
         $Zhiya_lk =  AiCurrency::where('id',$id)->first();//->get();
         
         
        if(empty($Zhiya_lk)){
            return $this->error('不存在');
        }
       
        if($id){
            //whereNotIn
            
            AiCurrency::where('id', $id)->delete();
            
          return $this->success('操作成功');
        }else{
            
          return $this->error('失败');  
        }
    }
    
    
    //新增项目
    public function saveAi(Request $request){
        $data = $request->all();
        $days = $data['days'];
        $name = $data['name'];
        $amount = $data['amount'];
        $amax = $data['amax'];
        $rate = $data['rate'];
        $ratemax = $data['ratemax'];
         $hot = $data['hot'];
        $commission = $data['commission'];
        //$total_number = $data['total_number'];
        $invest = $data['invest'];
        $investtr ='';
       foreach ($invest as $k=>$v){
            
         $investtr .=$v.'/';
       }
      //  return $this->error($investtr);
        if($data['days'] && $data['ratemax'] && $data['rate'] && $data['amount'] && $data['amax']){
         
           
           
         if($rate >= $ratemax){
              //  return $this->error('起始回报率不能大于最大回报率'); 
          }
           if($amount >= $amax){
               // return $this->error('起始金额不能大于最大金额'); 
          } 
           
           // $_tp = $data['type'] =='call'? 'C':'P';
            
             
            $dual_currency = new AiCurrency();
            $dual_currency->invest = substr($investtr, 0, strlen($investtr) - 1);
            $dual_currency->total_number = 0;//$total_number;
            $dual_currency->remaining_number = 0;//$total_number;
            $dual_currency->hot = $hot;
            $dual_currency->commission = $commission;
            $dual_currency->days = $days;
            $dual_currency->name = $name;//$days."天";
            $dual_currency->rate = $data['rate'];
            $dual_currency->ratemax = $data['ratemax'];
            $dual_currency->amount = $data['amount'];
            $dual_currency->amax = $data['amax'];
            $dual_currency->liquidateddamages = 0;//$data['liquidateddamages'];
            
            $dual_currency->created = date('Y-m-d',time());
            
            
            $dual_currency->save();
            
            return $this->success('操作成功');
        }else{
            
          return $this->error('必填项不能为空');  
        }
    }
    
    //获取实时价格
    public function getNewPrice(Request $request){
        $currency = $request->get('currency');
        $currencys = Currency::where('id',$currency)->get()->toArray();
        
        return $this->success(['data'=>$currencys]);
    }
    
    //理财设置
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 20);
        $status = $request->get('status');
       
        $results = AiCurrency::where('id','>',1);
        
        
        if(isset($status)){
            $results->where('status',$status);
        }
        
        // dump($results);die;
        $results = $results->orderBy('id', 'desc')->orderBy('status','desc')->paginate($limit);
      
        
        return $this->layuiData($results);
    }
    
    
    
}
