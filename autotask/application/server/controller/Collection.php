<?php
namespace app\server\controller;
use app\common\model\ArticleModel;
use think\Controller;
use Workerman\Lib\Timer;
 
class Collection extends Controller{
 
    public function __construct(){
          parent::__construct();
    }
 
    public function add_timer(){
        Timer::add(3, array($this, 'index'), array(), true);//时间间隔过小，运行会崩溃   5秒一次吧
        Timer::add(3, array($this, 'tips'), array(), true);//时间间隔过小，运行会崩溃   5秒一次吧
    }
    //后台提示音
    public function tips(){
        $cz=db("charge_req")->where("status",1)->order("id","desc")->find();
        $tx=db("users_wallet_out")->where("status",1)->order("id","desc")->find();
        if($cz && $tx){
            $msgdata=[
              "type"=>"chongzhitixian",
                "data"=>[
    				"uid"=>"chongzhitixian"
    			]
            ];
            websocket_server_push($msgdata,"all","admin_admin");
        }else{
            if($cz){
                $msgdata=[
                  "type"=>"chongzhi",
                    "data"=>[
        				"uid"=>"chongzhi"
        			]
                ];
                websocket_server_push($msgdata,"all","admin_admin");
            }
            if($tx){
                $msgdata=[
                  "type"=>"tixian",
                    "data"=>[
        				"uid"=>"tixian"
        			]
                ];
                websocket_server_push($msgdata,"all","admin_admin");
            }
        }
        
    }
    /**
     * 采集数据
     */
 
    public function index(){
        //加载配置文件 tasks_matches
        $tasks_matches=getConfig("../config/tasks_matches.php");
        $sys_matches=$tasks_matches["tasks_matches"];//获取到“交易所”的交易对
     
        $time=time();
        $time_now=strtotime(date("Y-m-d H:i"));//取整分钟
        $time_next=$time_now+60;
        foreach($sys_matches as $k=>$v){
            $current=db("myquotation")->where("base",$v["name"])->where("itime",$time_now)->find();//取出当前的数据
            if(!$current){
                continue;
            }
            $price=numZhiJian($current["high"],$current["low"]);//取出当前价格（从当前价的最高、最低价中取随机数）
            if($time_now==$time){
                $price=$current["close"];  //如果是整分钟，那么这个当前价就是本分钟的收盘价
            }
            //$next=db("myquotation")->where("itime",$time_next)->find();//取出下一条数据  好像不需要下一条
            $shu24=0;//$current["vol"] ->whereBetweenTime('create_time', '2017-01-01', '2017-06-30')
            $e=time();
            $s=$e-86400;
            $shu24=db("myquotation")->where("base",$v["name"])->whereTime('itime', 'between', [$s, $e])->sum("vol");
            $period="1min";//1分钟
            $php_time=$time_next;//要显示的时间戳
            $change=diychange($current["open"],$price);
            $str="<?php".PHP_EOL;
            $str.='return ['.PHP_EOL;
            $str.='"libra_data"=>['.PHP_EOL;
                  $str.='"api_form"=>"diy",
                         "change"=>"'.$change.'",
                         "close"=>'.$price.',
                         "currency_id"=>'.$v["currency_id"].',
                         "currency_name"=>"'.$current["base"].'",
                         "high"=>'.$current["high"].',
                         "legal_id"=>'.$v["legal_id"].',
                         "legal_name"=>"'.$v["legal_name"].'",
                         "low"=>'.$current["low"].',
                         "match_id"=>'.$v["id"].',
                         "now_price"=>'.$price.',
                         "open"=>'.$current["open"].',
                         "period"=>"'.$period.'",
                         "symbol"=>"'.$current["symbol"].'",
                         "time"=>'.$time_next.'000,
                         "type"=>"kline",
                         "volume"=>'.$current["vol"].'
                  '.PHP_EOL;
            $str.=']'.PHP_EOL.'];'.PHP_EOL;
    		writeFile("../config/kline_1min_".strtolower($v["name"]).".php",$str);
    		
    		//daymarket
    		
    		$str2="<?php".PHP_EOL;
            $str2.='return ['.PHP_EOL;
            $str2.='"libra_data"=>['.PHP_EOL;
                  $str2.='"api_form"=>"diy",
                         "change"=>"'.$change.'",
                         "close"=>'.$price.',
                         "currency_id"=>'.$v["currency_id"].',
                         "currency_name"=>"'.$current["base"].'",
                         "high"=>'.$current["high"].',
                         "legal_id"=>'.$v["legal_id"].',
                         "legal_name"=>"'.$v["legal_name"].'",
                         "low"=>'.$current["low"].',
                         "match_id"=>'.$v["id"].',
                         "now_price"=>'.$price.',
                         "open"=>'.$current["open"].',
                         "period"=>"'.$period.'",
                         "symbol"=>"'.$current["symbol"].'",
                         "time"=>'.$time_next.'000,
                         "type"=>"daymarket",
                         "volume"=>'.$shu24.'
                  '.PHP_EOL;
            $str2.=']'.PHP_EOL.'];'.PHP_EOL;
    		
    		
    		writeFile("../config/daymarket_".strtolower($v["name"]).".php",$str2);
    		//成交量数据  market_detail
    		$sj=mt_rand(1,100);
    		$sjsl=mt_rand(1,10000);
    		$direction="";
    		if($sj<50){
    		    //买
    		    $direction="buy";
    		}else{
    		    //卖
    		    $direction="sell";
    		}
    		
    		$str3="<?php".PHP_EOL;
            $str3.='return ['.PHP_EOL;
            $str3.='"libra_data"=>['.PHP_EOL;
                  $str3.='"base-currency"=>"'.$current["base"].'",
                         "currency_id"=>'.$v["currency_id"].',
                         "currency_name"=>"'.$current["base"].'",
                         "legal_id"=>'.$v["legal_id"].',
                         "legal_name"=>"'.$v["legal_name"].'",
                         "quote-currency"=>"'.$v["legal_name"].'",
                         "symbol"=>"'.$current["symbol"].'",
                         "type"=>"market_detail",
                         "data"=>[
                                "amount"=>'.$sjsl.',
                                "direction"=>"'.$direction.'",
                                "id"=>'.$time.'000,
                                "price"=>'.$price.',
                                "time"=>"'.date("H:i:s",$time).'",
                                "ts"=>'.$time.'000,
                                "tradeId"=>'.$time.'
                         ]
                  '.PHP_EOL;
            $str3.=']'.PHP_EOL.'];'.PHP_EOL;
    		writeFile("../config/market_detail_".strtolower($v["name"]).".php",$str3);
        		
        	//修改基本价格
        	$up=[
        	    "price"=>$price
        	 ];
        	db("currency")->where("id",$v["currency_id"])->update($up);
        	
        	//修改成交量等
        	$up2=[
        	    "now_price"=>$price,
        	    "change"=>$change
        	 ];
        	db("currency_quotation")->where("match_id",$v["id"])->where("currency_id",$v["currency_id"])->update($up2);
        	//20 秒一次增加购买数量
        	if($time%20==0){
        	    db("currency_quotation")->where("match_id",$v["id"])->where("currency_id",$v["currency_id"])->inc("volume",mt_rand(10,200))->update($up2);
        	}
        }
    }
 	public function get_curl($url){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($ch);
 
        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        curl_close($ch);
        // 4. 释放curl句柄
 
        return $output;
 
    }
   
}

function t50Obj2array($obj,$isstr=false){
	if($isstr){
		//字符串输出 
		return json_encode($obj,JSON_UNESCAPED_UNICODE);
	}else{
		//输出array
		return json_decode(json_encode($obj,JSON_UNESCAPED_UNICODE),true);
	}
	
}

//调用配置文件
function getConfig($file,$isstr=false){
	$cfg=include($file);
	return t50Obj2array($cfg,$isstr);
}



//发送消息
function websocket_server_push($data=array(),$to_type="admin",$to="admin_admin"){
	$param=[
		"type"=>"publish",
		"to"=>$to,
		"to_type"=>$to_type,//用户类型：all所有用户类型。admin后台管理员 alluser所有前端用户   agent 代理等。根据不同类型去查不同的表。用户登录时用type+uuid如"agent_xxxxxx"；可以防止UUID在不同表中重复。
		"content"=>$data
	];
	$headers =[
		0=>"Content-Type:application/json; charset=utf-8"
	];
	return wsstPost("http:/127.0.0.1:3188",$param,$headers);
}





function wsstPost($url = '', $param =array(),$headers=array()) {
		$param=json_encode($param,JSON_UNESCAPED_UNICODE);
		//echo $param;
		if(!$param){
			return false;
		}
        if (empty($url)) {
            return false;
        }
		/*$headers = array();
		//array_push($headers, "Authorization:APPCODE " . $appcode);
		//根据API的要求，定义相对应的Content-Type
		if($param_type=="form"){
			array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
		}
		if($param_type=="json"){
			array_push($headers, "Content-Type".":"."application/json; charset=utf-8");
		}*/
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
         //$data = json_decode($data);
        return $data;
}



?>