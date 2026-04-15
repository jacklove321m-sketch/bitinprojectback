var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"8004",
			menuAOpeneds:[],
			nowtimes:"23",
			one:{
				sitename:'',
				domain_name:'',
				kf_weixin:'',
				kf_qq:'',
				kf_qq2:'',
				kf_400:'',
				kf_wxcode:'',
				site_desc:'',
				site_keyword:'',
				client_name:''
			},
			ruleOne:{
				sitename:[
					{ required: true, message: '请输入4-20位系统名称', trigger: 'blur',min:4,max:20}
				],
				domain_name:[
					{ required: true, message: '请输入正确的网址，如abc.com', trigger: 'blur'}
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
				this.getsysinfo();
			},
			fileClick:function(){
				$('#file').click();
			},
			upok:function(files, fileList){
				  this.one.kf_wxcode=files.url;
			  },
			  uperror:function(err, file, fileList){
				  console.log(JSON.stringify(err))
				  console.log(JSON.stringify(file))
				  console.log(JSON.stringify(fileList))
				  this.$message.error(file.msg);
			  },
		   beforeAvatarUpload(file) {
			   console.log(file.type)
				const isJPG = (file.type === 'image/jpeg' || file.type === 'image/png');
				const isLt2M = file.size / 1024 / 1024 < 2;

				if (!isJPG) {
				  this.$message.error('请上传JPG,PNG类型图片!');
				  return false;
				}
				if (!isLt2M) {
				  this.$message.error('上传文件大小不能超过 2MB!');
				  return  false;
				}
				return isJPG && isLt2M;
			  },
			adds:function(name){
				this.$refs[name].validate((valid) => {
				  if (valid) {
					
					var query=this.one;
						$.ajax({
								  url: "/admin/admin/systemup",
								  type: "put",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									
									this.$message.success("保存成功！");
							   }.bind(this),
							   error:function(err){
								  
								}.bind(this)
							});
				  } else {
					return false;
				  }
				});
			},
			getsysinfo:function(){
				$.ajax({
					  url: "/admin/admin/sysinfo",
					  type: "get",
					  dataType: "json",
					  contentType: "application/json",
					  data:"",
					  success: function(data) {
						this.one=data.data;
				   }.bind(this),
				   error:function(err){}.bind(this)
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
								  url: "/admin/admin/uppass",
								  type: "post",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									comm.uppassForm.newpass="";
									comm.uppassForm.oldpass="";
									comm.uppassForm.renewpass="";
									comm.uppassForm=false;
									comm.uppassISshow=false;
									this.$message.success("密码修改成功。请使用新密码重新登录！");
									setTimeout(function () {
										window.location.href="/admin/admin/loginout";
									}, 2000)
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