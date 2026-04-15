var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"802",
			menuAOpeneds:[],
			lists:[],
			isloading:true,
			datatxt:"数据加载中",
			iseditshow:false,
			one:{
				typeid:null,
				content:""
			},
			oneRulue:{
				typeid:[
					{ required: true,type: 'number', message: '请选择分类', trigger: 'blur' }
				],
				content:[
					{ required: true,message: '请输入内容', trigger: 'blur' },
					{ type: 'string', min: 20, message: '内容至少20字符', trigger: 'blur' }
				]
			},
			ac:{
				type:'add',
				index:null
			},
			types:[],
			total:0,
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				this.gettype();
				this.getlist();
			},
			typeid2str:function(id){
				for(var i=0;i<this.types.length;i++){
					if(this.types[i].id==id){
						return this.types[i].name;
					}
				}
			},
			edit:function(index){
				if(index>-1){
					this.ac={
						type:'edit',
						index:index
					}
					var one={
						content:this.lists[index].content,
						typeid:this.lists[index].typeid
					}
					this.one=one
				}else{
					this.ac={
						type:'add',
						index:null
					}
					this.one={
						typeid:null,
						content:""
					}
				}
				this.iseditshow=true;
			},
			edits:function(){
				window.location.href="/okadmin/contentadds";
			},
			editSub:function(name){
				this.$refs[name].validate((valid) => {
					if (!valid) {
						//this.$Message.error('表单填写不完整');
					} else {
						var url="/okadmin/content";
						var type="post";
						var oktxt="内容添加";
						if(this.ac.type=="edit"){
							url="/okadmin/content/"+this.lists[this.ac.index].id;
							type="put";
							oktxt="内容修改";
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
									this.getlist();
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
				this.$confirm('此操作将永久删除该内容, 是否继续?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/content/"+this.lists[this.ac.index].id,
							type: "delete",
							dataType: "json",
							contentType: "application/json",
							data:"",
							success: function(data) {
								this.$message.success("删除成功");
								this.getlist();
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
					url: "/okadmin/contentslist",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.isloading=false;
						
						this.lists=data.data;
						this.total=data.total;
						if(this.lists.length<1){
							this.datatxt="没有数据";
						}
					}.bind(this),
					error:function(err){
					   
					}.bind(this)
				});
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