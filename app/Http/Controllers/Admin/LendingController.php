<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use App\Admin;
use App\Users;
use App\Currency;
use App\UsersWallet;
use App\AccountLog;
use App\UserJiedai;
use App\Defi;
use App\DefiOrder;
use App\DualCurrency;
use Illuminate\Support\Facades\DB;

class LendingController extends Controller
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
        
        return view('admin.lending.index');
    }
    
    public function order(){
        
        
        return view('admin.lending.order');
    }
    
    //订单列表
    public function order_lists(Request $request){
        
        $limit = $request->get('limit', 20);
        $htcode = $request->get('htcode');
        $user_id = $request->get('user_id');
        
        $list = DB::table('defi_order');
            
        if (!empty($htcode)) {
            $list = $list->where('htcode',$htcode);
        }
        
        if($user_id){
            $list->where('user_id',$user_id);
        }
        
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        file_put_contents('/www/wwwroot/crypto/public/t.txt',json_encode($list));
        
        foreach ($list as $v){
         //   $v->create_time = date('Y-m-d H:i:s',$v->create_time);
        }
        
       
        return $this->layuiData($list);
    }
    
    public function add(){
        return view('admin.lending.add');
    }
    
    
    public function editLendingView(Request $request){
        $id = $request->get('id');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $dual_currency =  DB::table('defi')->where(['id'=>$id])->first();
        // dump($dual_currency);die;
        return view('admin.lending.edit_lending',['results' => $dual_currency]);
    }
    
      //删除项目
     public function del(Request $request){
        $id = $request->post('id');

// file_put_contents('/www/wwwroot/xb-dex/public/d.txt',time().$id.PHP_EOL,FILE_APPEND);
         $Zhiya_lk = Defi::where('id',$id)->first();//->get();
         
         
        if(empty($Zhiya_lk)){
            return $this->error('不存在');
        }
       
        if($id){
            //whereNotIn
            
           DB::table('defi')->where('id', $id)->delete();
            
          return $this->success('操作成功');
        }else{
            
          return $this->error('失败');  
        }
    }
    
    
    
    
    //编辑项目
    public function editLending(Request $request){
        $id = $request->post('id');
      //  $title = $request->post('title');
        $days = $request->post('days');
       // $hk_type = $request->post('hk_type');
        //$amount = $request->post('amount');
      //  $amax = $request->post('amax');
        $bilv = $request->post('bilv');
        $percent = $request->post('percent');
        $opaymentlv = $request->post('opaymentlv');
         $fwamount = $request->post('fwamount');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $dual_currency = Defi::where(['id'=>$id])->first();
        
        if($bilv >100){
            return $this->error('利率有误');
        }
       // $dual_currency->title = $title;
        $dual_currency->days = $days;
       // $dual_currency->amount = $amount;
       // $dual_currency->amax   = $amax;
      //  $dual_currency->hk_type = $hk_type;
        $dual_currency->bilv = $bilv;
        $dual_currency->percent = $percent;
        $dual_currency->fwamount = $fwamount;
        $dual_currency->opaymentlv = $opaymentlv;
        $dual_currency->save();
        
        
        return $this->success(['data'=>$dual_currency]);
    }
    
    //新增项目
    public function saveLending(Request $request){
        $data = $request->all();
        
        // $amount = $data['amount'];
       //  $title = $data['title'];
         $days = $data['days'];
         $bilv = $data['bilv'];
           $percent =  $data['percent'];
        $opaymentlv =  $data['opaymentlv'];
         $fwamount =  $data['fwamount'];
       //  $hk_type = $data['hk_type'];
        if($days && $bilv){
            if($bilv >= 100){
                return $this->error('利率不能大于100%'); 
            }
            
            $dual_currency = new Defi();
            
          //  $dual_currency->amount = $amount;
           // $dual_currency->title = $title;
            $dual_currency->days = $days;
            $dual_currency->bilv = $bilv;
            $dual_currency->percent = $percent;
            $dual_currency->fwamount = $fwamount;
            $dual_currency->opaymentlv = $opaymentlv;
         //   $dual_currency->hk_type = $hk_type;
            $dual_currency->status = 1;
            $dual_currency->type = 1;
            $dual_currency->addtime = date("Y-m-d H:i:s",time());
            
            
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
         
       // $results = DualCurrency::where('id','>',1);
        
       $results =DB::table('defi');//->orderBy('id', 'asc')->paginate($limit); lk_zhiya
        
        if(isset($status)){
            $results->where('status',$status);
        }
        
        // dump($results);die;
        $results = $results->orderBy('id', 'desc')->orderBy('status','desc')->paginate($limit);
        
        return $this->layuiData($results);
    }
    
    public function show(Request $request)
    {
        $id = $request->get('id', '');
        if (!$id) {
            return $this->error('参数错误');
        }
        $walletout = DefiOrder::find($id);
       
      //  $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
        return view('admin.lending.edit', ['wallet_out' => $walletout]);
        
    }

    //test
    public function done(Request $request)
    {
        set_time_limit(0);
        $id = $request->post('id', '');
        $user_id = $request->post('user_id', 0);
        $amount = $request->post('amount', 0);
        $method = $request->get('method', '');
       
        if (!$id || !$user_id) {
            return $this->error('参数错误');
        }
       
        try {
            DB::beginTransaction();
            $wallet_out = DefiOrder::where('review_status', 1)->lockForUpdate()->findOrFail($id);
            
 

            $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', 3)->lockForUpdate()->first(); //lockForUpdate()->

            if ($method == 'done') {//确认提币
               
                $wallet_out->review_status = 2;//提币成功状态
                $wallet_out->status = 0;//反馈的信息
                
                $wallet_out->review_time = date('Y-m-d H:i:s',time()+$wallet_out->days*24*60*60);
                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, 2, $amount, AccountLog::USER_LOAN_ORDER_BUY, '借贷放款成功');
              
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
            } else {
                $wallet_out->review_status = 3;//提币失败状态
              
                
                $wallet_out->review_time = date('Y-m-d H:i:s',time());
                
                $wallet_out->save();
                 
            }
            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    
      //test
    public function cancle(Request $request)
    {
        set_time_limit(0);
        $id = $request->post('id', '');
      
       
        if (!$id) {
            return $this->error('参数错误');
        }
       
        try {
            DB::beginTransaction();
            $wallet_out = DefiOrder::where('review_status', 1)->lockForUpdate()->findOrFail($id);
            
 

           
                $wallet_out->review_status = 3;//提币失败状态
              
                
                $wallet_out->review_time = time();
                
                $wallet_out->save();
                 
            
            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    
}
