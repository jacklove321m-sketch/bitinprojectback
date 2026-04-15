<?php
namespace App\Http\Controllers\Api;

 
use App\Setting;
use App\Users;
use App\ChargeReq;
use App\WalletAddress;
use App\UserLevelModel;
use App\UsersWallet;
use App\AccountLog;
use App\Utils\RPC;
use Illuminate\Support\Facades\Redis;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Carbon;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    protected function makeSubscribeTopic($topic_template, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\$([a-zA-Z_]\w*)/', $topic_template, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception('topic:' . $topic_template . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\$([a-zA-Z_]\w*)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $topic_template);
    }

    public function test()
    {
        $period = '1min';
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->match_name,
                'period' => $period,
            ];
            $topic = $this->makeSubscribeTopic('market.$symbol.kline.$period', $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                //'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            print_r($sub_data);
        }
        exit();
    }
    
    
       /**
     * app  U盾回调  (Request $request)
     * @return string
     */
     public function CallBack(Request $request)
    {
       // $request =  $this->request->post();
     
        $udun_apikey = config('app.udun_apikey');
   
        $memberid = config('app.memberid');
        
         
        
        
        
     //$request =NULL;
     
     
    
    
     if(empty($request)){
         
       
          
        $result = array("status_code"=>404,"message"=>"404 Not Found");
        
        return json_encode($result);
    
      }  
     
      
 
   file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt",  "\n" . date('Y-m-d H:i:s') .json_encode($request) . "\n", FILE_APPEND);
  /*
    if(empty($request)){
       $body = '{"address":"0x4bc7709daa43fe0d08a5a55a9803fa00b066e4a3","amount":"5900000000","blockHigh":"47063475","coinType":"TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t","decimals":"6","fee":"0","mainCoinType":"195","memo":"","status":3,"tradeId":"1055873104265670656","tradeType":1,"txId":"9769f20e483cecf5e51507589f70f849bdf55d202e44842eae6e02d4a13c2e60"}'; 
       $request = array('timestamp'=>'1671582782904','nonce'=>'EDVaD6','sign'=>'20bd4d025c6324bb0325d40957e19ca6','body'=>$body); 
          $account = 1; 
           if($account == 1){$sign='987425f704f48c8cd7c0c12ca6829055';}
      }
    
    if(empty($request)){   
      
$body = '{"address":"TLng7RobC5jcNtw1XPxoyMiiifEksYEosp","amount":"5000000","blockHigh":"60241722","coinType":"TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t","decimals":"6","fee":"0","mainCoinType":"195","memo":"","status":3,"tradeId":"1221904373466329088","tradeType":1,"txId":"9f6d367313e4194e68cee9f1c7ff0b7bdfdab5ad1fbe4746a3760b36e4cd20c3"}'; 

 $request = array('timestamp'=>'1711366306497','nonce'=>'5LtilG','sign'=>'c857c2755bf18c63823e7b7e048c8840','body'=>$body); 
          $account = 1; 
           if($account == 1){$sign='987425f704f48c8cd7c0c12ca6829055';}
    
    }  
      */
        //回调示例
        $call_back_data = array(
            'timestamp' => $request['timestamp'],
            'nonce' => $request['nonce'],
            'sign' => $request['sign'],
            'body' => $request['body'],
        );
        
        
      
       file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" .json_encode($call_back_data) . "\n", FILE_APPEND); 
     

       file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" .'body: ' . $call_back_data['body'] . "\n", FILE_APPEND);

        $sign = md5($call_back_data['body'] . $udun_apikey . $call_back_data['nonce'] . $call_back_data['timestamp']);
        
       
      
    //   file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') .'nonce:'. $call_back_data['nonce']."-sign:".$call_back_data['sign']."--------".$sign ."--timestamp：".$request['timestamp']. "\n", FILE_APPEND);
    
 
        
         if ($call_back_data['sign'] == $sign) {
            $time = date("Y-m-d H:i:s",time());
            $body = json_decode($call_back_data['body']);
            
               
             file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s')  . $body->address.": 验证签名成功" . "\n", FILE_APPEND);
           
             //$body->tradeType 1充币回调 2提币回调
            if ($body->tradeType == 1) {
                

                 file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "充币回调成功" . "\n", FILE_APPEND);
                 
                 //$body->status 0待审核 1审核成功 2审核驳回 3交易成功 4交易失败
                if ($body->status == 3) {

                    //验证是否重复调用
                    $RechargesInfo =ChargeReq::where(["txid"=>$body->txId])->exists();
                    if ($RechargesInfo){
                        
                       file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt","\n" .date('Y-m-d H:i:s') . "U盾已结充值过". "\n", FILE_APPEND);
                        die;
                    }
                    
                    
                    $moeny = $body->amount/pow(10,$body->decimals);
                      file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "交易成功". "\n", FILE_APPEND);
                     
                     //业务处理
            
                     $map = ['memberid'=>$memberid,'address' => $body->address];
                     
                     $address = WalletAddress::where($map)->value('address');
                     if(!$address){
                         
                       
                          file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s')."流水号".$body->tradeId ."充币地址" .$body->address ."未找到该地址". "\n", FILE_APPEND);
                    }else{
                        
                      
                        file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "获取用户地址成功". "\n", FILE_APPEND);
                    }
                    
                  
        
       
        $address = $body->address;
        $k = 'address:' . strtolower($address);
        $user_id =  Redis::get($k);   
         file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "获取用户成功:".$user_id. "\n", FILE_APPEND);
     //   $user_id = 1086179;
        $user = Users::find($user_id);
        
       //  file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "获取用户:".json_encode($user). "\n", FILE_APPEND);
        $nick_name = '';
        if (empty($user['email'])){
            $nick_name = $user['phone'];
        }else{
            $nick_name = $user['email'];
        }
        
        $account = $user_id;
        
       //  file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "获取用户昵称:".$nick_name. "\n", FILE_APPEND);
        $type =0;
        $sub_type = 'USDT';
        $currency_id =3;
        if($body->mainCoinType == 60) {$sub_type = 'ETH';$currency_id =2;}
        if($body->mainCoinType == 195 && $body->coinType == 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t') $sub_type = 'USDT-TRC20';
        if($body->mainCoinType == 60 &&  $body->coinType == '0xdac17f958d2ee523a2206206994597c13d831ec7') $sub_type = 'USDT-ERC20';
        if($body->mainCoinType == 195 && $body->coinType == 'TEkxiTehnzSmSe2XqrBj4w32RUN966rdz8') $sub_type = 'USDC-TRC20';
        if($body->mainCoinType == 60 &&  $body->coinType == '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48') $sub_type = 'USDC-ERC20';
        if($body->mainCoinType == 195 && $body->coinType == '195') {$sub_type = 'TRX';$currency_id =19;}
        if($body->mainCoinType == 0) {$sub_type = 'BTC';$currency_id =1;}
      
       //  file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "获取币种:".$sub_type. "\n", FILE_APPEND);
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : null;
        
        $give = $userLevel ? round(($moeny * $userLevel['give'] / 100),8) : 0;
        $give_rate = $userLevel ? $userLevel['give'] : 0;
       //  file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "奖励:".$give. "\n", FILE_APPEND);    
        //日志
          $data = [
                'type'=>$type,
            	'uid' => $user_id,
            	'currency_id' => $currency_id,
            	'amount' => $moeny,
            	'price'  => $moeny,
            	'give' => $give,
            	'account_name' => $nick_name,
            	'give_rate' => $give_rate,
            	'user_account' => $account,
            	'currency_name' => 'Udun Pay',
            	'sub_type' => $sub_type,
            	'address' => $address,
            	'status' => 2,
             	'txid' => $body->txId,
            	'created_at' => date('Y-m-d H:i:s')
        	]; 
            
            $md =  Db::table('charge_req')->insert($data);
            if($md){
               //加分
            try {
             DB::beginTransaction();
               $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();


                 // $user_wallet->change_balance = $user_wallet->change_balance+$moeny+$give;
                  
                //   $save_result = $user_wallet->save();
                  
              //  Db::table('users_wallet')->where('currency',3)->where('user_id',$user_id)->update($data);
                 $change_result = change_wallet_balance($user_wallet, 2, $moeny+$give, AccountLog::ETH_EXCHANGE, '链上充币增加');
                
              
                 DB::commit();
                    file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt","\n" ."用户:".$user_id." address:".$address."，U盾充值：".$moeny. "\n", FILE_APPEND);
                 } catch (\Exception $e) {
                  DB::rollBack();
                   file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt","\n" ."用户U盾充值失败：currency_id:".$currency_id.",amount:".$moeny. "\n", FILE_APPEND);
                }
            }else{
               file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt","\n" ."用户U盾充值失败：currency_id:".$currency_id.",amount:".$moeny. "\n", FILE_APPEND);
            }
            
         
                                        
         // file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . json_encode($transaction) . "用户数据". "\n", FILE_APPEND);                             
                  
                    

                    
                }
                
             return "success";
            
            }elseif($body->tradeType == 2) {
				
				// U盾提现业务处理
				return "success";
			}
            
            
            
         }
         else
         {
                file_put_contents("/www/wwwroot/crypto/public/recharge_data.txt", "\n" . date('Y-m-d H:i:s') . "签名验证失败". "\n", FILE_APPEND);
         }
        
    }
    
}
?>