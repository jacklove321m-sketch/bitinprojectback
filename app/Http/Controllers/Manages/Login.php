<?php
namespace App\Http\Controllers\Manages;


use App\Admin;
use App\AdminRole;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Earnp\GoogleAuthenticator\GoogleAuthenticator;

class Login extends Controller
{
    public function login()
    {

        $username = Input::get('username', '');
        $password = Input::get('password', '');
        $authcode = Input::get('authcode', '');
        $password2 = $password;
        if (empty($username)) {
            return ['code'=>1,'msg'=>'用户名必须填写'];
        }
        if (empty($password)) {
            return ['code'=>1,'msg'=>'密码必须填写'];
        }
        
         
        $password = Users::MakePassword($password);
        $admin = Admin::where('username', $username)->first();
        
        
        if (empty($admin)) {
            return ['code'=>1,'msg'=>'用户名密码错误'];
        } else {
            if ($password != $admin->password) {
                return ['code'=>1,'msg'=>'用户名密码错误'];
            }
            if($admin->google){
                if(empty($authcode)){
                    return ['code'=>1,'msg'=>'谷歌验证码必须填写'];
                }else if(strlen($authcode)!=6){
                    return ['code'=>1,'msg'=>'谷歌验证码错误'];
                }else if(!GoogleAuthenticator::CheckCode($admin->google,$authcode)){
                    return ['code'=>1,'msg'=>'谷歌验证码错误'];
                }
            }
            $role = AdminRole::find($admin->role_id);
            if (empty($role)) {
                return ['code'=>1,'msg'=>'账号异常'];
            } else {
               
                session()->put('admin_username', $admin->username);
                session()->put('admin_id', $admin->id);
                session()->put('admin_role_id', $admin->role_id);
                session()->put('admin_is_super', $role->is_super);
                $admin -> session_id = session()->getId();
                $admin -> save();
                return ['code'=>0,'msg'=>'登陆成功'];
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        session()->put('admin_username', '');
        session()->put('admin_id', '');
        session()->put('admin_role_id','');
        session()->put('admin_is_super', '');
        return ['code'=>0,'msg'=>'退出成功'];
    }
}