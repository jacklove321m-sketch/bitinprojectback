var app=new Vue({
		el:"#app",
		data(){
			var filename_check = (rule, value, callback) => {
					if (!value) {
						return callback(new Error('文件名规则'));
					} else {
					const  reg = /^[1a]{6,30}$/
					//console.log(reg.test(value))
					if (reg.test(value)) {
						callback();
					} else {
						return callback(new Error('文件名规则6-30位a和1组合'));
					}
				}
			};
			var title_check = (rule, value, callback) => {
					if (!value.length) {
						return callback(new Error('请选择/输入页面标题'));
					} else {
						var arr=[];
						for(var i=0;i<value.length;i++){
							if(value[i].type=='input' && !value[i].con){
								return callback(new Error('固定内容不能为空'));
							}
							if(value[i].type!='-' && value[i].type!='_' && !(value[i].type=='input' && !value[i].con)){
								arr.push(value[i])
							}
						}
						if(arr.length<1){
							return callback(new Error('页面标题要包含key/固定内容/dynamic其中一项'));
						}
						callback();
					}
			};
			var template_check = (rule, value, callback) => {
					if (!value.length) {
						return callback(new Error('请至少选择1个页面模板'));
					} else {
						callback();
					}
			};
			var folder_check = (rule, value, callback) => {
					if (!value) {
						return callback(new Error('请输入生成文件夹名称'));
					} else {
						const  reg = /^[a-z0-9]{3,20}$/
						if (reg.test(value)) {
							if(this.one.is_wheel){
								if(this.one.wheel_chain.length<1){
									return callback(new Error('请选择轮链目录'));
								}
							}else{
								return callback();
							}
							return callback();
						} else {
							return callback(new Error('请输入3-20位字母+数字生成文件夹'));
						}
						
					}
			};
			var key_check = (rule, value, callback) => {
					if (!value) {
						return callback(new Error('请输入页面关键词'));
					} else {
						var arr=value.split("\n");
						if(arr.length<1 || arr.length>10000){
							return callback(new Error('最多只能1万个关键词'));
						}
						var newkey=[];
						for(var i=0;i<arr.length;i++){
							if(comm.sTrim(arr[i])){
								newkey.push(arr[i])
							}
						}
						var str="";
						for(var x=0;x<newkey.length;x++){
							if(x>0){
								str+="\n";
							}
							str+=comm.sTrim(comm.middleTrim(newkey[x]));
						}
						this.one.key=str;
						callback();
					}
			};
			var ground_page_check = (rule, value, callback) => {
					if (!value.length) {
						return callback(new Error('请输入落地页JS全称'));
					} else {
						//const  reg = /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/
						const  reg = /^[a-zA-Z0-9/]{1,100}(\.js)+$/
						//console.log(reg.test(value))
						if (reg.test(value)) {
							callback();
						} else {
							return callback(new Error('请输入正确落地页JS全称格式'));
						}
					}
			};
			return{
				menuActive:"101",
				menuAOpeneds:[],
				issub:true,
				nowtimes:"23",
				one:{
					sitename:'',
					title:[],
					key:"",
					folder:'',
					is_wheel:false,
					wheel_chain:[],
					description_type:'content',//页面描述类型content为自动获取内容20-30个文本；key则需要自动插入关键字，需要录入数量
					descriptionKey_num:1,//如果描述类型为key，则需要录入这个数量。1-
					template:[],
					filename_rules:"",
					ground_page:'',
					contentTypes:[]//内容类型
				},
				onekeyvalue:"",
				onekeyinputIsshow:false,
				floders:[],
				templates:[],
				types:[],//内容类型
				ruleOne:{
					sitename:[
						{ required: true, message: '请输入网站名称', trigger: 'blur',min:1}
					],
					title:[
						{ required: true, message: '请选择/输入页面标题', trigger: 'blur|change' },
						{validator: title_check, trigger: 'blur'}
					],
					key:[
						{ required: true,message: '请输入页面关键词', trigger: 'blur' },
						{validator: key_check, trigger: 'blur'}
					],
					folder:[
						{ required: true,message: '请输入生成文件夹名称', trigger: 'blur' },
						{validator: folder_check, trigger: 'blur'}
					],
					filename_rules:[
						{ required: true,min:6,max:30, message: '请输入文件名规则', trigger: 'blur|change' },
						{validator: filename_check, trigger: 'blur|change'}
					],
					template:[
						{ required: true,type:"array", message: '请至少选择1个页面模板', trigger: 'blur' },
						{validator: template_check, trigger: 'blur'}
					],
					ground_page:[
						{ required: true,message: '请输入落地页JS全称', trigger: 'blur' },
						{validator: ground_page_check, trigger: 'blur'}
					]
					/**/
				}
			}
			
		},
		created: function() {
			var that=this;
			this.init();
		},
	    methods: {
			init:function(){
				this.getalltemplates();
			},
			sub:function(formName){
				this.$refs[formName].validate((valid) => {
				  if (valid) {
					  this.issub=false;
					  var query=this.one;
						$.ajax({
								  url: "/member/createtask",
								  type: "post",
								  dataType: "json",
								  contentType: "application/json",
								  data:JSON.stringify(query),
								  success: function(data) {
									this.$message.success("任务创建成功");
									setTimeout(function () {
										window.location.href="/member/htmlprogress";
									}, 1000)
							   }.bind(this),
							   error:function(err){
								   this.issub=true;
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