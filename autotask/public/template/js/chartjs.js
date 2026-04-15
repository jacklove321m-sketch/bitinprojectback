
$(document).ready(function(e) {
    //自动计算浏览器窗口高度
	bodyAH=$(window).height();
	mainCH();
	
});


$(window).resize(function(e) {
	//自动计算浏览器窗口高度
	bodyAH=$(window).height();
	mainCH();	
});

function mainCH(){
	aheigh=bodyAH-$(".charttop").height();
	$(".chartConbox").height(aheigh);
	//$(".chartConbox").text(aheigh);
	$(".chartbox").height(aheigh);
}