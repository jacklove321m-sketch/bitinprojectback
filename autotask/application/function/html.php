<?php
//html-WEB标签处理
//统计字符长度
function getStrlen($str,$code = 'UTF-8'){
	$strlen=0;
	if($code == 'UTF-8'){
		 $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/"; 
        preg_match_all($pa, $str, $t_string); 
		$strlen=count($t_string[0]);
	}else{
		$strlen = strlen($str);
	}
	return $strlen;
}
 //获取中英文字符的长度
function getStrlen2($str){
	if ($str){
		return mb_strlen($str,'utf8');
	}else{
		return 0;
	}
}

//截字，不够数时末尾加省略号
function cut_str($string, $sublen,$after='', $start = 0, $code = 'UTF-8') 
{ 
    if($code == 'UTF-8') 
    { 
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/"; 
        preg_match_all($pa, $string, $t_string); 
        if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)).$after; 
        return join('', array_slice($t_string[0], $start, $sublen));  
    } 
    else 
    { 
        $start = $start*2; 
        $sublen = $sublen*2; 
        $strlen = strlen($string); 
        $tmpstr = ''; 
        for($i=0; $i<$strlen; $i++) 
        { 
            if($i>=$start && $i<($start+$sublen)) 
            { 
                if(ord(substr($string, $i, 1))>129) 
                { 
                    $tmpstr.= substr($string, $i, 2); 
                } 
                else 
                { 
                    $tmpstr.= substr($string, $i, 1); 
                } 
            } 
            if(ord(substr($string, $i, 1))>129) $i++; 
        } 
        if(strlen($tmpstr)<$strlen ) $tmpstr.= $after; 
        return $tmpstr; 
    } 
}


//HTML转txt
function html2txt($html){
	$html=strip_tags($html);
	$html=str_replace("&nbsp;",'',$html);
	$html=str_replace(" ",'',$html);
	$html=str_replace("　",'',$html);
	$html=str_replace(chr(13),'',$html);
	$html=str_replace(array("\r\n","\n","\r"),'',$html);
	return $html;
}
//去掉HTML样式style
function removeA_Style($html){
	$html=str_replace("&nbsp;",'',$html);
	$html=str_replace(" ",'',$html);
	$html=str_replace("　",'',$html);
	$html=preg_replace("/style=.+?['|\"]/i",'',$html);
	$html=preg_replace("/<a[^>]*>(.*?)<\/a>/is", "$1",$html);
	return $html;
}


//url base64编码
function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}
//url base64解码
function urlsafe_b64decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

function CutAbnormalHtml($str){//去掉yi2,zhu2,shang2的非法格式
	$str=str_replace(array("\r\n", "\r", "\n",chr(13),"↵"),'',$str);
	$str=str_replace(array("</div>", "</div >"),'<br>',$str);
	$str=str_replace(array("<span>", "<span >","<div>", "<div >","</span>", "</span >"),'',$str);
	$str=str_replace(array("<span", "<spa", "<sp", "<s", "<div", "<di", "<d"),'',$str);
	$str=str_replace(array("</div", "</span"),'',$str);
	$str=str_replace(array("</di", "</spa"),'',$str);
	$str=str_replace(array("</d", "</sp"),'',$str);
	$str=str_replace(array("</", "</s"),'',$str);
	//$str=strip_tags($str);
	return $str;
}

//闭合HTML标签
function fix_html_tags($input, $single_tags = array()) {
    $result = null;
    $stack = array();//标签栈
    $_single_tags = array('br', 'hr', 'img', 'input');//合理的单标签
 
    if ($single_tags && is_array($single_tags)) {
        $_single_tags = array_merge($_single_tags, $single_tags);
        $_single_tags = array_map('strtolower', $_single_tags);
        $_single_tags = array_unique($_single_tags);
    }
    //返回标签分隔之后的内容，包括标签本身
    $content = preg_split('/(<[^>]+>)/si', $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
 
    foreach ($content as $val) {
        //匹配未闭合的自闭合标签 如 <br> <hr> 等
        if (preg_match('/<(\w+)[^\/]*>/si', $val, $m) && in_array(strtolower($m[1]), $_single_tags) ) {
            $result .= "\r\n" . $val;
        }
        //匹配标准书写的自闭合标签，直接返回，不入栈
        else if (preg_match('/<(\w+)[^\/]*\/>/si', $val, $m)) {
            $result .= $val;
        }
        //匹配开始标签，并入栈
        else if (preg_match('/<(\w+)[^\/]*>/si', $val, $m)) {
            $result .= "\r\n" . str_repeat("\t", count($stack)) . $val;
            array_push($stack,  $m[1]);
        }
        //匹配闭合标签，匹配前先判断当前闭合标签是栈顶标签的闭合，优先闭合栈顶标签
        else if (preg_match('/<\/(\w+)[^\/]*>/si', $val, $m)) {
            //出栈，多余的闭合标签直接舍弃
            if (strtolower(end($stack)) == strtolower($m[1])) {
                array_pop($stack);
                $result .= $val;
            }
        } else {
            $result .= $val;
        }
    }
 
    //倒出所有栈内元素
    while ($stack) {
        $result .= "</".array_pop($stack).">";
        $result .= "\r\n";
    }
 
    return $result;
}



//生成拼音
function str2pinyin($str){
	require_once('pinyin/ChinesePinyin.class.php');
	$Pinyin=new ChinesePinyin();
	$strPinyin = $Pinyin->TransformWithTone($str);
	return $strPinyin;
}
function get_pinyinto($str){
	//拼音分行 start
	$str=str_replace(array("<p>","<P>"),'',$str);
	$str=str_replace(array("<br />","<br>","</p>","/P"),chr(13),$str);
	$pyarr=explode(chr(13),$str);
	//print_r($pyarr);
	$pybody="";
	//调用拼音程序
	//str2pinyin()
	require_once 'pinyin/ChinesePinyin.class.php';
	//require_once (dirname(__FILE__) . "/ChinesePinyin.class.php");
	$Pinyin=new ChinesePinyin();
	
	foreach($pyarr as $k=> $v){
			$pys = $Pinyin->TransformWithTone($v);
			$pybody.='<div class="pyshowbox00999"><span class="py">'.$pys.'</span>';
			$pybody.='<b>'.$v.'</b></div>';
		}
	return $pybody;
}

//取汉字首字母
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    
    $fchar = ord($str{0});
    
    if ($fchar >= ord('A') && $fchar <= ord('z'))
        return strtoupper($str{0});
    
    $s1 = iconv('UTF-8', 'gb2312', $str);
    
    $s2 = iconv('gb2312', 'UTF-8', $s1);
    
    $s = $s2 == $str ? $s1 : $str;
    
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    
    if ($asc >= -20319 && $asc <= -20284)
        return 'A';
    
    if ($asc >= -20283 && $asc <= -19776)
        return 'B';
    
    if ($asc >= -19775 && $asc <= -19219)
        return 'C';
    
    if ($asc >= -19218 && $asc <= -18711)
        return 'D';
    
    if ($asc >= -18710 && $asc <= -18527)
        return 'E';
    
    if ($asc >= -18526 && $asc <= -18240)
        return 'F';
    
    if ($asc >= -18239 && $asc <= -17923)
        return 'G';
    
    if ($asc >= -17922 && $asc <= -17418)
        return 'H';
    
    if ($asc >= -17417 && $asc <= -16475)
        return 'J';
    
    if ($asc >= -16474 && $asc <= -16213)
        return 'K';
    
    if ($asc >= -16212 && $asc <= -15641)
        return 'L';
    
    if ($asc >= -15640 && $asc <= -15166)
        return 'M';
    
    if ($asc >= -15165 && $asc <= -14923)
        return 'N';
    
    if ($asc >= -14922 && $asc <= -14915)
        return 'O';
    
    if ($asc >= -14914 && $asc <= -14631)
        return 'P';
    
    if ($asc >= -14630 && $asc <= -14150)
        return 'Q';
    
    if ($asc >= -14149 && $asc <= -14091)
        return 'R';
    
    if ($asc >= -14090 && $asc <= -13319)
        return 'S';
    
    if ($asc >= -13318 && $asc <= -12839)
        return 'T';
    
    if ($asc >= -12838 && $asc <= -12557)
        return 'W';
    
    if ($asc >= -12556 && $asc <= -11848)
        return 'X';
    
    if ($asc >= -11847 && $asc <= -11056)
        return 'Y';
    
    if ($asc >= -11055 && $asc <= -10247)
        return 'Z';
    
    return null;
    
}











?>