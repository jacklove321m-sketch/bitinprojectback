


var comm = new Vue({
    data () {
		return {
			
		}
	},
	created: function() {
		if(this.getUrlparameter("key")){
			//this.searchkey=this.getUrlparameter("key");
			
		}
		$.ajaxSetup({
				error:function(jqXHR, textStatus, errorThrown) {
					switch(jqXHR.status) {
					  case(500):
						diytipshow("err","服务器系统内部错误，请联系管理处理");
						break;
					   case(401):
						diytipshow("err","未登录或登录超时，请登录");
						setTimeout(function () {
							window.location.reload();
						}, 2000)
						break;
					   case(403):
						diytipshow("err","无权限执行此操作");
						break;
					   case(400):
						diytipshow("err",JSON.parse(jqXHR.responseText).msg);
						break;
					   case(408):
						diytipshow("err","请求超时");
						break;
					  //default:
						//diytipshow("err","未知错误");
					 }
					
				}.bind(this)
		});
		
	},
	methods:{
		sokey:function(){
			
		},
		isArrayCon:function(arr, val){
			for(var i=0; i<arr.length; i++){
				if(arr[i] == val)
					return true;
			}
			return false;
		},
		getUrlparameter:function(nametxt){//获取网址参数
			var reg = new RegExp("(^|&)" + nametxt + "=([^&]*)(&|$)", "i");
			var r = window.location.search.substr(1).match(reg);
			if (r != null){
				return unescape(r[2]);
			}else{
				return null;
			}
		},
		sTrim:function(str){
			if(!str){return ''};
			return str.replace(/^(\s|\u00A0)+/,'').replace(/(\s|\u00A0)+$/,'');
		},
		checkPhone:function(str){
			var reg=/^0?1[3|4|5|6|7|8|9][0-9]\d{8}$/;
			return reg.test(str);
		},
		searchKey:function(){
			window.location.href="/search?key="+this.sTrim(this.searchkey);
		}
	}
});

