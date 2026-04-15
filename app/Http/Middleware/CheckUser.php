<?php

namespace App\Http\Middleware;

use App\Users;
use App\Setting;
use Closure;
use App\UserReal;
class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      
       $value =  Setting::getValueByKey('realname');
       
      
        $user_id = Users::getUserId();
        $user_real = UserReal::where('user_id',$user_id)->first();
        $users=Users::where('id',$user_id)->first();
        if(empty($user_real) && $value){
            $message='请实名认证';
            $message=str_replace('massage.', '', __("massage.$message"));
            return response()->json(['type' => '998', 'message' =>$message]);
        }
        if(!empty($user_real)){
        
          if ($user_real->review_status != 2 && $value){

            return response()->json(['type' => 'error', 'message' => '您的实名认证还未通过！']);
          }
        } 
        if($users->frozen_funds == 1){
            $message='您好，您的资金已被锁定，详情请咨询客服。';
            $message=str_replace('massage.', '', __("massage.$message"));
            return response()->json(['type' => 'error', 'message' => $message]);
        }
      
        return $next($request);
    }
}
