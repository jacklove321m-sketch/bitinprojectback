<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use App\Users;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AccountLog extends Model
{
    protected $table = 'account_log';
    public $timestamps = false;
    const CREATED_AT = 'created_time';
    protected $appends = [
        'account_number',
        'account',
        'currency_name',//币种
        'before',//交易前
        'after',//交易后
        'transaction_info',//交易信息
        'type_info_m',
        'type_info_e',
        'type_info_j',
        'from_user_id'
    ];



    const ADMIN_LEGAL_BALANCE = 1;//后台调节资金账户余额
    const ADMIN_LOCK_LEGAL_BALANCE = 2;//后台调节资金账户锁定余额
    const ADMIN_CHANGE_BALANCE = 3;//后台调节币币账户余额
    const ADMIN_LOCK_CHANGE_BALANCE = 4;//后台调节币币账户锁定余额
    const ADMIN_LEVER_BALANCE = 5;//后台调节合约账户余额
    const ADMIN_LOCK_LEVER_BALANCE = 6;//后台调节合约账户锁定余额
    const ADMIN_MICRO_BALANCE = 7; //后台调节期权账户余额
    const ADMIN_LOCK_MICRO_BALANCE = 8; //后台调节期权账户锁定余额

    const WALLET_CURRENCY_OUT = 7;//提币记录
    const WALLET_CURRENCY_IN = 8;//充币记录

    const WALLET_LEGAL_OUT = 9;
    const WALLET_LEGAL_IN = 10;
    const WALLET_CHANGE_IN = 11;//资金划入记录
    const WALLET_CHANGE_OUT = 12;//资金划出记录
    const WALLET_CHANGE_LEVEL_OUT = 13;//资金划出记录
    const WALLET_CHANGE_LEVEL_IN = 14;//资金划出记录
    const WALLET_LEVER_IN = 15;
    const WALLET_LEVER_OUT = 16;
    const WALLET_MCIRO_IN = 15;
    const WALLET_MCIRO_OUT = 16;


    const INVITATION_TO_RETURN = 33;//邀请返佣

    const LEGAL_DEAL_SEND_SELL = 60;//商家发布资金出售
    const LEGAL_DEAL_USER_SELL = 61;//出售给商家资金
    const LEGAL_USER_BUY = 62;//用户购买商家资金成功
    const LEGAL_SELLER_BUY = 63;//商家购买用户资金成功
    const LEGAL_DEAL_USER_SELL_CANCEL = 64;//出售给商家资金-取消
    const INTO_TRA_FB = 65;//美丽链资金转入(imc)
    const INTO_TRA_BB = 66;//美丽链币币转入(imc)
    const INTO_TRA_GG = 67;//美丽链合约转入(imc)
    const ADMIN_SELLER_BALANCE = 70;//后台调节商家余额
    const LEGAL_DEAL_BACK_SEND_SELL = 71;//商家撤回发布资金出售
    const LEGAL_DEAL_ERROR_SEND_SELL = 72;//商家撤回发布资金出售
    const LEGAL_DEAL_AUTO_CANCEL = 68;//自动取消资金交易

    /* const BUY_BLOCK_CHAIN = 1;//购买区块链
     const ADJUDT_SUB_BALANCE = 2;//后台调节账户余额
     const TRANSFER_IN = 3;//转入
     const TRANSFER_OUT = 4;//转出
     const ETH_EXCHANGE = 5;//以太币兑换

     const USER_BONUS = 6;//日均收益
     const ECOLOGY_BONUS = 7;//更新生态推广奖励
     const AGENT_REWARD = 8;//代理商管理奖励

     const ADMIN_LOCK_BALANCE = 9; //后台调节锁定余额
     const ADMIN_REMAIN_LOCK_BALANCE = 10; //后台调节锁定余额相应剩余锁定余额

     const LOCK_BALANCE = 11; //锁仓增加
     const LOCK_REMAIN_BALANCE = 12; //锁仓减少
     const ACCEPTOR_SELL = 13; //用户提现承兑申请
     const ACCEPTOR_RECHARGE = 14; //用户充值承兑确认
     const ACCEPTOR_RECHARGE_VAR = 15; //用户充值承兑手续费
     const ACCEPTOR_RECHARGE_DEC = 16; //确认用户充值，承兑商充值额度减少
     const ACCEPTOR_CASH_INC = 17; //确认用户充值，承兑商提现额度增加
     const ACCEPTOR_CASH_DEC = 18; //确认用户提现，承兑商提现额度减少
     const ACCEPTOR_RECHARGE_INC = 19; //确认用户提现，承兑商充值额度增加
     const ACCEPTOR_SELL_RETURN = 20; //取消用户提现承兑申请
     const ACCEPTOR_CASH_RETURN = 91; //取消用户提现承兑,承兑商提现额度增加

     */
    const TRANSACTIONOUT_SUBMIT_REDUCE = 21; //提交卖出，扣除

    const TRANSACTIONIN_REDUCE = 22; //买入扣除
    const TRANSACTIONIN_SELLER = 23; //扣除卖方
    const TRANSACTIONIN_SUBMIT_REDUCE = 24; //提交买入，扣除

    const TRANSACTIONIN_REDUCE_ADD = 25; //买方增加币
    const TRANSACTIONIN_SELLER_ADD = 26; //卖方增加cny

    const TRANSACTIONIN_REVOKE_ADD = 27; //撤销增加
    const TRANSACTIONOUT_REVOKE_ADD = 28; //撤销增加

    const TRANSACTION_FEE = 29; //卖出手续费

    const LEVER_TRANSACTION = 30; //合约交易扣除保证金
    const LEVER_TRANSACTION_ADD = 31; //平仓增加
    const LEVER_TRANSACTION_FROZEN = 32; //爆仓冻结
    const LEVER_TRANSACTION_OVERNIGHT = 34; //隔夜费
    const LEVER_TRANSACTION_FEE = 35; //交易手续费
    const LEVER_TRANSACTIO_CANCEL = 36; //合约交易取消
    const CANDY_LEVER_BALANCE = 37; //通证兑换合约币增加
    const TOBE_SELLER_SUB_USDT = 38; //申请成为商家扣除USDT
    const CURRENCY_TO_USDT_MUL = 39; //资产兑换 减少兑换币
    const CURRENCY_TO_USDT_ADD = 40; //资产兑换 增加USDT资金
    const JOIN_BOSS = 41;
    const TRANSFER_TO_LH_ACCOUNT = 42; //转账入余币宝
    const LOCK_POSITION = 1004;
    const SEND_CURRENCY = 2022; //申购新币
    const SEND_CURRENCY_IN = 2023; //申购新币收益
    const BOSS_WITHDRAW = 43;
    const BANK_WITHDRAW = 44;
    const LH_LOAN = 45;
    const COIN_TRADE_FROZEN = 50;
    const COIN_TRADE = 51;
    const WALLETOUT = 99; //用户申请提币
    const WALLETOUTDONE = 100; //用户提币成功
    const WALLETOUTBACK = 101; //用户提币失败
    const TRANSACTIONIN_IN_DEL = 102;//取消买入交易
    const TRANSACTIONIN_OUT_DEL = 103;//取消买出交易

    const CHANGE_LEVER_BALANCE = 104;//合约交易账户变化

    const REWARD_CANDY = 105; //奖励通证
    const REWARD_CURRENCY = 106; //奖励数字货币

    const CANDY_TOUSDT_CANDY = 107; //通证兑换USDT
    const ADMIN_CANDY_BALANCE = 108; //后台调节通证

    const SELLER_BACK_SEND = 299;//合约交易账户变化
    const CHANGEBALANCE = 401; //转账
    const LTC_IN = 301; //来自矿机的转账
    const LTC_SEND = 302; //转账余额至矿机

    const ETH_EXCHANGE = 200; //充币增加余额
    const CHAIN_RECHARGE = 200;

    //c2c交易
    const C2C_DEAL_SEND_SELL = 201;//用户发布资金出售
    const C2C_DEAL_AUTO_CANCEL = 202;//自动取消c2c资金交易
    const C2C_DEAL_USER_SELL = 203;//出售给用户资金
    const C2C_USER_BUY = 204;//用户购买资金成功
    const C2C_DEAL_BACK_SEND_SELL = 205;//商家撤回发布资金出售

    const WALLET_LEGAL_LEVEL_OUT = 206;//资金(c2c)转入合约
    const WALLET_LEGAL_LEVEL_IN = 207;//资金(c2c)转入合约
    const WALLET_LEVEL_LEGAL_OUT = 208;//合约转入资金(c2c)
    const WALLET_LEVEL_LEGAL_IN = 209;//合约转入资金(c2c)
    const WALLET_DONGJIEGANGGAN = 210;
    const WALLET_JIEDONGGANGGAN = 211;//审核不通过解冻合约冻结

    const PROFIT_LOSS_RELEASE = 212;//历史盈亏释放,增加合约币

    const MICRO_TRADE_SUBMIT = 501; //期权下单
    const MICRO_TRADE_CLOSE_SETTLE = 502; //期权平仓结算
    const DEDUCT_PRINCIPAL_PLACING = 1003; //期权平仓结算
    const DEBIT_BALANCE_MINUS=600 ;//闪兑减少余额
    const DEBIT_BALANCE_ADD=601 ;//闪兑通过,增加余额
    const DEBIT_BALANCE_MINUS_LOCK=602 ;//闪兑减少锁定余额
    const DEBIT_BALANCE_ADD_LOCK=603 ;//闪兑增加锁定余额
    const DEBIT_BALANCE_ADD_REJECT=604 ;//闪兑驳回，增加余额


//    const WALLET_MICRO_LEVEL_OUT = 206;//期权(c2c)转入合约
//    const WALLET_MICRO_LEVEL_IN = 207;//期权(c2c)转入合约
//    const WALLET_LEVEL_MICRO_OUT = 208;//合约转入期权(c2c)
//    const WALLET_LEVEL_MICRO_IN = 209;//合约转入期权(c2c)
//
//    const WALLET_LEGAL_MICRO_OUT = 206;//资金(c2c)转入期权
//    const WALLET_LEGAL_MICRO_IN = 207;//资金(c2c)转入期权
//    const WALLET_MICRO_LEGAL_OUT = 208;//期权转入资金(c2c)
//    const WALLET_MICRO_LEGAL_IN = 209;//期权转入资金(c2c)
//
//    const WALLET_CNANGE_LEVEL_OUT = 206;//闪兑转入合约
//    const WALLET_CNANGE_LEVEL_IN = 207;//闪兑转入合约
//    const WALLET_LEVEL_CNANGE_OUT = 208;//合约转入闪兑
//    const WALLET_LEVEL_CNANGE_IN = 209;//合约转入闪兑

    const WALLET_USDT_MINUS=220; //usdt兑换bmb 减少usdt
    const WALLET_USDT_BMB_FEE=221;//usdt兑换手续费
    const WALLET_BMB_ADD=221;//usdt兑换BMB手续费

    const USER_BUY_INSURANCE = 230;//用户购买保险
    const USER_CLAIM_COMPENSATION = 231;//赔偿用户
    const USER_CLAIM_CLEAR = 232;//赔偿用户,清除受保金额
    const INSURANCE_RESCISSION1 = 233;//保险解约，清除受保金额
    const INSURANCE_RESCISSION2 = 234;//保险解约，清除保险金额
    const INSURANCE_RESCISSION_ADD = 234;//保险解约，赔付用户

    const RETURN_INSURANCE_TRADE_FEE = 235;//释放保险交易手续费


    const  LOWER_REBATE = 250;//下级返利
    const  INSURANCE_MONEY=251;//持币生币
    
    const  IEO_OPERATION = 700;

    //新加部分log
    const  USER_EXCHANGE_ADD = 701; //币币划转增加
    const  USER_EXCHANGE_DOWN = 702;//币币划转减少
    const  USER_EXCHANGE_FEE = 703;//币币划转手续费
    const MINING_BUY = 704; //锁仓挖矿
    const MINING_RETURN = 705; //挖矿收益
    const MICRO_SECONDS_TIPS = 706; //期权下单提示

    //跟单
    const LEVER_TRANSACTION_DEDUCT_CAUTION = 707;   //合约交易-扣除保证金
    const LEVER_TRANSACTION_TRADE_FEE = 708;   //合约交易-扣除手续费


     	// 借贷
   const USER_LOAN_ORDER_BUY = 709;
   const USER_LOAN_ORDER_RETURN = 710;
    //新加部分
    const USER_DUAL_ORDER_BUY = 1000;
    const USER_DUAL_ORDER_RETURN = 1001;
    const USER_BUY_NFT_TYPE_1 = 1002;//一口价购买nft
    const USER_BUY_MARGIN_NFT = 1003;//竞拍保证金
    const USER_RETURN_MARGIN_NFT = 1004; //退还保证金
    const USER_ORDER_PAY_NFT = 1005;
    const USER_AI_ORDER_BUY = 1010;  
    const USER_AI_ORDER_RETURN = 1020;  
    
    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }
    
    public function getFromUserIdAttribute()
    {
        //return $this->hasOne('App\WalletLog', 'account_log_id', 'id')->value('from_user_id');
        return $this->walletLog()->value('from_user_id');
    }
    
    public function user1()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }

    public function getAccountAttribute()
    {
        $value = $this->hasOne('App\Users', 'id', 'user_id')->value('phone');
        if (empty($value)) {
            $value = $this->hasOne('App\Users', 'id', 'user_id')->value('email');
        }
        return $value;
    }

    public function getCreatedTimeAttribute()
    {
        $value = $this->attributes['created_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getBeforeAttribute()
    {
        return $this->walletLog()->value('before');
    }
    public function getAfterAttribute()
    {
        return $this->walletLog()->value('after');
    }

    public function getTransactionInfoAttribute()
    {
        $type1 = [
            '0' => '无',
            '1' => '资金',
            '2' => '币币',
            '3' => '合约',
            '4' => '期权',
            '5'=>'保险'
        ];
        $type2 = ['', '[锁定]'];
        $balance_type = $this->walletLog()->value('balance_type');
        $lock_tpye = $this->walletLog()->value('lock_type');
        array_key_exists($balance_type, $type1) ? : $balance_type = 0;
        array_key_exists($lock_tpye, $type2) ? : $lock_tpye = 0;
        return $type1[$balance_type] . $type2[$lock_tpye];

    }

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('name');
    }

    public static function insertLog($data = array(), $data2 = array())
    {
        $data = is_array($data) ? $data : func_get_args();
        $log = new self();
        $log->user_id = $data['user_id'] ?? false;;
        $log->value = $data['value'] ?? '';
        $log->created_time = $data['created_time'] ?? time();
        $log->info = $data['info'] ?? '';
        $log->type = $data['type'] ?? 0;
        $log->currency = $data['currency'] ?? 0;
        $data_wallet['balance_type'] = $data2['balance_type'] ?? 0;
        $data_wallet['wallet_id'] = $data2['wallet_id'] ?? 0;
        $data_wallet['lock_type'] = $data2['lock_type'] ?? 0;
        $data_wallet['before'] = $data2['before'] ?? 0;
        $data_wallet['change'] = $data2['change'] ?? 0;
        $data_wallet['after'] = $data2['after'] ?? 0;
        $data_wallet['memo'] = $data['info'] ?? 0;
        $data_wallet['create_time'] = $data2['create_time'] ?? time();
        //dd($data_wallet);
        try {
            DB::transaction(function () use ($log, $data_wallet) {
                $log->save();
                $log->walletLog()->create($data_wallet);
            });
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
            return false;
        }
    }

    public static function newinsertLog($data = array(), $data2 = array())
    {
        $data = is_array($data) ? $data : func_get_args();
        $log = new self();
        $log->user_id = $data['user_id'] ?? false;;
        $log->value = $data['value'] ?? '';
        $log->created_time = $data['created_time'] ?? time();
        $log->info = $data['info'] ?? '';
        $log->type = $data['type'] ?? 0;
        $log->currency = $data['currency'] ?? 0;
//        $data_wallet['balance_type'] = $data2['balance_type']?? 0;
//        $data_wallet['wallet_id'] = $data2['wallet_id']?? 0;
//        $data_wallet['lock_type'] = $data2['lock_type']?? 0;
//        $data_wallet['before'] = $data2['before']?? 0;
//        $data_wallet['change'] = $data2['change']?? 0;
//        $data_wallet['after'] = $data2['after']?? 0;
//        $data_wallet['memo'] = $data['info']?? 0;
//        $data_wallet['create_time'] = $data2['create_time']?? time();
        //dd($data_wallet);
        try {
            DB::transaction(function () use ($log) {
                $log->save();
//                $log->walletLog()->create($data_wallet);
            });
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
            return false;
        }
    }


  public function getInfoAttribute()
    {
        $type = $this->attributes['type'];
        if ($type == 8) {
            $type = 3;
        }
        if ($type == 502) {
            $type = 8;
        }
        // var_dump($this);
        $prefix = '';
        //$lang = session()->get('lang');
        if ($this->attributes['is_lock']) {
            $prefix = str_replace('account_log_info.', '', __("account_log_info.is_lock")) . ' ';
        }
        $info = str_replace('account_log_info.', '', __("account_log_info.$type"));
        if ($info == $type) {
            $info = str_replace('account_log_info.', '', __("account_log_info.unknown"));
        }

        return $prefix . $info;
    }


    public static function getTypeInfo($type)
    {
        switch ($type) {

            case self::ADMIN_LEGAL_BALANCE:
                return '后台调节资金账户余额';
                break;
            case self::ADMIN_LOCK_LEGAL_BALANCE:
                return '后台调节资金账户锁定余额';
                break;
            case self::ADMIN_CHANGE_BALANCE:
                return '后台调节币币账户余额';
                break;
            case self::ADMIN_LOCK_CHANGE_BALANCE:
                return '后台调节币币账户锁定余额';
                break;
            case self::ADMIN_LEVER_BALANCE:
                return '后台调节合约账户余额';
                break;
            case self::ADMIN_LOCK_LEVER_BALANCE:
                return '后台调节合约账户锁定余额';
                break;
            case self::ADMIN_MICRO_BALANCE:
                return '后台调节期权账户余额';
                break;
            case self::ADMIN_LOCK_MICRO_BALANCE:
                return '后台调节期权账户锁定余额';
                break;
            case self::WALLET_LEGAL_OUT:
                return '资金账户转出至交易账户';
                break;
            case self::WALLET_LEGAL_IN:
                return '交易账户转入至资金账户';
                break;
            case self::WALLET_CHANGE_OUT:
                return '交易账户转出至资金账户';
                break;
            case self::WALLET_CHANGE_IN:
                return '资金账户转入交易账户';
                break;
            case self::WALLET_CHANGE_LEVEL_IN:
                return '合约账户转入交易账户';
                break;
            case self::WALLET_CHANGE_LEVEL_OUT:
                return '交易账户转出至合约账户';
                break;
            case self::WALLET_LEVEL_OUT:
                return '合约账户转出至交易账户';
                break;
            case self::WALLET_LEVEL_IN:
                return '交易账户转入合约账户';
                break;
            case self::INVITATION_TO_RETURN:
                return '邀请返佣金';
                break;
            case self::WALLETOUT:
                return '用户提币';
                break;
            case self::TRANSACTIONIN_IN_DEL:
                return '取消买入交易';
                break;
            case self::TRANSACTIONIN_OUT_DEL:
                return '取消卖出交易';
                break;
            case self::INTO_TRA_FB:
                return '美丽链资金交易余额转入';
                break;
            case self::INTO_TRA_BB:
                return '美丽链币币交易余额转入';
                break;
            case self::INTO_TRA_GG:
                return '美丽链合约交易余额转入';
                break;
            case self::WALLET_LEGAL_LEVEL_OUT:
                return '资金转入合约,资金减少';
                break;
            case self::WALLET_LEGAL_LEVEL_IN:
                return '资金转入合约，合约增加';
                break;
            case self::WALLET_LEVEL_LEGAL_OUT:
                return '合约转资金审核通过,合约减少';
                break;
            case self::WALLET_LEVEL_LEGAL_IN:
                return '合约转资金审核通过，资金增加';
                break;
            case self::WALLET_MICRO_LEVEL_OUT:
                return '期权转入合约,期权减少';
                break;
            case self::WALLET_MICRO_LEVEL_IN :
                return '期权转入合约，合约增加';
                break;
            case self::WALLET_LEVEL_MICRO_OUT :
                return '合约转期权审核通过,合约减少';
                break;
            case self::WALLET_LEVEL_MICRO_IN:
                return '合约转期权审核通过，期权增加';
                break;
            case self::WALLET_LEGAL_MICRO_OUT:
                return '资金转入期权,资金减少';
                break;
            case self::WALLET_LEGAL_MICRO_IN :
                return '资金转入期权，期权增加';
                break;
            case self::WALLET_MICRO_LEGAL_OUT :
                return '期权转资金审核通过,期权减少';
                break;
            case self::WALLET_MICRO_LEGAL_IN :
                return '期权转资金审核通过，资金增加';
                break;

            case self::WALLET_DONGJIEGANGGAN:
                return '合约转资金,冻结合约转化值';
                break;
            case self::WALLET_JIEDONGGANGGAN:
                return '合约转资金,审核不通过解冻';
                break;
            case self::CANDY_TOUSDT_CANDY:
                return '通证兑换USDT';
                break;
            case self::CANDY_LEVER_BALANCE:
                return '通证兑换，合约币增加';
                break;
            case self::PROFIT_LOSS_RELEASE:
                return '历史盈亏释放,增加合约币';
                break;
            case self::REWARD_CANDY:
                return '奖励通证';
                break;
            case self::REWARD_CURRENCY:
                return '奖励数字货币';
                break;
            case self::ADMIN_CANDY_BALANCE:
                return '后台调节通证';
                break;
            case self::TOBE_SELLER_SUB_USDT:
                return '申请成为商家扣除USDT';
                break;
            case self::CURRENCY_TO_USDT_MUL:
                return '资产兑换,减少持有币资金';
                break;
            case self::CURRENCY_TO_USDT_ADD:
                return '资产兑换,增加USDT资金';
                break;
            case self::JOIN_BOSS:
                return '激活BOSS';
            case self::TRANSFER_TO_LH_ACCOUNT:
                return '转账至余币宝钱包';
            case self::BOSS_WITHDRAW:
                return '从BOSS账户中转出余额';
            case self::BANK_WITHDRAW:
                return '余币宝提现';
            case self::LH_LOAN:
                return '质押';
            default:
                return '暂无此类型';
                break;
        }
    }

    /*public static function getTypeInfo($type)
    {
        switch ($type) {
            case self::BUY_BLOCK_CHAIN:
                return '购买区块链';
                break;
            case self::ADJUDT_SUB_BALANCE:
                return '后台调节账户余额';
                break;
            case self::ADMIN_LOCK_BALANCE:
                return '后台调节锁定余额';
                break;
            case self::ADMIN_REMAIN_LOCK_BALANCE:
                return '后台调节锁定余额变动剩余锁定余额';
                break;
            case self::ACCEPTOR_SELL:
                return '用户提现承兑申请';
                break;
            case self::ACCEPTOR_RECHARGE:
                return '用户充值承兑确认';
                break;
            case self::ACCEPTOR_RECHARGE_VAR:
                return '用户充值承兑手续费';
                break;
            case self::ACCEPTOR_RECHARGE_DEC:
                return '确认用户充值，承兑商充值额度减少';
                break;
            case self::ACCEPTOR_CASH_INC:
                return '确认用户充值，承兑商提现额度增加';
                break;
            case self::ACCEPTOR_CASH_DEC:
                return '确认用户提现，承兑商提现额度减少';
                break;
            case self::ACCEPTOR_RECHARGE_INC:
                return '确认用户提现，承兑商充值额度增加';
                break;
            case self::ACCEPTOR_SELL_RETURN:
                return '取消用户提现承兑申请';
                break;
            default:
                return '暂无此类型';
                break;
        }
    }*/

    public function getTypeInfoMAttribute(){
        $value = $this->attributes['type'];
        $a = [
            1=>"後台調節資金賬戶餘額",
            2=>"后台调节资金账户锁定余额",
            3=>"後台調節幣幣賬戶餘額",
            4=>"後台調節幣幣賬戶鎖定餘額",
            5=>"後台調節合約賬戶餘額",
            6=>"後台調節合約賬戶鎖定餘額",
//            7=>"後台調節期權賬戶餘額",
//            8=>"後台調節期權賬戶鎖定餘額",
            7=>"提幣記錄",
            8=>"充幣記錄",
            11=>"資金劃入記錄",
            12=>"資金劃出記錄",
            13=>"資金劃出記錄",
            14=>"資金劃出記錄",
            60=>"商家發布資金出售",
            61=>"出售給商家資金",
            62=>"用戶購買商家資金成功",
            63=>"商家購買用戶資金成功",
            64=>"出售給商家資金-取消",
            70=>"後台調節商家餘額",
            71=>"商家撤回發布資金出售",
            72=>"商家撤回發布資金出售",
            /*
            2=>"後台調節賬戶餘額",
            3=>"轉入",
            4=>"轉出",
            9=>"後台調節鎖定餘額",
            10=>"後台調節鎖定餘額相應剩餘鎖定餘額",
            11=>"鎖倉增加",
            13=>"用户提现承兑申请",
            14=>"用戶充值承兌確認",
            15=>"用戶充值承兌手續費",
            16=>"確認用戶充值，承兌商充值額度減少",
            17=>"確認用戶充值，承兌商提現額度增加",
            18=>"確認用戶提現，承兌商提現額度減少",
            19=>"確認用戶提現，承兌商充值額度增加",
            20=>"取消用戶提現承兌申請",
            91=>"取消用戶提現承兌,承兌商提現額度增加",
            */
           
            15=>"幣幣劃轉合約",
            16=>"合約劃轉幣幣",
            21=>"提交賣出，扣除",
            22=>"買入扣除",
            23=>"扣除賣方",
            24=>"提交買入，扣除",
            25=>"買方增加幣",
            26=>"賣方增加cny",
            27=>"撤銷增加",
            28=>"撤銷增加",
            29=>"賣出手續費",
            30=>"合約交易扣除保證金",
            31=>"平倉增加",
            32=>"爆倉凍結",
            34=>"隔夜費",
            35=>"交易手續費",
            36=>"合約交易取消",
            37=>"通證兌換合約幣增加",
            38=>"申請成為商家扣除USDT",
            39=>"資產兌換 減少兌換幣",
            40=>"資產兌換 增加USDT資金",
            50 => '幣幣交易凍結',
            51 => '幣幣交易',
            99=>"用戶申請提幣",
            100=>"用戶提幣成功",
            101=>"用戶提幣失敗",
            102=>"取消買入交易",
            103=>"取消買出交易",
            104=>"合約交易賬戶變化",
            105=>"獎勵通證",
            106=>"獎勵數字貨幣",
            107=>"通證兌換USDT",
            108=>"後臺調節通證",
            299=>"合約交易賬戶變化",
            401=>"轉賬",
            200=>"充幣增加余額",
            //c2c交易
            201=>"用戶發布資金出售",
            202=>"自動取消c2c資金交易",
            203=>"出售給用戶資金",
            204=>"用戶購買資金成功",
            205=>"商家撤回發布資金出售",
            206=>"資金(c2c)轉入合約",
            207=>"資金(c2c)轉入合約",
            208=>"合約轉入資金(c2c)",
            209=>"合約轉入資金(c2c)",
            211=>"審核不通過解凍合約凍結",
            212=>"歷史盈虧釋放,增加合約幣",
            501=>"期權下單",
            502=>"期權平倉結算",
            600=>"閃兌減少余額",
            601=>"閃兌通過,增加余額",
            602=>"閃兌減少鎖定余額",
            603=>"閃兌增加鎖定余額",
            604=>"閃兌駁回，增加余額",
//
//            206=>"期權(c2c)轉入合約",
//            207=>"期權(c2c)轉入合約",
//            208=>"合約轉入期權(c2c)",
//            209=>"合約轉入期權(c2c)",
//            206=>"資金(c2c)轉入期權",
//            207=>"資金(c2c)轉入期權",
//            208=>"期權轉入資金(c2c)",
//            209=>"期權轉入資金(c2c)",
//            206=>"閃兌轉入合約",
//            207=>"閃兌轉入合約",
//            208=>"合約轉入閃兌",
//            209=>"合約轉入閃兌",

            220=>"usdt兑换bmb 减少usdt",
            221=>"usdt兌換手續費",
            230=>"用戶購買保險",
            231=>"賠償用戶",
            232=>"賠償用戶,清除受保金額",
            233=>"保險解約，清除受保金額",
            234=>"保險解約，清除保險金額",
//            234=>"保險解約，賠付用戶",
            235=>"釋放保險交易手續費",
            250=>"下級返利",
            251=>"持幣生幣",
            1001=>"合約劃轉合約",
            1002=>"合約劃轉合約",
            1003=>"秒合約下單扣除本金",
            1004=>"鎖倉",
            2022=>"申購新幣",
            2023=>"申購新幣收益",

        ];
        return $a[$value]??'未知類型';
    }

    public function getTypeInfoEAttribute(){
        $value = $this->attributes['type'];
        $a = [
            1=>"Backstage adjustment of fund account balance",
            2=>"Adjust the locked balance of the fund account in the background",
            3=>"Backstage adjustment of currency account balance",
            4=>"Adjust the locked balance of the currency account in the background",
            5=>"Backstage adjustment of contract account balance",
            6=>"Adjust the locked balance of contract account in the background",
            7=>"Backstage adjustment of option account balance",
            8=>"Adjust the locked balance of option account in the background",
//            7=>"Withdrawal record",
//            8=>"Deposit record",
            11=>"Fund transfer record",
            12=>"Fund transfer record",
            13=>"Fund transfer record",
            14=>"Fund transfer record",
            60=>"Merchant releases funds for sale",
            61=>"Sell to merchant funds",
            62=>"User purchases merchant funds successfully",
            63=>"Merchant purchases user funds successfully",
            64=>"Sell to Merchant Funds-Cancel",
            70=>"Backstage adjustment of merchant balance",
            71=>"Merchants withdraw the release funds for sale",
            72=>"Merchants withdraw the release funds for sale",
            /*
            2=>"Backstage adjustment of account balance",
            3=>"Transfer in",
            4=>"Transfer out",
            9=>"Background adjustment lock balance",
            10=>"Background adjustment of locked balance corresponding to remaining locked balance",
            11=>"Increase in lock-up",
            13=>"User withdrawal and acceptance application",
            14=>"User deposit acceptance confirmation",
            15=>"User recharge acceptance fee",
            16=>"Confirm the user's recharge, the acceptor's recharge limit is reduced",
            17=>"Confirm user recharge, the acceptor's withdrawal limit increases",
            18=>"Confirm that the user withdraws, and the amount of withdrawal by the acceptor decreases",
            19=>"Confirm that the user withdraws cash, and the acceptor's recharge amount increases",
            20=>"Cancel user withdrawal acceptance application",
            91=>"Cancellation of user withdrawal acceptance and increase of withdrawal amount of acceptor",
            */
           
            15=>"Coin To Contract",
            16=>"Contract To exchange",
            21=>"Submit sell, deduct",
            22=>"Purchase deduction",
            23=>"Deduction of seller",
            24=>"Submit buy, deduct",
            25=>"Buyer increases currency",
            26=>"CNY added by seller",
            27=>"Withdrawal of addition",
            28=>"Withdrawal of addition",
            29=>"Service charge for selling",
            30=>"Contract transaction deducting margin",
            31=>"Closing increased",
            32=>"Frozen warehouse",
            34=>"Overnight fee",
            35=>"Transaction fee",
            36=>"Cancellation of contract transaction",
            37=>"Currency increase in exchange of token",
            38=>"Apply to become a merchant and deduct usdt",
            39=>"Decrease in exchange currency",
            40=>"Asset exchange increases usdt funds",
            50=>'Currency transaction frozen',
            51=>'Currency transaction',
            99=>"User application for withdrawal of currency",
            100=>"User withdraws currency successfully",
            101=>"User failed to withdraw currency",
            102=>"Cancel buy",
            103=>"Cancel buy out",
            104=>"Contract trading account changes",
            105=>"Reward pass",
            106=>"Reward digital currency",
            107=>"Exchange of token for usdt",
            108=>"Background regulation pass",
            299=>"Contract trading account changes",
            401=>"Transfer",
            200=>"Increase balance by replenishing currency",
            //c2c交易
            201=>"Users release funds for sale",
            202=>"Automatic cancellation of C2C fund transaction",
            203=>"Funds sold to users",
            204=>"Successful purchase of funds by user",
            205=>"Merchant withdraws release fund sale",
            206=>"Fund (C2C) transfer contract",
            207=>"Fund (C2C) transfer contract",
            208=>"Contract transfer in funds (C2C)",
            209=>"Contract transfer in funds (C2C)",
            211=>"Unfreeze contract freeze if audit fails",
            212=>"Release of historical profit and loss, increase contract currency",
            501=>"Option placing",
            502=>"Option closing settlement",
            600=>"Decrease balance of flash cash",
            601=>"Flash through, increase balance",
            602=>"Flash cash reduces locked balance",
            603=>"Flash cash increases lock up balance",
            604=>"Flash cash rejected, increase balance",
//
//            206=>"Option (C2C) transfer contract",
//            207=>"Option (C2C) transfer contract",
//            208=>"Contract transfer option (C2C)",
//            209=>"Contract transfer option (C2C)",
//            206=>"Capital (C2C) transfer option",
//            207=>"Capital (C2C) transfer option",
//            208=>"Funds transferred from options (C2C)",
//            209=>"Funds transferred from options (C2C)",
//            206=>"Flash transfer contract",
//            207=>"Flash transfer contract",
//            208=>"Contract transfer to flash cash",
//            209=>"Contract transfer to flash cash",

            220=>"Usdt exchange for BMB reduces usdt",
            221=>"Usdt exchange fee",
            230=>"User purchase insurance",
            231=>"Compensate users",
            232=>"Compensate the user and clear the insured amount",
            233=>"Cancellation of insurance",
            234=>"Cancellation of insurance",
//            234=>"Insurance termination, compensation for users",
            235=>"Release of insurance transaction fees",
            250=>"Subordinate rebate",
            251=>"Holding currency",
            1001=>"Contract To Contract",
            1002=>"Contract To Contract",
            1003=>"Deduction of purchase contract",
            1004=>"Lock position",
            2022=>"Apply for SGD",
            2023=>"Subscription of new currency income",
        ];
        return $a[$value]??'Unknow Operation';
    }
    public function getTypeInfoJAttribute(){
        $value = $this->attributes['type'];
        $a = [
            1=>"資金口座残高のバックステージ調整",
            2=>"バックグラウンドでファンド口座のロックされた残高を調整します",
            3=>"通貨口座残高のバックステージ調整",
            4=>"バックグラウンドで通貨口座のロックされた残高を調整する",
            5=>"契約口座残高のバックステージ調整",
            6=>"バックグラウンドで契約アカウントのロックされた残高を調整する",
            7=>"オプション口座残高のバックステージ調整",
            8=>"バックグラウンドでオプション口座の固定残高を調整します",
//            7=>"出金記録",
//            8=>"預金実績",
            11=>"送金実績",
            12=>"送金実績",
            13=>"送金実績",
            14=>"送金実績",
            60=>"商人は販売のための資金を解放します",
            61=>"マーチャントファンドに売ります",
            62=>"ユーザーが販売者の資金を正常に購入した",
            63=>"販売者がユーザーの資金を正常に購入",
            64=>"マーチャントファンドへの販売-キャンセル",
            70=>"販売者の残高の舞台裏での調整",
            71=>"商人は販売のためのリリース資金を引き出します",
            72=>"商人は販売のためのリリース資金を引き出します",
            /*
            2=>"口座残高のバックステージ調整",
            3=>"乗り換え",
            4=>"転出する",
            9=>"背景調整ロックバランス",
            10=>"残りのロックされたバランスに対応するロックされたバランスのバックグラウンド調整",
            11=>"ロックアップの増加",
            13=>"ユーザーの脱退および承認申請",
            14=>"ユーザー入金受付確認",
            15=>"ユーザーリチャージ受付手数料",
            16=>"ユーザーの再充電を確認すると、アクセプターの再充電制限が引き下げられます",
            17=>"ユーザーの再充電を確認すると、アクセプターの引き出し限度額が増加します",
            18=>"ユーザーの現金引き出しを確認し、引受人の現金引き出し額が減少した。",
            19=>"ユーザーの現金引き出しを確認し、引受人のチャージ額が増加した。",
            20=>"ユーザーの現金引受申請をキャンセルします。",
            91=>"ユーザーの現金引受をキャンセルし、引受人の現金引受額が増加しました。",
            */
           
            15=>"コイン換算契約",
            16=>"契約換算コイン",
            21=>"販売の提出、控除",
            22=>"買取控除",
            23=>"控除売り方",
            24=>"購入の提出、控除",
            25=>"買い手が貨幣を増やす",
            26=>"売り手増加cny",
            27=>"取消し追加",
            28=>"取消し追加",
            29=>"販売手数料",
            30=>"契約取引控除保証金",
            31=>"平仓増を买います",
            32=>"倉庫が凍結する",
            34=>"通夜料",
            35=>"取引手数料",
            36=>"契約取引のキャンセル",
            37=>"通証両替契約貨幣の増加",
            38=>"事業体控除の申請",
            39=>"資産両替は両替を減らす",
            40=>"資産交換でUSDT資金を増やす",
            50 => '通貨取引',
            51 => '通貨取引',
            99=>"ユーザーが貨幣の引き出しを申請する",
            100=>"ユーザーの貨幣獲得成功",
            101=>"ユーザーの貨幣獲得に失敗しました",
            102=>"取引のキャンセル",
            103=>"取引のキャンセル",
            104=>"契約取引口座の変更",
            105=>"証明書の奨励",
            106=>"奨励デジタル通貨",
            107=>"通証両替USDT",
            108=>"バックグランド調整通証",
            299=>"契約取引口座の変更",
            401=>"振替",
            200=>"チャージの残高増加",
            //c2c交易
            201=>"ユーザーが資金売却を発表する",
            202=>"自動キャンセルc 2 c資金取引",
            203=>"ユーザー資金への売却",
            204=>"ユーザーの資金購入成功",
            205=>"事業者が資金売却の発表を撤回する",
            206=>"資金（c 2 c）転入契約",
            207=>"資金（c 2 c）転入契約",
            208=>"契約繰越資金（c 2 c）",
            209=>"契約繰越資金（c 2 c）",
            211=>"審査解凍契約で凍結しない",
            212=>"ヒストリカル損益解放、契約貨幣増加",
            501=>"オプション注文",
            502=>"オプション倉庫決済",
            600=>"フラッシュ減少残高",
            601=>"フラッシュを通して残高を増やす",
            602=>"フラッシュはロック残高を減らす",
            603=>"フラッシュはロック残高を増加します。",
            604=>"フラッシュは却下し、残高を増やす",
//
//            206=>"契約繰越オプション（c 2 c）",
//            207=>"契約繰越オプション（c 2 c）",
//            208=>"契約繰越オプション（c 2 c）",
//            209=>"契約繰越オプション（c 2 c）",
//            206=>"資金（c 2 c）繰越オプション",
//            207=>"資金（c 2 c）繰越オプション",
//            208=>"オプション繰越資金（c 2 c）",
//            209=>"オプション繰越資金（c 2 c）",
//            206=>"フラッシュバック契約",
//            207=>"フラッシュバック契約",
//            208=>"契約転入フラッシュ",
//            209=>"契約転入フラッシュ",

            220=>"usdt両替bmb減少usdt",
            221=>"USdtがBMBに両替する手数料",
            230=>"ユーザーが保険を買う",
            231=>"ユーザーへの補償",
            232=>"ユーザーに賠償し、保証金額をクリアする",
            233=>"保険解約、保険金額クリア",
            234=>"保険解約、保険金額クリア",
//            234=>"保険解約、加入者の賠償",
            235=>"保険取引手数料の解放",
            250=>"部下が利益を取り戻す",
            251=>"貨幣を持つ",
            1001=>"契約換算契約",
            1002=>"契約換算契約",
            1003=>"購入契約控除",
            1004=>"ロックボックス",
            2022=>"SGDを申請する",
            2023=>"新しい通貨収入のサブスクリプション",
        ];
        return $a[$value]??'Unknow Operation';

    }

    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }

    //关联钱包记录模型
    public function walletLog()
    {
        return $this->hasOne('App\WalletLog', 'account_log_id', 'id')->withDefault();
    }
}
