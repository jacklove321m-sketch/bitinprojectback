var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"8001",
			edittitle:'',
			menuAOpeneds:[],
			lists:[],
			isloading:true,
			datatxt:"数据加载中",
			iseditshow:false,
			one:{
				title:'',
				pid:0,
				id:null
			},
			oneRulue:{
				title:[
					{ required: true, message: '请输入地区名称', trigger: 'blur' },
					{ type: 'string', min: 2, message: '地区名称至少2字符', trigger: 'blur' },
					{ type: 'string', max: 15, message: '地区名称最多15符', trigger: 'blur' }
				]
			},
			ac:{
				type:'add',
				index:null
			}
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				this.getlist();
			},
			edit:function(index){
				if(index>-1){
					this.edittitle="修改顶级地区";
					this.ac={
						type:'edit',
						index:index
					}
					var one={
						title:'',
						pid:0,
						id:null
					}
					this.one=one
				}else{
					this.edittitle="添加顶级地区";
					this.ac={
						type:'add',
						index:null
					}
					this.one={
						title:'',
						pid:0,
						id:null
					}
				}
				this.iseditshow=true;
			},
			editSub:function(name){
				this.$refs[name].validate((valid) => {
					if (valid) {
						var url="/okadmin/api/regions";
						var type="post";
						var oktxt="地区添加";
						if(this.ac.type=="edit"){
							url="/okadmin/api/regions/"+this.lists[this.ac.index].id;
							type="put";
							oktxt="地区修改";
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
									this.lists.push(data.data);
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
				this.$confirm('此操作将永久删除分类【'+this.lists[this.ac.index].name+'】, 是否继续?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/type/"+this.lists[this.ac.index].id,
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
			getlist:function(){
				this.isloading=true;
				this.lists=[];
				this.datatxt="数据加载中";
				$.ajax({
					url: "/okadmin/api/regions",
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