<?php

namespace App\Http\Controllers\Api;

  
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Service\RedisService;
use App\Utils\RPC;
use App\Currency;
use App\CurrencyMatch;
use App\TransactionComplete;
use App\Users;
use App\MarketHour;
use App\CurrencyQuotation;
use App\AreaCode;
use App\UdunAddress;
use App\UsersWallet;
use App\WalletAddress;
use App\Http\mode\Sina as sina;

class CurrencyController extends Controller
{
    public function area_code()
    {
        $LHaaruJ = AreaCode::get()->toArray();
        return $this->success($LHaaruJ);
    }
    public function rangeNew(){
        $hot = DB::table('currency_quotation')
            ->join('currency', 'currency_quotation.currency_id', '=', 'currency.id')
            ->where('currency.is_display', 1)
            ->where('currency.is_legal', 1)
            ->orderBy("currency_quotation.volume","desc")
            ///->where('currency_quotation.change','>', 0)
            ->limit(100)
            ->get();
            
        $increase = DB::table('currency_quotation')
            ->join('currency', 'currency_quotation.currency_id', '=', 'currency.id')
            ->where('currency.is_display', 1)
            ->where('currency.is_legal', 1)
            //->where('currency_quotation.change','>', 0)
            ->orderBy("currency_quotation.change","desc")
            ->limit(100)
            ->get();
            $currency['hot'] = $hot;
             $currency['increase'] = $increase;
        return $this->success($currency);
    }
    
    public function bizcate(){
        $data = DB::table('lh_deposit_config')->join('currency','currency.id','=','currency_id')
            ->groupBy('currency_id')
            ->select(['currency.name as currency_name','currency.logo as currency_logo','lh_deposit_config.*'])
            ->get();
        return $this->success($data);
    }
  
    
    
    public function listsRecharge()
    {
       
        $Key = config('app.udun_apikey');
   
        $memberid = config('app.memberid'); 
        $HttpUrl = config('app.gateway');
        $CallUrl = config('app.callback');
        
        $udun_enable = config('app.udun_enable');
        
       
        $list = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $array = array();
        
        if($udun_enable==true) {
            
            
             $list = UdunAddress::where('status', 1)->orderBy('id', 'asc')->get()->toArray();
            
             $array = array();
             foreach ($list as $v) {
                 
                $walletaddress= $this->xcreateAddress($v['chain_id'],$memberid,$v['name'],$v['contract']);
                $v['address_erc'] = $v['address_omni'] =$walletaddress['data']['address'];
                $v['vcode'] = $v['real_name'];
               // if($v['chain_id']==60 && $v['name']=='USDT') $v['name'] = $v['name'].'-ERC20';
               // if($v['chain_id']==195 && $v['name']=='TRX') $v['name'] = 'USDT-TRC20';
                 array_push($array, $v);
             }
            
            
        }else{
        
         foreach ($list as $v) {
            if ($v['address_erc'] !='' && $v['address_omni'] !='') {
                $a=$v;
                $a['name']=$a['name'].'-ERC20';
                array_push($array, $a);
                
                $b=$v;
                $b['name']=$b['name'].'-TRC20';
                $b['address_erc']=$b['address_omni'];
                array_push($array, $b);
            }else{
                if($v['address_erc'] !=''){
                    array_push($array, $v);
                }
                if($v['address_omni'] !=''){
                    
                    array_push($array, $v);
                }
            }
            
        } 
        
        }
         
        return $this->success(array('currency' => $list, 'udun_enable'=>$udun_enable,'recharge' => $array));
    }
    
    
      public  function xcreateAddress(int $coinType,$MerchantId,$CoinName,$contract)
        {
              
            $Key     = config('app.udun_apikey');
            $HttpUrl = config('app.gateway');
            $CallUrl = config('app.callback');
            $memberid = config('app.memberid'); 
            
           // if($coinType ==0) $currency_id='BTC';
           // if($coinType ==60) $currency_id='ETH';
           // if($coinType ==195) $currency_id='TRX';
              
            
              $user_id = Users::getUserId();
         //  $map = array('memberid'=>$memberid,'user_id' => $user_id, 'chain_id' => $coinType);
        
           if(!$user_id) return $this->error("非法用户");
        
        
         
       
        if(!WalletAddress::where('memberid',$memberid)->where('user_id',$user_id)->where('chain_id',$coinType)->where('contract',$contract)->where('currency_id',$CoinName)->first()){
            
           
            
           $body = array(
                'merchantId' => $MerchantId,
                'coinType' => $coinType,
                'callUrl' => $CallUrl,
            );
            
            
           
            $Timestamp = time();
            $Nonce = rand(100000,999999);
           
            $body = '['.json_encode($body).']';
            $timestamp = $Timestamp;
            $nonce = $Nonce;

            $url = $HttpUrl.'/mch/address/create';
            $key = $Key;

            $sign = md5($body.$key.$nonce.$timestamp);

            $data = array(
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'sign' => $sign,
                'body' => $body
            );
            $data_string = json_encode($data);
            
          
           $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-AjaxPro-Method:ShowList',
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
            );
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            $data = curl_exec($ch);
            
            curl_close($ch);
            
            if(is_object($data)) {
            $data = (array)$data;
             }
             
            $data_array = json_decode($data,true); 
            
             
            
            if($data_array['code']!=200 ){
                  //   file_put_contents('/www/wwwroot/crypto/public/walletaddress.txt',time().json_encode($data_array).PHP_EOL,FILE_APPEND);       
                     return $this->error("用户id:".$user_id." chian_id : ".$id."地址生成失败");
                     
            }
          
            $newdata['memberid'] =$memberid;
            
            $newdata['chain_id'] = $coinType;
            $newdata['contract'] = $contract;
            
            $newdata['currency_id']  = $CoinName;
            
            $newdata['user_id']  = $user_id;
            
            $newdata['address']  = $data_array['data']['address']; 
            
            DB::table('wallet_address')->insert($newdata);
           
          /*  WalletAddress::create([
                'memberid'=>$memberid,
                'chain_id'=>$coinType,
                'currency_id'=>$currency_id,
                'user_id'=>$user_id,
                'address'=>$data_array['data']['address'],
                ]);
           
            
           WalletAddress::create($newdata);
           */
           
           
         Redis::set('address:' . strtolower($data_array['data']['address']),$user_id);
             
            
          
        }else{
           
            
           $data_array['data'] =  WalletAddress::where('memberid',$memberid)->where('user_id',$user_id)->where('chain_id',$coinType)->where('contract',$contract)->where('currency_id',$CoinName)->first();
            
        }
          
          return $data_array;
            
        }
    
    
    
     public  function createAddress(int $coinType,$MerchantId,$CoinName)
        {
              
            $Key     = config('app.udun_apikey');
            $HttpUrl = config('app.gateway');
            $CallUrl = config('app.callback');
            $memberid = config('app.memberid'); 
            
           // if($coinType ==0) $currency_id='BTC';
           // if($coinType ==60) $currency_id='ETH';
           // if($coinType ==195) $currency_id='TRX';
              
            
              $user_id = Users::getUserId();
         //  $map = array('memberid'=>$memberid,'user_id' => $user_id, 'chain_id' => $coinType);
        
           if(!$user_id) return $this->error("非法用户");
        
        
         
       
        if(!WalletAddress::where('memberid',$memberid)->where('user_id',$user_id)->where('chain_id',$coinType)->where('currency_id',$CoinName)->first()){
            
           
            
           $body = array(
                'merchantId' => $MerchantId,
                'coinType' => $coinType,
                'callUrl' => $CallUrl,
            );
            
            
           
            $Timestamp = time();
            $Nonce = rand(100000,999999);
           
            $body = '['.json_encode($body).']';
            $timestamp = $Timestamp;
            $nonce = $Nonce;

            $url = $HttpUrl.'/mch/address/create';
            $key = $Key;

            $sign = md5($body.$key.$nonce.$timestamp);

            $data = array(
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'sign' => $sign,
                'body' => $body
            );
            $data_string = json_encode($data);
            
          
           $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-AjaxPro-Method:ShowList',
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
            );
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            $data = curl_exec($ch);
            
            curl_close($ch);
            
            if(is_object($data)) {
            $data = (array)$data;
             }
             
            $data_array = json_decode($data,true); 
            
             
            
            if($data_array['code']!=200 ){
                  //   file_put_contents('/www/wwwroot/crypto/public/walletaddress.txt',time().json_encode($data_array).PHP_EOL,FILE_APPEND);       
                     return $this->error("用户id:".$user_id." chian_id : ".$id."地址生成失败");
                     
            }
          
            $newdata['memberid'] =$memberid;
            
            $newdata['chain_id'] = $coinType;
            
            $newdata['currency_id']  = $CoinName;
            
            $newdata['user_id']  = $user_id;
            
            $newdata['address']  = $data_array['data']['address']; 
            
            DB::table('wallet_address')->insert($newdata);
           
          /*  WalletAddress::create([
                'memberid'=>$memberid,
                'chain_id'=>$coinType,
                'currency_id'=>$currency_id,
                'user_id'=>$user_id,
                'address'=>$data_array['data']['address'],
                ]);
           
            
           WalletAddress::create($newdata);
           */
           
           
            Redis::set('address:' . strtolower($data_array['data']['address']),$user_id);
             
            
          
        }else{
            
           $data_array['data'] =  WalletAddress::where('memberid',$memberid)->where('user_id',$user_id)->where('chain_id',$coinType)->where('currency_id',$CoinName)->first();
            
        }
          
          return $data_array;
            
        }
    
    public function address(int $id): array
    {
       
       $Key = config('app.udun_apikey');
   
        $memberid = config('app.memberid'); 
        $HttpUrl = config('app.gateway');
        $CallUrl = config('app.callback');
        $memberid = config('memberid');
        $user_id = Users::getUserId();
        $map = ['memberid'=>$memberid,'user_id' => $user_id, 'chain_id' => $id];
        if(!WalletAddress::query()->where($map)->exists()){
            $coinType = 0; 
          
            switch ((int)$id){
                case 3:
                    $coinType = 60;
                    $currency_id = 2;
                    break;
                case 4:
                    $coinType = 60;
                    $currency_id = 2;
                    break;
                case 1:
                    $coinType = 195;
                    $currency_id = 10;
                    break;
                case 2:
                    $coinType = 0;
                    $currency_id = 1;
                    break;
                case 5:
                    $coinType = 0;
                    $currency_id = 1;
                    break;
            }
            
            $data_array = $this->createAddress($coinType,$memberid);//,$CallUrl,$HttpUrl,$Key
           
            $data['memberid'] =$memberid;
            
            $data['chain_id'] = $id;
            
            $data['currency_id']  = $currency_id;
            
            $data['user_id']  = $user_id;
            
            $data['address']  = $data_array['data']['address'];
            
         //  file_put_contents('/www/wwwroot/server/public/t.txt',$memberid."---".json_encode($data_array),FILE_APPEND);
            
            if($data_array['code']!=200 ){
                     return $this->error("用户id:".$user_id." chian_id : ".$id."地址生成失败");
            }else{
                     WalletAddress::query()->create($data);
                    Redis::set('address:' . strtolower($data_array['data']['address']),$user_id);
                }
            
            
             
            $address = $data_array['data']['address'];
        }else{
            $address =WalletAddress::query()->where($map)->value('address');
        }
        return ['address' => $address];
    }
    
    public function lists()
    {
        $CnrtCHJ = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $ZwZsbXQ = array();
        foreach ($CnrtCHJ as $LkZuCYJ) {
            if ($LkZuCYJ['is_legal']) {
                array_push($ZwZsbXQ, $LkZuCYJ);
            }
        }
        return $this->success(array('currency' => $CnrtCHJ, 'legal' => $ZwZsbXQ));
    }
    public function lever()
    {
        $ORXHJHJ = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $GDKcLQQ = array();
        foreach ($ORXHJHJ as $tvYImDv) {
            if ($tvYImDv['is_lever']) {
                array_push($GDKcLQQ, $tvYImDv);
            }
        }
        $QQXtbYJ = strtotime(date('Y-m-d'));
        foreach ($GDKcLQQ as $lZliuuJ) {
            $NCAnfzQ = array();
            foreach ($ORXHJHJ as $wlhJtsJ) {
                if ($wlhJtsJ['id'] != $lZliuuJ['id']) {
                    $kbWrlVv = 0;
                    $qpXftSJ = 0;
                    $ilDGdcQ = '';
                    $DpAftsJ = '';
                    $VVYVhSv = 0.0;
                    $ilDGdcQ = TransactionComplete::orderBy('create_time', 'desc')->where('currency', $wlhJtsJ['id'])->where('legal', $lZliuuJ['id'])->first();
                    $DpAftsJ = TransactionComplete::orderBy('create_time', 'desc')->where('create_time', '<', $QQXtbYJ)->where('currency', $wlhJtsJ['id'])->where('legal', $lZliuuJ['id'])->first();
                    !empty($ilDGdcQ) && ($kbWrlVv = $ilDGdcQ->price);
                    !empty($DpAftsJ) && ($qpXftSJ = $DpAftsJ->price);
                    if (empty($kbWrlVv)) {
                        if ($qpXftSJ) {
                            $VVYVhSv = -100.0;
                        }
                    } else {
                        if ($qpXftSJ) {
                            $VVYVhSv = ($kbWrlVv - $qpXftSJ) / $qpXftSJ;
                        } else {
                            $VVYVhSv = 100.0;
                        }
                    }
                    array_push($NCAnfzQ, array('id' => $wlhJtsJ['id'], 'name' => $wlhJtsJ['name'], 'last_price' => $kbWrlVv, 'proportion' => $VVYVhSv, 'yesterday_last_price' => $qpXftSJ));
                }
            }
            $lZliuuJ['quotation'] = $NCAnfzQ;
        }
        return $this->success($GDKcLQQ);
    }
    public function TradeMarket(Request $request)
    {
        $quo = Currency::find($request->input('legal_id'))->name;
        $base = Currency::find($request->input('currency_id'))->name;
        $currencyInfo=Currency::where('id',$request->input('currency_id'))->first();
        $sisValue  =$currencyInfo->oncontact;

        $symbol = strtolower($base . $quo);
//        var_dump($base,$quo);
//        die;
        $url = "https://api.huobi.pro/market/history/trade?symbol={$symbol}&size=20";
        $res = json_decode(file_get_contents($url), true);


        $rsp = [];
        if ($res['status']=='ok') {

            foreach ($res['data'] as $val) {
                if (count($rsp) >= 20) {
                    break;
                }
                array_walk($val['data'], function (&$v) use (& $rsp,$sisValue) {

                    $v['time'] = date('H:i:s', intVal($v['ts'] / 1000));
                    if (count($rsp) >= 20) {

                    } else {
                         if($sisValue  != 0){
                                        $v['price']   = round($v['price'] + $sisValue,8);
                                    }  
                        
                        $rsp[] = $v;
                    }
                });
            }
        }

//        var_dump($rsp);
        return $this->success($rsp);
    }
    public function quotation_tian()
    {
        $BrsWBjv = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $mOnknFv = array();
        foreach ($BrsWBjv as $qMSPJTQ) {
            if ($qMSPJTQ['is_legal']) {
                array_push($mOnknFv, $qMSPJTQ);
            }
        }
        $fjllgVJ = strtotime(date('Y-m-d'));
        foreach ($mOnknFv as $zlqdSQv) {
            $XSjKELv = array();
            foreach ($BrsWBjv as $NAaKMjv => $UNHGjLv) {
                $zlqdSQv['quotation'] = CurrencyQuotation::orderBy('add_time', 'desc')->where('legal_id', $zlqdSQv['id'])->get()->toArray();
            }
        }
        return $this->success($mOnknFv);
    }
    
    //获取单个行情 2024
    public function exDeal(){
        $legal_id = Input::get("legal_id");
        $currency_id = Input::get("currency_id");

        if (empty($legal_id) || empty($currency_id))
            return $this->error("参数错误");
            
        $arr = CurrencyMatch::where("currency_id",$currency_id)
            ->where("legal_id",$legal_id)
            ->where('is_display', 1)->first();
            
        return $this->success($arr);
        
    }
    
    
      //币种分类
     public function b_class(Request $request){
        
        $result =DB::table('currency_class')->where('is_display',1)->orderBy('sort','asc')->get();
        
        $data =  [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
        return $data;
       // echo json_encode($data);
    }
    
     public function sinatest(){
        $sina = new sina();
       // $currency_list = CurrencyMatch::forward_sj();
      //  echo json_encode($currency_list);
       // $name = 'JPY';
       // echo json_encode($sina->foreign($name));
        
          $name = 'CBK';
      //  echo json_encode($sina->real());
          $period='1min';
        echo json_encode($sina->real_gu($name));
    }
    
    
    
    
    
    public function test(){
        $sina = new sina();
        echo $sina->real().'<br>';
        echo $sina->foreign_real().'<br>';
        echo $sina->etfreal().'<br>'; 
        echo $sina->gpreal().'<br>';
        /*
        $currency_list = CurrencyMatch::forward_sj();
        echo json_encode($currency_list);
        $name = 'JPY';
        echo json_encode($sina->foreign($name));*/
    }
    
    
     public function sina_s($name,$period){
         
        $sina = new sina();
        $result =  $sina->getwaipanKline($name,$period,300);
        $result = array_map(function ($value) {
            return $this->normalizeKlineRow($value);
        }, $result);
        
         //  file_put_contents('/www/wwwroot/crypto/public/t.txt',$period."--".$name);

        $data =  [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
        return $data;
        echo json_encode($data);
    }
    public function foreign($name,$period){
   
        
        $sina = new sina();
        $result =  $sina->getKline($name,$period,300);
        $result = array_map(function ($value) {
            return $this->normalizeKlineRow($value);
        }, $result);
        
        $data =  [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
        return $data;
        echo json_encode($data);
    }
    
     public function gp($name,$period){
   
        
        $sina = new sina();
        $result =  $sina->geteftgp($name,$period,300);
        $result = array_map(function ($value) {
            return $this->normalizeKlineRow($value);
        }, $result);
        
        $data =  [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
        return $data;
       
    }
    
    
    public function newTimeshars(Request $request)
    {
        
        $iriUDGJ = $request->get('symbol');
        $VmCMUNQ = $request->get('period');
        $biRnHJv = $request->get('from', null);
        $cAMDfuJ = $request->get('to', null);
        $iriUDGJ = strtoupper($iriUDGJ);
        $KuPpbTJ = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1D' => 4, '1W' => 8, '1M' => 9, '1day' => 4, '1week' => 8, '1mon' => 9, '1year' => 10];
        $lKNcYmQ = array_keys($KuPpbTJ);
        $fVvRJYJ = array_values($KuPpbTJ);
        if ($biRnHJv == null || $cAMDfuJ == null) {
            return ['code' => -1, 'msg' => 'error: start time or end time must be filled in', 'data' => null];
        }
        if ($biRnHJv > $cAMDfuJ) {
            return ['code' => -1, 'msg' => 'error: start time should not exceed the end time.', 'data' => null];
        }
        if ($iriUDGJ == '' || stripos($iriUDGJ, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => null];
        }
        if ($VmCMUNQ == '' || !in_array($VmCMUNQ, $lKNcYmQ)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => null];
        }
        $adlYLXQ = strtotime(date('Y-m-d H:i'));
        if ($VmCMUNQ == '1min' && $cAMDfuJ >= $adlYLXQ) {
            $cAMDfuJ = $adlYLXQ - 1;
        }
        
        
        $mjWkkrv = $KuPpbTJ[$VmCMUNQ];
        $iriUDGJ = explode('/', $iriUDGJ);
        list($HSQfSkJ, $JnRWhhQ) = $iriUDGJ;
        $HSQfSkJ = Currency::where('name', $HSQfSkJ)->where('is_display', 1)->first();
        $JnRWhhQ = Currency::where('name', $JnRWhhQ)->where('is_display', 1)->where('is_legal', 1)->first();
        if (!$HSQfSkJ || !$JnRWhhQ) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }
        $jqbYHhJ = $JnRWhhQ->id;
        $ElYLALJ = $HSQfSkJ->id;
        $fFVCeHJ = MarketHour::orderBy('day_time', 'asc')->where('currency_id', $ElYLALJ)->where('legal_id', $jqbYHhJ)->where('type', $mjWkkrv)->where('day_time', '>=', $biRnHJv)->where('day_time', '<=', $cAMDfuJ)->get();
        $rXDaOwJ = array();
        if ($fFVCeHJ) {
            foreach ($fFVCeHJ as $qhNdsMJ => $ncfMSiJ) {
                $KuIMXuv = array('open' => $ncfMSiJ->start_price, 'close' => $ncfMSiJ->end_price, 'high' => $ncfMSiJ->highest, 'low' => $ncfMSiJ->mminimum, 'volume' => $ncfMSiJ->number, 'time' => $ncfMSiJ->day_time * 1000);
                array_push($rXDaOwJ, $KuIMXuv);
            }
        } else {
            foreach ($fFVCeHJ as $qhNdsMJ => $ncfMSiJ) {
                $KuIMXuv = null;
                array_push($rXDaOwJ, $KuIMXuv);
            }
        }
        return ['code' => 1, 'msg' => 'success:)', 'data' => $rXDaOwJ];
    }
    
    
    public function klineMarket(Request $request)
    {
        
//        die('dsa');
        $symbol = $request->input('symbol');
        $period = $request->input('period');
        $from = $request->input('from', null);
        $to = $request->input('to', null);
   
        $str = explode('/',$symbol);
        $currency = DB::table('currency')->where('name',$str[0])->first();
        $currency = json_encode($currency);
        $currency = json_decode($currency,true);
        $currency_matches = DB::table('currency_matches')->where('currency_id',$currency['id'])->first();
        $currency_matches = json_encode($currency_matches);
        $currency_matches = json_decode($currency_matches,true);
       // $currency_matches['market_from'];
         
        
            if($currency_matches['market_from']==3){///999
               
              return $this->sina_s($str[0],$period);
            }///999
            
            if($currency_matches['market_from']==0){///999
            
              return $this->foreign($str[0],$period);
            }
            
             if($currency_matches['market_from']==6 || $currency_matches['market_from']==9){///999
            
              return $this->gp($str[0],$period);
            }
            
              
        
        
        
        $symbol = strtoupper($symbol);
        $result = [];
        //类型，1=15分钟，2=1小时，3=4小时,4=一天,5=分时,6=5分钟，7=30分钟,8=一周，9=一月,10=一年
        $period_list = [
            '1min' => '1min',
            '5min' => '5min',
            '15min' => '15min',
            '30min' => '30min',
            '60min' => '60min',
            '1H' => '60min',
            '1D' => '1day',
            '1W' => '1week',
            '1M' => '1mon',
            '1Y' => '1year',
            '1day' => '1day',
            '1week' => '1week',
            '1mon' => '1mon',
            '1year' => '1year',
        ];
        if ($from == null || $to == null) {
            return [
                'code' => -1,
                'msg' => 'error: from time or to time must be filled in',
                'data' => $result,
            ];
        }
        if ($from > $to) {
            return [
                'code' => -1,
                'msg' => 'error: from time should not exceed the to time.',
                'data' => $result,
            ];
        }
        $periods = array_keys($period_list);
        if ($period == '' || !in_array($period, $periods)) {
            return [
                'code' => -1,
                'msg' => 'error: period invalid',
                'data' => $result,
            ];
        }
        if ($symbol == '' || stripos($symbol, '/') === false) {
            return [
                'code' => -1,
                'msg' => 'error: symbol invalid',
                'data' => $result,
            ];
        }
        $period = $period_list[$period];
        list($base_currency, $quote_currency) = explode('/', $symbol);
        $base_currency_model = Currency::where('name', $base_currency)
            ->where("is_display", 1)
            ->first();
        $quote_currency_model = Currency::where('name', $quote_currency)
            ->where("is_display", 1)
            ->where("is_legal", 1)
            ->first();
        if (!$base_currency_model || !$quote_currency_model) {
            return [
                'code' => -1,
                'msg' => 'error: symbol not exist',
                'data' => null
            ];
        }
        $result = MarketHour::getEsearchMarket($base_currency, $quote_currency, $period, $from, $to);
//        var_dump($result);
//        die;

        $result = array_map(function ($value) {
            return $this->normalizeKlineRow($value);
        }, $result);
//        $result[10]['low']=$result[10]['low']-1200;
//        $result[10]['close']=$result[10]['close']-1000;

        
        return [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
    }
    
    public function klineMarket111(Request $request)
    {
        $symbol = $request->input('symbol');
        $period = $request->input('period');
        $from = $request->input('from', null);
        $to = $request->input('to', null);
        

        $symbol = strtoupper($symbol);
       
        $array = [];
        $data = ['1min' => '1min', '5min' => '5min', '15min' => '15min', '30min' => '30min', '60min' => '60min', '1H' => '60min', '1D' => '1day', '1W' => '1week', '1M' => '1mon', '1Y' => '1year', '1day' => '1day', '1week' => '1week', '1mon' => '1mon', '1year' => '1year'];
        if ($from == null || $to == null) {
            return ['code' => -1, 'msg' => 'error: from time or to time must be filled in', 'data' => $array];
        }
        if ($from > $to) {
            return ['code' => -1, 'msg' => 'error: from time should not exceed the to time.', 'data' => $array];
        }
        $newdata = array_keys($data);
        if ($period == '' || !in_array($period, $newdata)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => $array];
        }
        if ($symbol == '' || stripos($symbol, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => $array];
        }
        $period = $data[$period];
        list($is_display, $is_legal) = explode('/', $symbol);
        $result = Currency::where('name', $is_display)->where('is_display', 1)->first();
        $results = Currency::where('name', $is_legal)->where('is_display', 1)->where('is_legal', 1)->first();
        if (!$result || !$results) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }
        $array = MarketHour::getEsearchMarket($is_display, $is_legal, $period, $from, $to);
        $array = array_map(function ($value) {
            return $this->normalizeKlineRow($value);
        }, $array);
        return ['code' => 1, 'msg' => 'success', 'data' => $array];
    }

    protected function normalizeKlineRow($value)
    {
        $value['time'] = ($value['id'] ?? 0) * 1000;
        if (!array_key_exists('volume', $value) || $value['volume'] === null) {
            if (array_key_exists('vol', $value) && $value['vol'] !== null) {
                $value['volume'] = $value['vol'];
            } else {
                $value['volume'] = $value['amount'] ?? 0;
            }
        }
        if (!array_key_exists('amount', $value) || $value['amount'] === null) {
            $price = $value['close'] ?? ($value['open'] ?? 0);
            $value['amount'] = ($value['volume'] ?? 0) * $price;
        }
        return $value;
    }
    
    
    public function huangnewQuotation()
    {
        $is_class = Input::get('is_class', 0); 
         $list = Currency::with(["quotation"=>function($query) use ($is_class){
        $query->where('is_display', 1)->where('market_from', $is_class)->where('id', '<>',4)->orderBy('sort','asc');
        }])->whereHas('quotation', function ($query) {
            $query->where('is_display', 1);
        })->where('is_display', 1)->where('is_legal', 1)->orderBy('sort','asc')->get();
        
        return $this->success($list);
    }
     public function newQuotation()
    {
        $BmnYXrJ = Currency::with('quotation')->whereHas('quotation', function ($query) {
            $query->where('is_display', 1);
        })->where('is_display', 1)->where('is_legal', 1)->orderBy('sort','asc')->get();
        return $this->success($BmnYXrJ);
    }
    
    
    public function optionalQuotation()
    {
        $BmnYXrJ = Currency::with(["quotation"=>function($query){
        $query->where('is_display', 1)->where('id', '<>',4)->orderBy('sort','asc');
        }])->whereHas('quotation', function ($query) {
            $query->where('is_display', 1);
        })->where('is_display', 1)->where('is_legal', 1)->orderBy('sort','asc')->get();
        return $this->success($BmnYXrJ);
    }
    public function dealInfo()
    {
        $TAXjNyv = Input::get('legal_id');
        $XhwvDSQ = Input::get('currency_id');
        if (empty($TAXjNyv) || empty($XhwvDSQ)) {
            return $this->error('参数错误');
        }
        $bltKpAQ = Currency::where('is_display', 1)->where('id', $TAXjNyv)->where('is_legal', 1)->first();
        $AmPMnfQ = Currency::where('is_display', 1)->where('id', $XhwvDSQ)->first();
        if (empty($bltKpAQ) || empty($AmPMnfQ)) {
            return $this->error('币未找到');
        }
        $gEUGyVJ = Input::get('type', '1');
        $PWRLYvQ = 60;
        switch ($gEUGyVJ) {
            case 2:
                $PWRLYvQ = 900;
                break 1;
            case 3:
                $PWRLYvQ = 3600;
                break 1;
            case 4:
                $PWRLYvQ = 14400;
                break 1;
            case 5:
                $PWRLYvQ = 86400;
                break 1;
            default:
                $PWRLYvQ = 60;
        }
        $GlYqXKv = time();
        $qTstiMQ = 0;
        $dViLeaQ = TransactionComplete::orderBy('create_time', 'desc')->where('currency', $XhwvDSQ)->where('legal', $TAXjNyv)->first();
        $dViLeaQ && ($qTstiMQ = $dViLeaQ->price);
        $aRpAMwv = TransactionComplete::getQuotation($TAXjNyv, $XhwvDSQ, $GlYqXKv - $PWRLYvQ, $GlYqXKv);
        $vaTCiiv = array();
        for ($LVnvAcQ = 0; $LVnvAcQ < 10; $LVnvAcQ++) {
            $KXhrmpJ = $GlYqXKv - $LVnvAcQ * $PWRLYvQ;
            $yrQDxmQ = $KXhrmpJ - $PWRLYvQ;
            $PtbcDYQ = array();
            $PtbcDYQ = $aRpAMwv = TransactionComplete::getQuotation($TAXjNyv, $XhwvDSQ, $yrQDxmQ, $KXhrmpJ);
            array_push($vaTCiiv, $PtbcDYQ);
        }
        return $this->success(array('legal' => $bltKpAQ, 'currency' => $AmPMnfQ, 'last_price' => $qTstiMQ, 'now_quotation' => $aRpAMwv, 'quotation' => $vaTCiiv));
    }
    public function userCurrencyList()
    {
        $user_id = Users::getUserId();
        $XOylMHJ = Currency::where('is_display', 1)->orderBy('sort', 'desc')->get();
        $XOylMHJ = $XOylMHJ->filter(function ($item, $key) {
            $fRtZmfQ = array_sum([$item->is_legal, $item->is_lever, $item->is_match, $item->is_micro]);
            return $fRtZmfQ > 1;
        })->values();
        $XOylMHJ->transform(function ($item, $key) use($user_id) {
            $VxDXWbJ = UsersWallet::where('user_id', $user_id)->where('currency', $item->id)->first();
            $item->setVisible(['id', 'name', 'is_legal', 'is_lever', 'is_match', 'is_micro', 'wallet']);
            return $item->setAttribute('wallet', $VxDXWbJ);
        });
        return $this->success($XOylMHJ);
    }
}
