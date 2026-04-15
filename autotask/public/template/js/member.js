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
    $(".incah").height(bodyH-355);
	if($(".incah").height()<210){
		$(".incah").height(210)
	}
	$(".in-rbox").height($(".mainconbox").height()-61);
	$(".in-rbox").width($(".in-rbox-w").width()-2);
	if($(".in-rbox").height()<180){
		$(".in-rbox").height(180);
	}
	
}


//
function xxphotoulimgautoh(){
	myimgw=$(".xxphotoul li").width()*0.75;
	$(".xxphotoul li").find("img").height(myimgw);
}

//后台 头部导航选中效果
function tonavjs(){
	$(".topnavboxul li").each(function(index, element) {
		if($(this).attr("data-navid")==topnavid){
			$(this).attr("class","cur");
	     };
		
    });
}



//会员中心弹出页面  所有弹出页面均在member目录下

//查看新闻公告
function shownews(newsid){
	var diag = new Dialog();
	diag.Width = 800;
	diag.Height = 440;
	//diag.Title = "设定了高宽和标题的普通窗口";
	diag.ShowButtonRow=true;
	diag.URL = "/index.php?app=home&c=shownews&id="+newsid;
	diag.show();
	diag.okButton.value=" s ";
	diag.cancelButton.value="关 闭";
	$("#_ButtonOK_"+diag.ID).hide();
}

//查看项目通知公告（重点关注）
function shownt(newsid,xmnm){
	var diag = new Dialog();
	diag.Width = 400;
	diag.Height = 300;
	//diag.Title = "设定了高宽和标题的普通窗口";
	diag.ShowButtonRow=true;
	diag.URL = "/index.php?app=home&c=shownt&id="+newsid+"&xmnm="+xmnm;
	diag.show();
	diag.okButton.value=" s ";
	diag.cancelButton.value="关 闭";
	$("#_ButtonOK_"+diag.ID).hide();
}


//修改密码
function upuserpass(){
	var diag = new Dialog();
	diag.Width = 400;
	diag.Height = 160;
	//diag.Title = "设定了高宽和标题的普通窗口";
	diag.ShowButtonRow=true;
	diag.URL = "uppass.html";
	diag.show();
	diag.okButton.value=" 保 存 ";
	diag.cancelButton.value="取 消";
}
//退出登录
function loginOut(){
	Dialog.confirm('确定要退出系统吗？',function(){
		window.location="index.php?app=home&c=logout";
	});
}











//选项卡
function setTab(name,cursel,n){
 for(i=1;i<=n;i++){
  var menu=document.getElementById(name+i);
  var con=document.getElementById("c_"+name+"_"+i);
  menu.className=i==cursel?"hover":"";
  con.style.display=i==cursel?"block":"none";
 }
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





/*底部跑马灯*/
function btpmd(){
var myspeed=40
var tabLeft=document.getElementById("bootpmd");
var tab1=document.getElementById("adbox1");
var tab2=document.getElementById("adbox2");
tab2.innerHTML=tab1.innerHTML;

function Marquee1(){
if(tabLeft.scrollLeft>=tab2.offsetWidth)
tabLeft.scrollLeft-=tab1.offsetWidth
else{
tabLeft.scrollLeft+=2;
}
}

var MyMar=setInterval(Marquee1,myspeed);
tabLeft.onmouseover=function() {clearInterval(MyMar)};
tabLeft.onmouseout=function() {MyMar=setInterval(Marquee1,myspeed)};
}