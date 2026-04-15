var app=new Vue({
		el:"#app",
		data:{
			menuActive:"8006",
			edittitle:'',
			menuAOpeneds:[],
			lists:[],
			isloading:true,
			datatxt:"数据加载中",
			iseditshow:false,
			one:{
				account_name:'',
				user_name:"",
				role:null,
				password:"",
				id:null
			},
			oneRulue:{
				account_name:[
					{ required: true, message: '请输入登录账号', trigger: 'blur' },
					{ type: 'string', min: 6, message: '登录账号至少6字符', trigger: 'blur' },
					{ type: 'string', max: 12, message: '登录账号最多12符', trigger: 'blur' }
				],
				user_name:[
					{ required: true, message: '请输入姓名', trigger: 'blur' },
					{ type: 'string', min: 2, message: '姓名至少2字符', trigger: 'blur' },
					{ type: 'string', max: 12, message: '姓名最多12符', trigger: 'blur' }
				]
			},
			ac:{
				type:'add',
				index:null
			},
			permissions:[],
			roles:[],
			selpss:[],
			isedit:false
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				this.getallpermissions();
				this.getlist();
			},
			edit:function(index){
				this.selpss=[];
				if(index>-1){
					this.edittitle="修改管理员";
					this.ac={
						type:'edit',
						index:index
					}
					var one={
						account_name:this.lists[index].account_name,
						user_name:this.lists[index].user_name,
						role:this.lists[index].role,
						id:this.lists[index].id
					}
					this.one=one
				}else{
					this.edittitle="添加管理员";
					this.ac={
						type:'add',
						index:null
					}
					this.one={
						account_name:'',
						user_name:"",
						role:null,
						password:"",
					}
				}
				this.iseditshow=true;
			},
			editSub:function(name){
				this.$refs[name].validate((valid) => {
					if (valid) {
						var url="/okadmin/api/admins";
						var type="post";
						var oktxt="管理员添加";
						if(this.ac.type=="edit"){
							url="/okadmin/api/admins/"+this.lists[this.ac.index].id;
							type="patch";
							oktxt="管理员修改";
						}
					   var query=this.one;
						$.ajax({
							url: url,
							type: type,
							dataType: "json",
							contentType: "application/json",
							data:JSON.stringify(query),
							success: function(data) {
								this.$message.success(oktxt+"成功");
								if(this.ac.type=="add"){
									this.lists.splice(0,0,data.data);
								}else{
									this.lists.splice(this.ac.index,1,data.data);
								}
								this.iseditshow=false;
							}.bind(this),
							error:function(err){
							   
							}.bind(this)
						});
					}
				})
			},
			del:function(index){
				this.ac.index=index;
				this.$confirm('此操作将永久删除 '+this.lists[this.ac.index].user_name+'【'+this.lists[this.ac.index].account_name+'】, 是否继续?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/api/admins/"+this.lists[this.ac.index].id,
							type: "delete",
							dataType: "json",
							contentType: "application/json",
							data:"",
							success: function(data) {
								this.$message.success("删除成功");
								this.lists.splice(this.ac.index,1);
							}.bind(this),
							error:function(err){
							   
							}.bind(this)
						});
					  
					}).catch(() => {
					           
					});
			},
			repass:function(index){
				this.ac.index=index;
				this.$confirm('此操作将把 '+this.lists[this.ac.index].user_name+'【'+this.lists[this.ac.index].account_name+'】的密码重置为 123456, 是否继续?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/api/admins/"+this.lists[this.ac.index].id+"/repass",
							type: "patch",
							dataType: "json",
							contentType: "application/json",
							data:"",
							success: function(data) {
								this.$message.success("重置密码成功");
							}.bind(this),
							error:function(err){
							   
							}.bind(this)
						});
					  
					}).catch(() => {
					           
					});
			},
			editstate:function(index){
				this.ac.index=index;
				var state=this.lists[this.ac.index].state;
				var actxt="禁用"
				var newstate=2
				if(state==2){
					actxt="启用"
					newstate=1
				}
				var query={
					state:newstate
				}
				this.$confirm('确定要 '+actxt+this.lists[this.ac.index].user_name+'【'+this.lists[this.ac.index].account_name+'】吗?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/api/admins/"+this.lists[this.ac.index].id+"/state",
							type: "patch",
							dataType: "json",
							contentType: "application/json",
							data:JSON.stringify(query),
							success: function(data) {
								this.$message.success(actxt+"成功");
								this.lists[this.ac.index].state=newstate;
								console.log(JSON.stringify(this.lists[this.ac.index]))
							}.bind(this),
							error:function(err){
							   
							}.bind(this)
						});
					}).catch(() => {
					           
					});
			},
			getlist:function(){
				this.isloading=true;
				this.lists=[];
				this.datatxt="数据加载中";
				$.ajax({
					url: "/okadmin/api/admins",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.isloading=false;
						
						this.lists=data.data;
						if(this.lists.length<1){
							this.datatxt="没有数据";
						}
					}.bind(this),
					error:function(err){
					   
					}.bind(this)
				});
			},
			getallpermissions:function(){
				$.ajax({
					url: "/okadmin/api/roles",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.roles=data.data;
					}.bind(this),
					error:function(err){
					   
					}.bind(this)
				});
			},
			selAp:function(id){
				if(comm.isArrayCon(this.selpss,id)){
					for(var i=0;i<this.selpss.length;i++){
						if(id==this.selpss[i]){
							this.selpss.splice(i,1)
						}
					}
				}else{
					this.selpss.push(id);
				}
				//console.log(JSON.stringify(this.one.permissions));
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