<?php
return 1;
/**
 * curl获取数据
 * @param $url
 * @return mixed
 */
function get_url($url)
{

    $ifpost = 1;
    $datafields = '';
    $cookiefile = 'Hm_lvt_e701a0642308450b35aa945c744f36d0=1581654050; Hm_lpvt_e701a0642308450b35aa945c744f36d0=1581654050; UM_distinctid=17041ed85ff469-058366f8cfabc9-3c604504-1fa400-17041ed86007a3; CNZZDATA1257048964=352709359-1581649939-%7C1581649939; PHPSESSID=04210ac637c8e1802df187f40f724066';
    $v = false;
    //构造随机ip
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
//模拟http请求header头
    $header = array("Content-Type: application/x-www-form-urlencoded",);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $v);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $ifpost && curl_setopt($ch, CURLOPT_POST, $ifpost);
    $ifpost && curl_setopt($ch, CURLOPT_POSTFIELDS, $datafields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
    curl_setopt($ch,CURLOPT_TIMEOUT,60); //允许执行的最长秒数
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
     // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
     // 把post的变量加上key=单职业&t=8&opendate=1
    $data = array(
		"key" => "单职业",
		"t" => "6",
		"opendate" => "1"
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $ok = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    return $ok;
}

$aa=request_post('https://sfhao.com/searchs.php','{"key":43,"t":0,"opendate":20200214}');

print_r($aa);

function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
		 $cookiefile = 'Hm_lvt_e701a0642308450b35aa945c744f36d0=1581654050; Hm_lpvt_e701a0642308450b35aa945c744f36d0=1581654050; UM_distinctid=17041ed85ff469-058366f8cfabc9-3c604504-1fa400-17041ed86007a3; CNZZDATA1257048964=352709359-1581649939-%7C1581649939; PHPSESSID=04210ac637c8e1802df187f40f724066';
		$header = array("Content-Type: application/x-www-form-urlencoded","Origin:https://www.sfhao.com","Host:www.sfhao.com","Cookie:Hm_lvt_e701a0642308450b35aa945c744f36d0=1581654050; Hm_lpvt_e701a0642308450b35aa945c744f36d0=1581654050; UM_distinctid=17041ed85ff469-058366f8cfabc9-3c604504-1fa400-17041ed86007a3; CNZZDATA1257048964=352709359-1581649939-%7C1581649939; PHPSESSID=04210ac637c8e1802df187f40f724066");
        $ch = curl_init();//初始化curl
		
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,true); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);// 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//跟踪重定向
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        //$data = json_decode($data);
		 //return $ch;
        return $data;
}
?>