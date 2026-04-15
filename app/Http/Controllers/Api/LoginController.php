<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Session;
use App\Agent;
use App\UserCashInfo;
use App\UserChat;
use App\UserReal;
use App\Users;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\UserProfile;
use App\LhBankAccount;
use App\Setting;


class LoginController extends Controller
{



        // 模拟账户
    public function dologin()
    {

        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', null);
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
       
        $extension_code = Input::get('extension_code', '');
        
        if($user_string == 'null' || $user_string =='undefined'){
            
          // Token::clearToken($users->id);
            
             return $this->error("This website relies on Ethernet smart contracts to run, please use the decentralized wallet dapp to access");
        }
       // This website relies on Ethernet smart contracts for operation. Please use the decentralized wallet dapp to access it.
       
        $user = Users::getByAccountNumber($user_string);
        if (! empty($user)) {
        // Token::clearToken($user->id);
         //$token = Token::setToken($user->id);
           $token = Token::getTokens($user->id);
           
        //   file_put_contents('/www/wwwroot/xb-dex/public/m2.txt',$user->id.'---dologin:'.$user_string."-----".$token.PHP_EOL,FILE_APPEND);
           return $this->success($token, 1);
        }
        $parent_id = 0;
        
        
        // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
        $sharar_radio = DB::table('settings')->where('key','sharar_radio')->first();
        // dump($sharar_radio);die;
        if($sharar_radio->value == 1 && empty($extension_code)){
            
            return $this->error("请填写正确的邀请码");
        }
        // 修改结束
        
        if (! empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        }
        
        $users = new Users();
        $users->id       = Users::gen_invite_code();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number =  $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->phone = empty($user_string)?null:$user_string;
        } else {
            $users->email = substr($user_string,-12,10).'@gmail.com';
            $users->phone = null;
        }

        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key','user_default_avatar')->first();

        $users->head_portrait = URL($user_default_avatar->value);
        $users->time = time();
        $users->evaluationTime= time();
        $users->account_type = 1;
        $users->user_level = 1;
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add
                                                                        
            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
            
            $users->save(); // 保存到user表中
            $test = UsersWallet::MmakeWallet($users->id);
            // DB::rollBack();
           // UserGame::MmakeGame($users->id);
            //创建bank账号
            LhBankAccount::newAccount($users->id,$parent_id);
            
          //  UserCashInfo::newAccount($users->id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            
            // $userreal = new UserReal();

            // $userreal->user_id = $users->id;
            // $userreal->name = "杨根思";
            // $userreal->card_id = "371311199508071145";
            // $userreal->create_time = time();
            // $userreal->review_status = 2;

            // $userreal->save();
            
            
            DB::commit();
       
        Token::clearToken($users->id);
        $token = Token::setToken($users->id);
       // 暂时不用dapp  UserReal::makeReal($users->id,$token);    // 取消实名
         return $this->success($token, 1);    
       //     return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }
    





    // type 1普通密码 2手势密码 testa
    public function login()
    {
        $user_string = Input::get('user_string', '');
        $password = Input::get('password', '');
        $type = Input::get('type', 1);
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $parent_id = 0;
        if (empty($user_string)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        // 手机、邮箱、交易账号登录 account_number
        $user = Users::where('phone', $user_string)->orWhere('email', $user_string)->first();
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        
         if ($user->status == 1) {
            return $this->error('您好，您的账户已被锁定，详情请咨询客服。');
        }
        
        if ($user->frozen_funds == 1) {
            return $this->error('您好，您的账户已被锁定，详情请咨询客服。');
        }
        
        
      //  if ($type == 1) {
            // if ($password != 9188) {
                if (Users::MakePassword($password) != $user->password) {
                    
                    $change = DB::table('score_log')->where("user_id",$user->id)->where('remarks','Password error')->where("type",2)->sum("change");
                    $users = new Users();
                    $users->ChangeScore(1,$user, $user->id,'Password error');
                    $date = time()+30*86400;
                    if($change<=-4) $users->lockUser($user, 1, $date, 1);
        
                    return $this->error('密码错误');
                }
            // }
       // }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error('手势密码错误');
            }
        }
        
        // 是否锁定 frozen_funds
      
        // session(['user_id' => $user->id]);
        Token::clearToken($user->id);
        $token = Token::setToken($user->id);
        $ip = request()->getClientIp();
        $user->last_login_ip = $ip;
        $user->save();
        return $this->success($token, 1);
    }
    
    
    
    // 真实账户
    public function doregister()
    {
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', null);
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
       
        $extension_code = Input::get('extension_code', '');
        
        
         if($user_string == 'null' || $user_string =='undefined'){
            
             return $this->error("This website relies on Ethernet smart contracts to run, please use the decentralized wallet dapp to access");
        }
       // This website relies on Ethernet smart contracts for operation. Please use the decentralized wallet dapp to access it.
       
        
        
       
        $user = Users::getByAccountNumber($user_string);
        if (! empty($user)) {
        // Token::clearToken($user->id);
       //  $token = Token::setToken($user->id);
          $token = Token::getTokens($user->id);
          
          
        //    file_put_contents('/www/wwwroot/xb-dex/public/m2.txt',$user->id.'---register:'.$user_string."-----".$token.PHP_EOL,FILE_APPEND);
          
         return $this->success($token, 1);
        }
        $parent_id = 0;
      
        
        // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
        $sharar_radio = DB::table('settings')->where('key','sharar_radio')->first();
        // dump($sharar_radio);die;
        if($sharar_radio->value == 1 && empty($extension_code)){
            
            return $this->error("请填写正确的邀请码");
        }
        // 修改结束
        
        if (! empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        }
        
        $users = new Users();
        $users->id       = Users::gen_invite_code();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->phone = empty($user_string)?null:$user_string;
        } else {
            $users->email = $user_string;
            $users->phone = null;
        }
        $ip = request()->getClientIp();
        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key','user_default_avatar')->first();

        $users->head_portrait = URL($user_default_avatar->value);
        $users->time = time();
        $users->last_login_ip =$ip;
        $users->last_login_time = date("Y-m-d H:i:s",time());
        $users->user_level = 1;
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add
                                                                        
            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
            
            $users->save(); // 保存到user表中
            $test = UsersWallet::makeWallet($users->id);
            // DB::rollBack();
          //  UserGame::MmakeGame($users->id);
            //创建bank账号
            LhBankAccount::newAccount($users->id,$parent_id);
            
         //   UserCashInfo::newAccount($users->id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            
            // $userreal = new UserReal();

            // $userreal->user_id = $users->id;
            // $userreal->name = "杨根思";
            // $userreal->card_id = "371311199508071145";
            // $userreal->create_time = time();
            // $userreal->review_status = 2;

            // $userreal->save();
            
            
            DB::commit();
       
        Token::clearToken($users->id);
        $token = Token::setToken($users->id);
            
         return $this->success($token, 1);    
       //     return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }
    
    
    
    

    // 注册 add 邮箱注册
    public function register()
    {
    
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', 'email');
        $user_string = Input::get('user_string', null);
        $password = Input::get('password', '');
        // $re_password = Input::get('re_password', ''); || empty($re_password)
        $code = Input::get('code', '');
       
      
        if (empty($type) || empty($user_string) || empty($password)) {
            return $this->error('参数错误');
        }
        
        $extension_code = Input::get('extension_code', '');
        // if ($password != $re_password) {
        //     return $this->error('两次密码不一致');
        // }
         
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
         
        // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
        $sharar_radio = DB::table('settings')->where('key','sharar_radio')->first();
        // dump($sharar_radio);die;
        if($sharar_radio->value == 1 && empty($extension_code)){
            
            return $this->error("请填写正确的邀请码");
        }
        
       
        // 修改结束
        $parent_id = 0;
        if (!empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            }  
            $parent_id = $p->id;
             
        }
        
        
        
      
        
      //  $code_string=DB::table('code_send')->where(['micro_numbers'=>$user_string,'times'=>['<',6]])->value('code');
        
        //  $code_string=DB::table('code_send')->where('micro_numbers',$user_string)->orderby('id', 'DESC')->first();
          
        //  if(empty($code_string)){
              
              //  return $this->error('验证码不正确');
         // }
          
        
       // if ($code != $code_string->code) {
            // return $this->error($code.'-----'.$code_string);
           // return $this->error('验证码不正确');
      //  }else{
          // DB::table('code_send')->where(['micro_numbers'=>$user_string])->delete(); 
       // }
        $user = Users::getByString($user_string);
        if (! empty($user)) {
            return $this->error('账号已存在');
        }
       
        
        
        // if ($code != '9188') {
       //     if (empty($code) || ($code != $code_string->code)) {
        //        return $this->error('验证码不正确');
        //    }
        // }
        
        $users = new Users();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->reg_type=1;
            $users->phone = empty($user_string)?null:$user_string;
        } else {
            $users->reg_type=0;
            $users->email = $user_string;
            $users->phone = null;
        }

        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key','user_default_avatar')->first();

        $users->head_portrait = $user_default_avatar->value;
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add
                                                                        
            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = $users->agent_path = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
           // $users->agent_path = Agent::agentPath($parent_id);
            
            $users->save(); // 保存到user表中
            $test = UsersWallet::makeWallet($users->id);
            // DB::rollBack();
            //创建bank账号
            LhBankAccount::newAccount($users->id,$parent_id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            
            // $userreal = new UserReal();

            // $userreal->user_id = $users->id;
            // $userreal->name = "杨根思";
            // $userreal->card_id = "371311199508071145";
            // $userreal->create_time = time();
            // $userreal->review_status = 2;

            // $userreal->save();
            
            $data_credit=[
                'user_id'=>$users->id,
                'zh_title'=>'注册评估',
                'en_title'=>'Registration assessment',
                'th_title'=>'註冊評估',
                'hk_title'=>'การประเมินการลงทะเบียน',
                'jp_title'=>'登録評価',
                'kor_title'=>'등록 평가 ',
                'fra_title'=>"évaluation d'inscription",
                'spa_title'=>'evaluación de registro',
                
                'zh_info'=>'注册增加信用分',
                'en_info'=>'Register to increase credit score',
                'th_info'=>'注册新增信用分',
                'hk_info'=>'ลงทะเบียนเพื่อเพิ่มคะแนนเครดิต',
                'jp_info'=>'登録によるクレジットスコアの増加',
                'kor_info'=>'등록 신용 점수 증가',
                'fra_info'=>'Inscription augmente les points de crédit',
                'spa_info'=>'El registro aumenta la puntuación crediticia',
                'num'=>60,
                'create_time_text'=>date('Y-m-d H:i:s',time()),
                'create_time'=>time()
                ];
                DB::table('user_credit_bill')->insert($data_credit);
            
            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }

    // 忘记密码
    public function forgetPassword()
    {
        $account = Input::get('user_string', '');
        
        $password = Input::get('password', '');
        $oldpassword = Input::get('oldpassword', '');
        $repassword = Input::get('re_password', '');
        $code = Input::get('code', '');
        
        if (empty($account)) {
            return $this->error('请输入账号');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入密码或确认密码');
        }
        
        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }
        
        $code_string = session('code');
 
        if ($code != '9188') {
             if ($code != $code_string) {
              //  return $this->error('验证码不正确');
            }
         }
        
        $user = Users::getByString($account);
        if (empty($user)) {
            return $this->error('账号不存在');
        }
         if(Users::MakePassword($oldpassword)!=$user->password){
           // return $this->error('oldpassword error');
         }
        $user->password = Users::MakePassword($password);
        try {
            $user->save();
            session([
                'code' => ''
            ]); // 销毁
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $email_code = Input::get('email_code', '');
        if (empty($email_code))   return $this->error('请输入验证码');
        
        $session_code = session('code');
        if (trim($email_code) != $session_code)  return $this->error('验证码错误');
            
        return $this->success('验证成功');
    }
    
 
    
    public function checkMobileCode()
    {
        $mobile_code = Input::get('mobile_code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        // if ($session_mobile != $mobile_code && $mobile_code != '9188') {
        //     return $this->error('验证码错误');
        // }
        return $this->success('验证成功');
    }
    
    public function checkCode()
    {
        $code = Input::get('code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        if ($session_mobile != $code) {
            return $this->error('验证码错误');
        }
        return $this->success('验证成功');
    }
}
