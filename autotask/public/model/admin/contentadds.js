var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"802",
			menuAOpeneds:[],
			nowtimes:"23",
			one:{
				typeid:'',
				contents:[
					{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''}
				]
			},
			ruleOne:{
				typeid:[
					{ required: true,type: 'number', message: '请选择分类', trigger: 'blur' }
				],
				c:[
					{ required: true, message: '请输入内容', trigger: 'blur' }
				]
			},
			types:[]
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				this.gettype();
			},
			gettype:function(){
				this.types=[];
				$.ajax({
					url: "/okadmin/typelist",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.types=data.data;
					}.bind(this),
					error:function(err){
					   
					}.bind(this)
				});
			},
			adds:function(name){
				this.$refs[name].validate((valid) => {
				  if (valid) {
					
					var query=this.one;
						$.ajax({
								  url: "/okadmin/contentadds",
								  type: "post",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									this.one.contents=[
										{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''},{c:''}
									]
									this.$message.success("批量添加成功！");
							   }.bind(this),
							   error:function(err){
								  
								}.bind(this)
							});
				  } else {
					return false;
				  }
				});
			},
			addonecontent:function(){
				this.one.contents.push("");
			},
			uppassSub:function(formName){
				this.$refs[formName].validate((valid) => {
				  if (valid) {
					var old=comm.sTrim(comm.uppassForm.oldpass)
					var newp=comm.sTrim(comm.uppassForm.newpass)
					var renewp=comm.sTrim(comm.uppassForm.repass)
					var query={
							password:old,
							newPassword:newp
						};
						$.ajax({
								  url: "/okadmin/uppass",
								  type: "post",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									comm.uppassForm.newpass="";
									comm.uppassForm.oldpass="";
									comm.uppassForm.renewpass="";
									comm.uppassForm=false;
									this.$message.success("密码修改成功。请使用新密码重新登录！");
									setTimeout(function () {
										window.location.href="/okadmin/loginout";
									}, 3000)
							   }.bind(this),
							   error:function(err){
								   this.$message.error(JSON.parse(err.responseText).msg);
								}.bind(this)
							});
				  } else {
					return false;
				  }
				});
						
			},
			
			
		}
	})