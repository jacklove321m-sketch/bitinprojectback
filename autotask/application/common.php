<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Controller;
use think\Session;
include "loadconfig/config_set.php";
include "loadconfig/wx_set.php";
include "loadconfig/mail_set.php";
include "loadconfig/config_pageset.php";
include "loadconfig/permissions.php";
include "function/html.php";
include 'phpqrcode.php'; 

function buildHtml($htmldata) {
	$arr=$htmldata["links"];
	$lunlian="";
	foreach($arr as $k=>$v){
		$lunlian.='<li><a href="/'.$v["folder"].'/'.$v["file"].'">'.$v["title"].'</a></li>';
	}
	$oldlunlian="";
	$oldarr=$htmldata["oldlinks"];
	foreach($oldarr as $k=>$v){
		$oldlunlian.='<li><a href="/'.$v["folder"].'/'.$v["file"].'">'.$v["title"].'</a></li>';
	}
	$pagejs=$htmldata["pagejs"];
	$template=$htmldata["template"];
	$open = fopen($template,"r"); //打开模板文件
	$content = fread($open,filesize($template)); //读取模板文件内容
	//print_r($content);
	$content = str_replace("{页面标题}",$htmldata["title"],$content);//替换
	$content = str_replace("{网站名称}",$htmldata["sitename"],$content);
	$content = str_replace("{生成时间}",$htmldata["time"],$content);
	$content = str_replace("{页面描述}",$htmldata["dec"],$content);
	$content = str_replace("{页面关键字}",$htmldata["key"],$content);
	$content = str_replace("{页面内容}",$htmldata["con"],$content);
	
	$content = str_replace('{轮链}',$lunlian,$content);
	$content = str_replace('{旧轮链}',$oldlunlian,$content);
	/*
	//轮链是否显示  
	if($lunlian){
		$content = str_replace('{轮链}',$lunlian,$content);
	}else{
		$content = str_replace('<div class="fr"><div class="rtitle">最新推荐</div><ul class="ul">{轮链}</ul></div>',"",$content);
	}
	*/
	$content = str_replace("{落地页JS}",$pagejs,$content);
	$dir = USERHTMLFOLDER.$htmldata["uid"]."/".$htmldata["folder"];
	if(!is_dir($dir))
	{
		mkdir($dir,0777,true);//文件夹不存在就创建一个
	}
	$newtemp = fopen($dir."/".$htmldata["file"].".html","w");//生成,用写入方式打开一个不存在（新）的页面
	fwrite($newtemp,$content);//将刚刚替换的内容写入新文件中
	fclose($newtemp);
}

//微信小程序获取unionId
function wxxcxGetUnionId($sessionKey,$encryptedData,$iv){
	
	include_once "function/wxxcxapi/wxBizDataCrypt.php";
	$appid = WXAPPID;
	$pc = new WXBizDataCrypt($appid, $sessionKey);
	$errCode = $pc->decryptData($encryptedData, $iv, $data );

	if ($errCode == 0 || !$errCode) {
		return ($data);
	} else {
		return($errCode);
	}
}
function checkUserLogin(){//检测前端用户登录
	$uemail=Session::get('uemail');
	$useruid=Session::get('useruid');
	$sessionTime=Session::get('sessionTime');
	$site_name=SITE_NAME;
	$res=array(
		"code"=>200,
		"msg"=>""
	);
	if(!$uemail || !$useruid || $sessionTime<time()){
		//echo '<script>window.location.href="/login";</script>';
		//header("Location:/login"); 
		$res=array(
			"code"=>401,
			"msg"=>"未登录或登录超时"
		);
	}else{
		Session::set('sessionTime',time()+60*30);
	}
	return $res;
}
//生成UUID
function create_uuid($prefix = ""){    //可以指定前缀
    $str = md5(uniqid(mt_rand(), true));   
    $uuid  = substr($str,0,8) . '-';   
    $uuid .= substr($str,8,4) . '-';   
    $uuid .= substr($str,12,4) . '-';   
    $uuid .= substr($str,16,4) . '-';   
    $uuid .= substr($str,20,12);   
    return $prefix . $uuid;
}
function checkAdminLogin($permiss=null){//检测后台管理员登录
	$adminName=Session::get('adminName');
	$adminId=Session::get('adminId');
	$adminSessionTime=Session::get('adminSessionTime');
	$site_name=SITE_NAME;
	
	if(!$adminName || !$adminId || $adminSessionTime<time()){ 
		Session::delete('adminName');
		Session::delete('userName');
		Session::delete('adminId');
		return 401;//401，要重新登录
		exit;
	}else{
		Session::set('adminSessionTime',time()+60*30);
		if($permiss){
			$admin=db("admins")
					->where("id",$adminId)
					->find();
			$role_id=$admin["role"];
			if($role_id){
				$adminPerm=db("roles")
							->where("id",$role_id)
							->find();
				if($adminPerm["permissions"]=="all"){
					return 200;
				}
				$role_data=explode(",",$adminPerm["permissions"]);
				if(in_array($permiss,$role_data)){
					return 200;
				}else{
					return 403;
				}
			}else{
				return 403;
			}
		}else{
			return 200;
		}
		exit;
	}
}

//CURL
function request_get($url = '') {
        if (empty($url)) {
            return false;
        }
        $postUrl = $url;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);//要求结果为字符串且输出到屏幕上
        //curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
       // curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        //$data = json_decode($data,true);
        return $data;
}
function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,true); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
         //$data = json_decode($data);
        return $data;
}
//判断是涨是跌

function diychange($open,$price){
    $num=(($price-$open)*100/$open);
    $num=sprintf("%.2f",$num);
    if($num>=0){
        $num="+".$num;
    }
    if($num=="+-0.00"){
        $num="+0.00";
    }
    
    return $num;
    $zd="";
    if($open<=$price){
        $zd="+";
        $num=(($price-$open)*100/$open);
        return $zd.sprintf("%.2f",$num);
    }else{
        $zd="-";
        $num=(($open-$price)*100/$open);
        return $zd.sprintf("%.2f",$num);
    }
}

//取指定范围内随机数。
function numZhiJian($high,$low){
    $zz=1000000000;
    $low2=$low*$zz;
    $high2=$high*$zz;
    $num=mt_rand($low2,$high2);
    return sprintf("%.8f",$num/$zz);
    return mt_rand($num);
    
}

#php写文件
function writeFile($file,$str,$mode='w') { 
			//if(is_file($file)) unlink($file);
			file_put_contents($file,$str);
} 

#获取用户Ip
   function getIP(){
					global $ip;
					if (getenv("HTTP_CLIENT_IP"))
					$ip = getenv("HTTP_CLIENT_IP");
					else if(getenv("HTTP_X_FORWARDED_FOR"))
					$ip = getenv("HTTP_X_FORWARDED_FOR");
					else if(getenv("REMOTE_ADDR"))
					$ip = getenv("REMOTE_ADDR");
					else $ip = "Unknow";
					return $ip;
}


function get_client_ip($type = 0) {
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL) return $ip[$type]; 
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']); $pos = array_search('unknown',$arr); if(false !== $pos) unset($arr[$pos]); $ip = trim($arr[0]); 
	}elseif (
	isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP']; 
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR']; 
	} 
	//IP地址合法验证
	$long = ip2long($ip);
	$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}

//生成随机的姓
function randXing(){
	$list='赵钱孙李周吴郑王冯陈楮卫蒋沈韩杨朱秦尤许何吕施张孔曹严华金魏陶姜戚谢邹喻柏水窦章云苏潘葛奚范彭郎鲁韦昌马苗凤花方俞任袁柳酆鲍史唐费廉岑薛雷贺倪汤滕殷罗毕郝邬安常乐于时傅皮卞齐康伍余元卜顾孟平黄和穆萧尹姚邵湛汪祁毛禹狄米贝明臧计伏成戴谈宋茅庞熊纪舒屈项祝董梁杜阮蓝闽席季麻强贾路娄危江童颜郭梅盛林刁锺徐丘骆高夏蔡田樊胡凌霍虞万支柯昝管卢莫经房裘缪干解应宗丁宣贲邓郁单杭洪包诸左石崔吉钮龚程嵇邢滑裴陆荣翁荀羊於惠甄麹家封芮羿储靳汲邴糜松井段富巫乌焦巴弓牧隗山谷车侯宓蓬全郗班仰秋仲伊宫宁仇栾暴甘斜厉戎祖武符刘景詹束龙叶幸司韶郜黎蓟薄印宿白怀蒲邰从鄂索咸籍赖卓蔺屠蒙池乔阴郁胥能苍双闻莘党翟谭贡劳逄姬申扶堵冉宰郦雍郤璩桑桂濮牛寿通边扈燕冀郏浦尚农温别庄晏柴瞿阎充慕连茹习宦艾鱼容向古易慎戈廖庾终暨居衡步都耿满弘匡国文寇广禄阙东欧殳沃利蔚越夔隆师巩厍聂晁勾敖融冷訾辛阚那简饶空曾毋沙乜养鞠须丰巢关蒯相查后荆红游竺权逑盖益桓公万俟司马上官欧阳夏侯诸葛闻人东方赫连皇甫尉迟公羊澹台公冶宗政濮阳淳于单于太叔申屠公孙仲孙轩辕令狐锺离宇文长孙慕容鲜于闾丘司徒司空丌官司寇仉督子车颛孙端木巫马公西漆雕乐正壤驷公良拓拔夹谷宰父谷梁晋楚阎法汝鄢涂钦段干百里东郭南门呼延归海羊舌微生岳帅缑亢况后有琴梁丘左丘东门西门商牟佘佴伯赏南宫墨哈谯笪年爱阳佟';
	preg_match_all("/./u", $list, $arr);
	$total=count($arr[0])-1;
	$index=rand(1,$total);
	return $arr[0][$index];
}



function qrcode($url,$size=4){
    Vendor('Phpqrcode.phpqrcode');
    // 如果没有http 则添加
    if (strpos($url, 'http')===false) {
        $url='http://'.$url;
    }
    QRcode::png($url,false,QR_ECLEVEL_L,$size,2,false,0xFFFFFF,0x000000);
}
function createQrcode($data,$saveDir="Qrcode",$logo = "")
    {
        $rootPath = "";
        $path = $saveDir.'/'.date("Y-m-d").'/';
        $fileName = uniqid();
        if (!is_dir($rootPath.$path))
        {
            mkdir($rootPath.$path,0777,true);
        }
        $originalUrl = $path.$fileName.'.png';
        
        Vendor('phpqrcode.phpqrcode');
        $object = new \QRcode();
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 20;            //生成图片大小（这个值可以通过参数传进来判断）
        $object->png($data,$rootPath.$originalUrl,$errorCorrectionLevel, $matrixPointSize, 2);
        
        //判断是否生成带logo的二维码
        if(file_exists($logo))
        {
            $QR = imagecreatefromstring(file_get_contents($rootPath.$originalUrl));        //目标图象连接资源。
            $logo = imagecreatefromstring(file_get_contents($logo));    //源图象连接资源。
            
            $QR_width = imagesx($QR);            //二维码图片宽度
            $QR_height = imagesy($QR);            //二维码图片高度
            $logo_width = imagesx($logo);        //logo图片宽度
            $logo_height = imagesy($logo);        //logo图片高度
            $logo_qr_width = $QR_width / 4;       //组合之后logo的宽度(占二维码的1/5)
            $scale = $logo_width/$logo_qr_width;       //logo的宽度缩放比(本身宽度/组合后的宽度)
            $logo_qr_height = $logo_height/$scale;  //组合之后logo的高度
            $from_width = ($QR_width - $logo_qr_width) / 2;   //组合之后logo左上角所在坐标点
            
            //重新组合图片并调整大小
            //imagecopyresampled() 将一幅图像(源图象)中的一块正方形区域拷贝到另一个图像中
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height);
            
            //输出图片
            imagepng($QR, $rootPath.$originalUrl);
            imagedestroy($QR);
            imagedestroy($logo);
        }
        
        $result['errcode'] = 0;
        $result['errmsg'] = 'ok';
        $result['data'] = $originalUrl;
        return $result;
    
    }
    
    /**
     * 生成临时二维码图片
     * 这里返回的是base64进制图片
     * 一般用于微信扫码支付二维码生成场景
     *
     * @param string $data 二维码内容
     *         示例数据：http://www.tf4.cn或weixin://wxpay/bizpayurl?pr=0tELnh9
     *
     * @return
     */
function createTempQrcode($data)
    {
        Vendor('phpqrcode.phpqrcode');
        $object = new \QRcode();
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 5;            //生成图片大小
        
        //打开缓冲区
        ob_start();
        //生成二维码图片
        $returnData = $object->png($data,false,$errorCorrectionLevel, $matrixPointSize, 2);
        //这里就是把生成的图片流从缓冲区保存到内存对象上，使用base64_encode变成编码字符串，通过json返回给页面。
        $imageString = base64_encode(ob_get_contents());
        //关闭缓冲区
        ob_end_clean();
        $base64 = "data:image/png;base64,".$imageString;
        
        $result['errcode'] = 0;
        $result['errmsg'] = 'ok';
        $result['data'] = $base64;
        return $result;
    }
##############  页面分页函数  #####################
#记录集分页
function pagination($count,$perlogs,$page,$url,$suffix=''){
	$pnums = @ceil($count / $perlogs);
	if($page>5 ){
		$dot='...';
	}else{
		$dot ='';
	}
	if(($page + 4) < $pnums){
		$dotx='...';
	}else{
		$dotx='';
	}
	
	$re = '';
		if($page >1){
					$re .='<span><a href="'.$url.($page-1).$suffix.'">上一页</a></span>'.$dot;
					}
	for ($i = $page-4;$i <= $page+4 && $i <= $pnums; $i++){
			if ($i > 0){
				
			if ($i == $page){
				$re .= ' <span class="current">'.$i.'</span> ';
			} else {
				$re .= '<a href="'.$url.$i.$suffix.'">'.$i.'</a>';
			}
		}
	}
		if($page <$pnums){
					$re .=$dotx.' <span id="pagenav" ><a  href="'.$url.($page+1).$suffix.'">下一页</a></span>';
					}
	if ($page > 1) $re = '<a href="'.$url.'1'.$suffix.'">首页</a>'.$re;
	if ($page < $pnums) $re .= ' <a href="'.$url.$pnums.$suffix.'" >尾页</a>';
	if ($pnums <= 1) $re = '';
	return $re;
	
	
}
##################  结束  ####################
//生成二维码

//本项目专用
    /**
     * @description:根据数据 
     * @param {dataArr:需要分组的数据；keyStr:分组依据} 
     * @return: 
     */
    function dataGroup(array $dataArr,string $keyStr)
    {
        $newArr=[];
        foreach ($dataArr as $k => $val) {    //数据根据日期分组
			
           $newArr[$val[$keyStr]][] = $val;
        }
		return $newArr;
    }
	//特定:某人按日期分组
function data2Group(array $dataArr,string $keyStr)
    {
		//in_array
        $newArr=[];
		$keyArr=[];
		$items=[];
		//先生成按日期分组的数组 
        foreach ($dataArr as $k => $val) {    //数据根据日期分组
			if(!in_array($dataArr[$k][$keyStr],$keyArr)){
				$keyArr[]= $dataArr[$k][$keyStr];
			}
			
			if(in_array($dataArr[$k][$keyStr],$keyArr)){
				
			}else{
				
			}
           //$newArr[$val[$keyStr]][] = $val;
        }
		foreach($keyArr as $k=>$v){
			$items[]=array(
				$keyStr=>$v,
				"data"=>[]
			);
		}
		foreach($items as $k=>$v){
			foreach($dataArr as $kk => $vv){
				if($vv[$keyStr]==$v[$keyStr]){
					array_push($items[$k]["data"],$vv);
				}
			}
		}
		return $items;
    }

function check_str($str, $substr)
{
	//echo $str;
	 $nums=substr_count($str,$substr);
	 //echo $nums;
	 if ($nums>=1)
	 {
	  return false;
	 }
	 else
	 {
	  return true;
	 }
}
function ToUrlParams($urlObj)
{
	$buff = "";
	foreach ($urlObj as $k => $v)
	{
		$buff .= $k . "=" . $v . "&";
	}
	
	$buff = trim($buff, "&");
	return $buff;
}

//发邮件公共方法
//给注册邮箱发信开始
//sendMAILto("","标题1","内容1","other");
function sendMAILto($toaddress,$mailtitle,$htmlBody,$type='163'){
		if($type=="qqmail"){
			require_once('phpmailer/QQMailer.php');
			$mailer = new QQMailer(true);
			$mailer->send($toaddress, $mailtitle, $htmlBody);
			$msg= $mailer->mailer->ErrorInfo;
			if($msg) {
					$message= "发生错误" ;
				} else {
					$message= "ok";/**/
				}
				return $message;
		}else{
			require_once('phpmailer/class.phpmailer.php');
			 include("phpmailer/class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
				$mailserver_host=HOST_OT;
				$mailserver_port=PORT_OT;
				$mailserver_username=USERNAME_OT;
				$mailserver_password=PASSWORD_OT;
				$mailsuffix=MAILSUFFIX; //邮件后缀
				$mailsender=NICKNAME; //发件人
				$mail             = new PHPMailer();
				//$body             = file_get_contents('contents.html');
				$body                     = $htmlBody.$mailsuffix;
				//$body             = preg_replace('/[\]/','',$body);
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->Host           = $mailserver_host; // SMTP server
				$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
														   // 1 = errors and messages
														   // 2 = messages only
				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->Host          = $mailserver_host; // sets the SMTP server
				$mail->Port           = $mailserver_port;                    // set the SMTP port for the GMAIL server
				$mail->Username   = $mailserver_username; // SMTP account username
				$mail->Password    =  $mailserver_password;        // SMTP account password
				$mail->SetFrom($mailserver_username, $mailsender);
				$mail->AddReplyTo($mailserver_username, $mailsender);
				$mail->Subject    = $mailtitle;
				$mail->AltBody    = "请使用HTML兼容的电子邮件查看器!!"; // optional, comment out and test
				$mail->MsgHTML($body);
				$address =$toaddress;
				$mail->AddAddress($address, "");
				if(!$mail->Send()) {
					$message= "发生错误了: " . $mail->ErrorInfo;
				} else {
					$message= "ok";/**/
				}
				return $message;
		}
	
		
}//发邮件公共方法结束	


//等级转文字
function level2txt($level){
	$str="";
	switch($level){
		case 1:
			$str="普通会员";
			break;
		case 2:
			$str="初级会员";
			break;
		case 3:
			$str="中级会员";
			break;
		case 4:
			$str="高级会员";
			break;
		case 5:
			$str="超级VIP会员";
			break;
		default:
			$str="未知等级";
			break;
	}
	return $str;
}


//角色字符串转数组  1,2,3 to ["1","2","3"]
function rolestr2arr($str){
	$arr=array();
	if($str){
		$arr=explode(',',$str);
	}
	return $arr;
}

//判断是否有权限  $qx是文本，不是数字
function checkQX($qx){
	if(!$qx){
		return false;
	}
	$adminId=Session::get('adminId');
	$role=db("admins")
		->where("id",$adminId)
		->find();
	if($role){
		$one=db("roles")
			->where("id",$role["role"])
			->find();
	}else{
		return false;
	}
	if($one){
		$qx=$one["permissions"];
		if($qx=="all"){
			return true;
		}else{
			$arr=explode(",",$qx);
			return(in_array($arr,$qx));
		}
	}else{
		return false;
	}
}

//把权限数据从数据库读出来编写成常量   权限增、删、改后调用一下
function getPStoSET(){
	//echo unicodeDecode("\u7cfb\u7edf\u8bbe\u7f6e");
	//获取所有权限（一级，二级）
	$list=db("permissions")
		->where("pid",0)
		->order("id","asc")
		->select();
	foreach($list as $k=>$v){
		$list[$k]["item"]=db("permissions")
				->where("pid",$v["id"])
				->order("id","asc")
				->select();
		/*
		foreach($list[$k]["item"] as $k2=>$v2){
			$list[$k]["item"][$k2]["name"]=unicodeDecode($v2["name"]);
		}
		*/
	}
	//print_r($list);
	//权限2 仅2级组成数组
	$list2=db("permissions")
		->where("pid>0")
		->order("id","asc")
		->select();
	$arr2str="[";
	foreach($list2 as $k=>$v){
		$arr2str.='{"id":"'.$v["id"].'","name":"'.$v["name"].'"}';
	}
	$arr2str.="]";
	
	
	$str="<?php \n";
	$str.='define("PS_ARR",\''.json_encode($list).'\');  #权限 ';
	$str.="\n";
	
	
	$str.='define("PS_ARR2",\''.$arr2str.'\');  #权限2，不仅其组 ';
	$str.="\n";
	$str.='?>';
	writeFile("../application/loadconfig/permissions.php",$str);
}

//根据管理员ID获取角色名称
function adminRolename($roleid){
	$one=db("roles")
		->where("id",$roleid)
		->find();
	if($one){
		return $one["name"];
	}else{
		return "";
	}
}

//Unicode解码成中文
function unicodeDecode($unicode_str){
    $json = '{"str":"'.$unicode_str.'"}';
    $arr = json_decode($json,true);
    if(empty($arr)) return '';
    return $arr['str'];
}

//将XML格式转为JSON格式
function xmltoarray($xml) {
     //禁止引用外部xml实体LIBXML_NOCDATA
	libxml_disable_entity_loader(true);
	$xmlstring = simplexml_load_string($xml,'SimpleXMLElement',16384);
	$val = json_decode(json_encode($xmlstring),true);
	return $val;
}



//根据图片自动生成缩略图
function imagecropper($source_path, $target_width, $target_height)
{
	$imgname=ROOT_PATH . 'public' . DS .$source_path;
	$source_info = getimagesize($imgname);
	
	$source_width = $source_info[0];
	$source_height = $source_info[1];
	$source_mime = $source_info['mime'];
	$source_ratio = $source_height / $source_width;
	$target_ratio = $target_height / $target_width;

	// 源图过高
	if ($source_ratio > $target_ratio)
	{
		$cropped_width = $source_width;
		$cropped_height = $source_width * $target_ratio;
		$source_x = 0;
		$source_y = ($source_height - $cropped_height) / 2;
	}
	// 源图过宽
	elseif ($source_ratio < $target_ratio)
	{
		$cropped_width = $source_height / $target_ratio;
		$cropped_height = $source_height;
		$source_x = ($source_width - $cropped_width) / 2;
		$source_y = 0;
	}
	// 源图适中
	else
	{
		$cropped_width = $source_width;
		$cropped_height = $source_height;
		$source_x = 0;
		$source_y = 0;
	}

	switch ($source_mime)
	{
		case 'image/gif':
			$source_image = imagecreatefromgif($imgname);
			break;

		case 'image/jpeg':
			$source_image = imagecreatefromjpeg($imgname);
			break;

		case 'image/png':
			$source_image = imagecreatefrompng($imgname);
			break;

		default:
			return false;
			break;
	}

	$target_image = imagecreatetruecolor($target_width, $target_height);
	$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

	// 裁剪
	imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
	// 缩放
	imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);

	//保存图片到本地(两者选一)
	$randNumber = mt_rand(00000, 99999). mt_rand(000, 999);
	$fileName = substr(md5($randNumber), 8, 16) .".png";
	imagepng($target_image,$imgname);
	imagedestroy($target_image);
	/*
	//直接在浏览器输出图片(两者选一)
	header('Content-Type: image/jpeg');
	imagepng($target_image);
	imagedestroy($target_image);
	imagejpeg($target_image);
	imagedestroy($source_image);
	imagedestroy($target_image);
	imagedestroy($cropped_image);
	*/
}
/**
 * @param $length
 * @param bool|false $numeric
 * @return string
 * 生成指定长度的随机字符串并返回。
 */
function random($length, $numeric = false) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

//PHP将BASE64生成图片并获取图片名称
function base2img($imgdata){
	$arr=explode(";",$imgdata);
	$type=str_replace("data:image/","",$arr[0]);//获取类型
	$ext=".png";
	switch($type){
		case "jpeg":
			$ext=".jpg";
			break;
		case "gif":
			$ext=".gif";
			break;
		case "bmp":
			$ext=".bmp";
			break;
		case "x-icon":
			$ext=".icon";
			break;
		default:
			$ext=".png";
			break;
	};
	//print_r(explode("data:image/".$type.";base64,",$imgdata));
	$arrNum=count(explode("data:image/".$type.";base64,",$imgdata));
	//echo $arrNum;
	if($arrNum<2){
		$imgres=array(
			"code"=>400,
			"msg"=>"base64格式图片数据错误，请检查",
			"imgurl"=>""
		);
	}else{
		$img = str_replace("data:image/".$type.";base64,", "", $imgdata);
		$data = base64_decode($img);
		$imgname=time().$ext;
		$savepath="upload/base64/";
		file_put_contents($imgname,$data);
		copy($imgname,mb_convert_encoding($savepath.$imgname,"gb2312","UTF-8"));
		unlink($imgname);
		chmod($savepath, 0777);
		
		
		
		$imgres=array(
			"code"=>200,
			"msg"=>"ok",
			"imgurl"=>$savepath.$imgname
		);
	}
	
	return $imgres;
	
	/*
	data:text/plain,文本数据
	data:text/html,HTML代码
	data:text/html;base64,base64编码的HTML代码
	data:text/css,CSS代码
	data:text/css;base64,base64编码的CSS代码
	data:text/javascript,Javascript代码
	data:text/javascript;base64,base64编码的Javascript代码
	data:image/gif;base64,base64编码的gif图片数据
	data:image/png;base64,base64编码的png图片数据
	data:image/jpeg;base64,base64编码的jpeg图片数据
	data:image/x-icon;base64,base64编码的icon图片数据
	*/
}

//IMG转为BASE64数据图片
function img2base64($img){
	
}




//TOKEN相关

//获取请求headers
if (!function_exists('getallheaders')){
    function getallheaders() 
    { 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
}





function checkToken($token){//校验TOKEN //从前端获取用户ID，当前时间
	
}
function makeToken($payload){//生成TOKEN
	  $tokenKey=TOKENKEY;
	  $header=array(
			'alg'=>'HS256', //生成signature的算法
			'typ'=>'JWT'  //类型
		 );
    if(is_array($payload))
    {
       $base64header=urlsafe_b64encode(json_encode($header,JSON_UNESCAPED_UNICODE));
       $base64payload=urlsafe_b64encode(json_encode($payload,JSON_UNESCAPED_UNICODE));
       $token=$base64header.'.'.$base64payload.'.'.tokenSignature($base64header.'.'.$base64payload,$tokenKey,$header['alg']);
	   header('refresh_token:'.$token);
	   return $token;
    }else{
      return false;
    }
}


function tokenSignature($input, $tokenKey, $alg = 'HS256')
  {
    $alg_config=array(
      'HS256'=>'sha256'
    );
    return urlsafe_b64encode(hash_hmac($alg_config[$alg], $input,$tokenKey,true));
}
function getJWTdata($jwt, $key){//根据JWT数据获取实体数据
  $tokens = explode('.', $jwt);
  //$key = md5($key);
 
  if (count($tokens) != 3){
	  return false;
  }
   
 
  list($header64, $payload64, $sign) = $tokens;
 
  $header = json_decode(urlsafe_b64decode($header64), JSON_OBJECT_AS_ARRAY);
  if (empty($header['alg']))
   return false;

  if (tokenSignature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
   return false;
 
  $payload = json_decode(urlsafe_b64decode($payload64), JSON_OBJECT_AS_ARRAY);
 /*
  $time = $_SERVER['REQUEST_TIME'];
  if (isset($payload['iat']) && $payload['iat'] > $time)
   return false;
 
  if (isset($payload['exp']) && $payload['exp'] < $time)
   return false;
 */
  return $payload;
 }
/*
echo getToken(array(
		"username"=>$username,
		"password"=>$password
	));
	echo json_encode(getJWTdata("eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IjMiLCJwYXNzd29yZCI6IjM0In0.NIFOtilgNdPFdXNUwyfBMpdRPnsFLjJGoNs91vl0ufc","zz123456"),JSON_UNESCAPED_UNICODE);
*/

function needToken(){
	$HEADERS=getallheaders();
	$resdata=array();
	
	if(!isset($HEADERS['Authorization'])){
			$resdata["code"]=400;
			$resdata["msg"]="HEADER里缺少Authorization";
		}else{
			$TOKEN=$HEADERS['Authorization'];
			$oldTK=getJWTdata(str_replace("Bearer ","",$TOKEN),TOKENKEY);
			//print_r($oldTK);
			//echo '2323';
			$t=time();
			//$oldTK["exp"]=$t;
			//echo $oldTK["exp"];
			//echo bitokentime($oldTK["exp"]);
			if(bitokentime($oldTK["exp"])){//判断过期时间
				$resdata["code"]=200;
				$resdata["msg"]="OK";
				$oldTK["exp"]=$t+(60*60);
				$resdata["useruuid"]=$oldTK["uuid"];
				$resdata["username"]=$oldTK["username"];
				header('refresh_token:'.makeToken($oldTK));
			}else{
				$resdata["code"]=401;
				$resdata["msg"]="登录超时，请重新登录";
				//$oldTK["exp"]=$t+(60*60);
				//header('refresh_token:'.makeToken($oldTK));
			}
		}
		
		//print_r(bitokentime($oldTK["exp"]));
	//echo $oldTK["exp"]."<br>";
	//echo $t."<br>";
	return $resdata;
}
function bitokentime($tokentime){
	
	if(intval($tokentime)>time()){
		return 1;
	}else{
		return 0;
	}
}
//TOKEN相关 END



/**
 * @param $key
 * @param string $if_not_exist
 * @return mixed
 * 获取当前参数的值
 */
function params($key,$if_not_exist = ""){
    return request()->param($key,$if_not_exist);
}

/**
 * @param $key
 * @param string $if_not_exist
 * @return mixed
 * 获取当前参数的值 数组
 */
function params_array($key,$if_not_exist = ""){
    return request()->param("{$key}/a",$if_not_exist);
}

/**
 * @param $array
 * @return array|string
 * 数组去空格
 */
function array_trim($array){
    if(!is_array($array)){
        return trim($array);
    }
    return array_map('array_trim', $array);
}


/**
 * @param $keys
 * @param $params
 * 是否的值
 */
function param_is_or_no($keys,&$params){
    if(check_array($keys) && $params){
        foreach ($keys as $key){
            if(!isset($params[$key])){
                continue;
            }
            $params[$key] = trim($params[$key]) == 1?1:0;
        }
    }
}

/**
 * @param $keys
 * @param $params
 * @param int $point
 * 返回保留小数，默认2位
 */
function params_round($keys,&$params,$point = 2){
    if(check_array($keys) && $params){
        foreach ($keys as $key){
            if(!isset($params[$key])){
                continue;
            }
            $params[$key] = round(floatval(trim($params[$key])),$point);
        }
    }
}

/**
 * @param $keys
 * @param $params
 * 去掉小数位
 */
function params_floor($keys,&$params){
    if(check_array($keys) && $params){
        foreach ($keys as $key){
            if(!isset($params[$key])){
                continue;
            }
            $params[$key] = floor(trim($params[$key]));
        }
    }
}

/**
 * @param $num
 * @param int $point
 * @return float|int
 * 舍去法格式化数字
 */
function floor_float($num,$point = 2){
    if(!is_numeric($num)){
        return $num;
    }
    return floor($num*pow(10,$point))/pow(10,$point);
}



/**
 * @param array $data
 * @param string $message
 * @param int $code
 * json格式返回数据
 */
function to_json($code = 0,$message = '访问成功',$data =[]){
    // utf-8编码
    @header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(array(
        'data' => $data,
        'message' => $message,
        'code' => $code
    )));
}



//友好的时间显示
function date_friend_tips($time){
    if (!$time)
        return false;
    $d = TIMESTAMP - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}



/**
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float
 * 根据两个经纬度算距离
 */
function location_distance($lat1, $lng1, $lat2, $lng2){
    $earthRadius = 6378137;//单位:m
    $lat1 = ($lat1 * M_PI)/180;
    $lng1 = ($lng1 * M_PI)/180;
    $lat2 = ($lat2 * M_PI)/180;
    $lng2 = ($lng2 * M_PI)/180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

/**
 * @param $lng
 * @param $lat
 * @param float $distance 单位：km
 * @return array
 * 根据传入的经纬度，和距离范围，返回所有在距离范围内的经纬度的取值范围
 */
function location_range($lng, $lat,$distance = 0.5){
    $earthRadius = 6378.137;//单位km
    $d_lng =  2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat)));
    $d_lng = rad2deg($d_lng);
    $d_lat = $distance/$earthRadius;
    $d_lat = rad2deg($d_lat);
    return array(
        'lat_start' => $lat - $d_lat,//纬度开始
        'lat_end' => $lat + $d_lat,//纬度结束
        'lng_start' => $lng-$d_lng,//纬度开始
        'lng_end' => $lng + $d_lng//纬度结束
    );
}






/**
     * xml格式转换为数组
     * @param $xml
     * @return mixed
     */
   

    /**
     * 将数组转换成xml格式（简单方法）
     * @param $data
     * @return string
     */
     function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
     function createNoncestr($length =32){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";

        for($i=0;$i<$length;$i++){
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param $url              发送地址
     * @param $xmldata          发送文件格式
     * @param int $second       设置执行最长秒数
     * @param array $aHeader    设置头部
     * @return bool|mixed
     */
     function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array()){
        $isdir=IA_ROOT.'/attachment/kundian_farm/'.$this->uniacid.'/';
        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
//            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }


    /**
     * [sendMoney 企业付款到零钱]
     * @param  [type] $amount     [发送的金额（分）目前发送金额不能少于1元]
     * @param  [type] $re_openid  [发送人的 openid]
     * @param  string $desc       [企业付款描述信息 (必填)]
     * @param  string $check_name [收款用户姓名 (选填)]
     * @return [type]             [description]
     */
     function sendMoney($amount,$re_openid,$desc='测试',$check_name=''){
    	/*
    	$mch_appid=$this->APPID;
    	$nonce_str=$this->MCHID;
    	$secrect_key=$this->SECRECT_KEY;
    	*/
    	$mch_appid="wx1d0e6ff75bd2aa2c";
    	$nonce_str=1524645871;
    	$secrect_key="chenshangde18977081286huangliqun";
    	//设置OK
    	
        $total_amount = (100) * $amount;
        $data=array(
            'mch_appid'=>$mch_appid,//商户账号appid
            'mchid'=> $nonce_str,//商户号
            'nonce_str'=>$this->createNoncestr(),//随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),//商户订单号
            'openid'=> $re_openid,//用户openid
            'check_name'=>'NO_CHECK',//校验用户姓名选项,
            're_user_name'=> $check_name,//收款用户姓名
            'amount'=>$total_amount,//金额
            'desc'=> $desc,//企业付款描述信息
            'spbill_create_ip'=> IP,//Ip地址
        );
        //生成签名算法
        $secrect_key=$secrect_key;///这个就是个API密码。MD5 32位。
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        //生成签名算法
        $xml=$this->arraytoxml($data);

        //$url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res=$this->curl_post_ssl($url,$xml);
        $return=$this->xmltoarray($res);
        
        return $return;
    }





