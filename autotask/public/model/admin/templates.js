var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"803",
			menuAOpeneds:[],
			lists:[],
			isloading:true,
			datatxt:"数据加载中",
			iseditshow:false,
			one:{
				file:"",
				name:"",
				dec:""
			},
			oneRulue:{
				file:[
					{ required: true,message: '请上传模板', trigger: 'blur' }
				],
				name:[
					{ required: true,message: '请输入模板名称', trigger: 'blur' },
					{ type: "string",min:2,max:20,message: '模板名称长度为2-20', trigger: 'blur' },
				],
				dec:[
					{ required: true,message: '请输入描述', trigger: 'blur' }
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
						dec:this.lists[index].dec,
						name:this.lists[index].name,
						file:this.lists[index].file
					}
					this.one=one
				}else{
					this.ac={
						type:'add',
						index:null
					}
					this.one={
						file:"",
						name:"",
						dec:""
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
						var url="/okadmin/templates";
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
							url: "/okadmin/template/"+this.lists[this.ac.index].id,
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
			yulan:function(index){
				this.ac.index=index;
				
			},
			fileClick:function(){
				$('#file').click();
			},
			getlist:function(){
				this.isloading=true;
				this.lists=[];
				this.datatxt="数据加载中";
				$.ajax({
					url: "/okadmin/templateslist",
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
			  upok:function(files, fileList){
				  this.one.file=files.url;
			  },
			  uperror:function(err, file, fileList){
				  console.log(JSON.stringify(err))
				  console.log(JSON.stringify(file))
				  console.log(JSON.stringify(fileList))
				 this.$message({
					  showClose: true,
					  message: file.msg,
					  type: 'error'
					});
			  },
			   beforeAvatarUpload(file) {
					const isJPG = file.type === 'text/html';
					const isLt2M = file.size / 1024 / 1024 < 2;

					if (!isJPG) {
					  this.$message.error('请上传html类型文件!');
					}
					if (!isLt2M) {
					  this.$message.error('上传文件大小不能超过 2MB!');
					}
					return isJPG && isLt2M;
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
	
	