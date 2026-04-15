<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
	"/"=> ['index/Index/index',['method' => 'get']],
	"index"=> ['index/Index/index',['method' => 'get']],
	"okadmin"=> ['admin/Admin/index',['method' => 'get']],
	
	
	"okadmin/roles"=> ['admin/Sysset/roles',['method' => 'get']],
	"okadmin/api/roles"=> ['admin/Api/roles',['method' => 'post|get']],
	"okadmin/api/roles/:id"=> ['admin/Api/roles',['method' => 'patch|delete']],
	"okadmin/api/allPermissions"=> ['admin/Api/allPermissions',['method' => 'get']],
	
	//管理员管理
	"okadmin/admins"=> ['admin/admin/logins',['method' => 'get']],
	"okadmin/api/admins"=> ['admin/Api/admins',['method' => 'post|get']],
	"okadmin/api/admins/:id"=> ['admin/Api/admins',['method' => 'patch|delete']],
	"okadmin/api/admins/:id/repass"=> ['admin/Api/adminrepass',['method' => 'patch']],
	"okadmin/api/admins/:id/state"=> ['admin/Api/adminstate',['method' => 'patch']],
	
	//地区管理
	"okadmin/regions"=> ['admin/Html/regions',['method' => 'get']],
	"okadmin/api/regions"=> ['admin/Api/regions',['method' => 'post|get']],
	"okadmin/api/regions/:id"=> ['admin/Api/regions',['method' => 'patch|delete']],
	
	//系统设置
	"okadmin/syssetting"=> ['admin/Admin/syssetting',['method' => 'get']],
	"okadmin/sysinfo"=> ['admin/Admin/sysinfo',['method' => 'get']],
	"okadmin/wxcodeup"=> ['admin/Admin/wxcodeup',['method' => 'post']],
	"okadmin/systemup"=> ['admin/Admin/systemup',['method' => 'put']],
	
	
	//暂弃
	
	"okadmin/types"=> ['admin/Admin/types',['method' => 'get']],
	"okadmin/typelist"=> ['admin/Admin/typelist',['method' => 'get']],
	"okadmin/type"=> ['admin/Admin/type',['method' => 'post']],
	"okadmin/type/:id"=> ['admin/Admin/type',['method' => 'put|delete']],
	"okadmin/contents"=> ['admin/Admin/contents',['method' => 'get']],
	"okadmin/contentslist"=> ['admin/Admin/contentslist',['method' => 'get']],
	"okadmin/content"=> ['admin/Admin/content',['method' => 'post']],
	"okadmin/content/:id"=> ['admin/Admin/content',['method' => 'put|delete']],
	"okadmin/contentadds"=> ['admin/Admin/contentadds',['method' => 'post|get']],
	
	
	
	"okadmin/templates"=> ['admin/Admin/templates',['method' => 'get|post']],
	"okadmin/template/:id"=> ['admin/Admin/template',['method' => 'delete']],
	"okadmin/templateslist"=> ['admin/Admin/templateslist',['method' => 'get']],
	"okadmin/templateupload"=> ['admin/Admin/templateupload',['method' => 'post']],
	
	"okadmin/login"=> ['admin/admin',['method' => 'get']],
	"okadmin/adminlogin"=> ['admin/admin/loginlogin',['method' => 'post']],
	"okadmin/loginout"=> ['admin/adminout',['method' => 'get']],
	"okadmin/uppass"=> ['admin/Admin/uppass',['method' => 'post']],
	"okadmin/upuserxx"=> ['admin/Admin/upuserxx',['method' => 'post']],
	
	"okadmin/getoneuser"=> ['admin/Admin/getoneuser',['method' => 'get']],
	"okadmin/delUser"=> ['admin/Admin/delUser',['method' => 'delete']],
	"okadmin/upStateUser"=> ['admin/Admin/upStateUser',['method' => 'put']],
	"okadmin/searchUsers"=> ['admin/Admin/searchUsers',['method' => 'get']],
	"okadmin/adduser"=> ['admin/Admin/adduser',['method' => 'post']],
	"okadmin/upuserpass"=> ['admin/Admin/upuserpass',['method' => 'post']],
	"okadmin/system"=> ['admin/Admin/system',['method' => 'get']],
	"okadmin/systemup"=> ['admin/Admin/systemup',['method' => 'put']],
	
	"hanzi"=> ['index/Index/hanzi',['method' => 'get']],
	"showhanzi"=> ['index/Index/showhanzi',['method' => 'get']],
	//"server"=> ['server/Worker/onWorkerStart',['method' => 'get']],
	
	"member"=> ['member/Member/index',['method' => 'get']],
	"member/index"=> ['member/Member/index',['method' => 'get']],
	"member/login"=> ['member/Member/login',['method' => 'get']],
	"member/logingo"=> ['member/Member/logingo',['method' => 'post']],
	"member/loginout"=> ['member/Member/loginout',['method' => 'get']],
	"member/reg"=> ['member/Member/reg',['method' => 'get']],
	"member/reggo"=> ['member/Member/reggo',['method' => 'post']],
	"member/ckmail"=> ['member/Member/ckmail',['method' => 'post']],
	"member/uppassto"=> ['member/Member/uppassto',['method' => 'post']],
	"member/uppass"=> ['member/Member/uppass',['method' => 'get']],
	
	"member/accountcenter"=> ['member/Member/accountcenter',['method' => 'get']],
	"member/htmlcreation"=> ['member/Html/htmlcreation',['method' => 'get|post']],
	"member/htmlprogress"=> ['member/Html/htmlprogress',['method' => 'get']],
	"member/createtask"=> ['member/Html/createtask',['method' => 'post']],
	"member/templates"=> ['member/Html/templates',['method' => 'get']],
	"member/tp"=> ['member/Html/tp',['method' => 'get']],
];
