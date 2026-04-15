var app=new Vue({
		el:"#app",
		data:{
			
			menuActive:"8005",
			edittitle:'',
			menuAOpeneds:[],
			lists:[],
			isloading:true,
			datatxt:"数据加载中",
			iseditshow:false,
			one:{
				name:'',
				desc:"",
				issys:0,
				permissions:[],
				id:null
			},
			oneRulue:{
				name:[
					{ required: true, message: '请输入角色名称', trigger: 'blur' },
					{ type: 'string', min: 2, message: '角色名称至少2字符', trigger: 'blur' },
					{ type: 'string', max: 15, message: '角色名称最多15符', trigger: 'blur' }
				]
			},
			ac:{
				type:'add',
				index:null
			},
			permissions:[],
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
			edit:function(index,type){
				this.selpss=[];
				this.isedit=false;
				
				if(index>-1){
					this.edittitle="修改角色";
					if(type){
						this.isedit=true;
						this.edittitle="查看角色";
					}
					this.ac={
						type:'edit',
						index:index
					}
					var one={
						name:this.lists[index].name,
						desc:this.lists[index].desc,
						issys:this.lists[index].issys,
						permissions:this.lists[index].permissions,
						id:this.lists[index].id
					}
					var p=this.lists[index].permissions;
					for(var i=0;i<p.length;i++){
						this.selpss.push(p[i])
					}
					//=one.permissions;
					this.one=one
				}else{
					this.edittitle="添加角色";
					this.ac={
						type:'add',
						index:null
					}
					this.one={
						name:'',
						desc:"",
						permissions:[]
					}
				}
				this.iseditshow=true;
			},
			editSub:function(name){
				this.$refs[name].validate((valid) => {
					if (valid) {
						var url="/okadmin/api/roles";
						var type="post";
						var oktxt="角色添加";
						this.one.permissions=[];
						for(var i=0;i<this.selpss.length;i++){
							this.one.permissions.push(this.selpss[i]);
						}
						if(this.ac.type=="edit"){
							url="/okadmin/api/roles/"+this.lists[this.ac.index].id;
							type="patch";
							oktxt="角色修改";
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
				this.$confirm('此操作将永久删除角色【'+this.lists[this.ac.index].name+'】, 是否继续?', '提示', {
					  confirmButtonText: '确定',
					  cancelButtonText: '取消',
					  type: 'warning'
					}).then(() => {
						$.ajax({
							url: "/okadmin/api/roles/"+this.lists[this.ac.index].id,
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
					url: "/okadmin/api/roles",
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
					url: "/okadmin/api/allPermissions",
					type: "get",
					dataType: "json",
					contentType: "application/json",
					data:"",
					success: function(data) {
						this.isloading=false;
						this.permissions=data.data;
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