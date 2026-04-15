//对话

$(document).ready(function(e) {
  
	//工具操作
	$(".toolbar .vol_ok").click(function(e) {
        $(this).hide();
		$(".toolbar .vol_no").show();
    });
	$(".toolbar .vol_no").click(function(e) {
        $(this).hide();
		$(".toolbar .vol_ok").show();
    });
	
	$(".toolbar .win_max").click(function(e) {
        $(this).hide();
		$(".toolbar .win_back").show();
    });
	$(".toolbar .win_back").click(function(e) {
        $(this).hide();
		$(".toolbar .win_max").show();
    });
	$(".toolbar .win_hide").click(function(e) {
       $(".wegmsgbox").hide();
    });
	
});


$(window).resize(function(e) {
	
});