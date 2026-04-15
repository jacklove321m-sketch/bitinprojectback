;(function(){
	$.fn.myTree=function(options){
	
		$.fn.myTree.defaults = {
			obj: this
			,subClass:'sub'
		};
		var o=$.extend({},$.fn.myTree.defaults,options);
		var $obj=o.obj;
		var $openDOM=$('<i class="glyphicon glyphicon-folder-close"></i>');
		var $closeDOM=$('<i class="glyphicon glyphicon-folder-open"></i>');
		$obj.find('ul.'+o.subClass).first().prev('a').prepend($openDOM);
		$obj.find('ul.'+o.subClass).first().find('li').addClass('active');
		$obj.find('ul.'+o.subClass).first().find('ul.sub').show().prev('a').each(function(idx,n){
				$(n).text(function(i,s){
					//return s.trim();
				})
		}).prepend($closeDOM);
		
	/*	为满足需要，把展开/合上功能注释掉了
		//为字节点绑定事件
		$obj.find('ul.'+o.subClass+'>li>a').on('click',function(){
			if($(this).parent().attr('class')=='active'){
				//如果子节点打开则隐藏
				$(this).next('ul.sub').hide('fast',function(){	
					$(this).parent().removeClass('active').children('a').children('i.glyphicon-folder-open').removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');		
					}
				)
			}else{
				//如果子节点隐藏则打开 
				$(this).next('ul.sub').show('fast',function(){
					$(this).parent().addClass('active').children('a').children('i.glyphicon-folder-close').removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
					}
				)
			}
		})*/
	}
})(jQuery)