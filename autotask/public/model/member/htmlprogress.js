var app=new Vue({
		el:"#app",
		data(){
			return{
				menuActive:"102",
				menuAOpeneds:[],
				nowtimes:"23",
				
			}
			
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				
			},
			sub:function(formName){
				this.$refs[formName].validate((valid) => {
				  if (valid) {
					  var query=this.one;
						$.ajax({
								  url: "/member/createtask",
								  type: "post",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									
							   }.bind(this),
							   error:function(err){
								   //this.$message.error(JSON.parse(err.responseText).msg);
								}.bind(this)
							});
				  } else {
					return false;
				  }
				});
						
			},
			keycount:function(){
				var key=this.one.key.split("\n");
				var newkey=[];
				for(var i=0;i<key.length;i++){
					if(key[i]){
						newkey.push(key[i])
					}
				}
				
				return newkey.length;
			},
			getalltemplates:function(){
				$.ajax({
					url: "/member/tp",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.templates=data.templates;
						this.types=data.types
					}.bind(this),
					error:function(err){ }.bind(this)
				});
			},
			addtitle:function(type){
				var o={
					type:type,
					con:''
				}
				switch(type){
					case "key":
						o.con="key";
						break;
					case "dynamic":
						o.con="dynamic";
						break;
					case "_":
						o.con="_";
						break;
					case "-":
						o.con="-";
						break;
					case "input":
						o.con="";
						break;
					case "sitename":
						o.con="网站名称";
						break;
					case "title":
						o.con="文章标题";
						break;
				}
				this.one.title.push(o);
			},
			deltitle:function(index){
				this.one.title.splice(index,1)
			},
			delkey:function(tag){
				this.one.key.splice(this.one.key.indexOf(tag),1);
			},
			showonekeyinput:function(){
				this.onekeyinputIsshow = true;
				this.$nextTick(_ => {
				  this.$refs.saveTagInput.$refs.input.focus();
				});
			},
			hideonekeyinput:function(){
				let onekeyvalue = this.onekeyvalue;
				if (onekeyvalue) {
				  this.one.key.push(onekeyvalue);
				}
				this.onekeyinputIsshow = false;
				this.onekeyvalue = '';
			},
			
		}
	})