<?php
/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;

class News extends ShopModel
{
    protected $table = 'news';
    //自动时间戳
    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

        // 'jp' => '日语',
        // 'ko' =>'韩语'
    protected static $langList = [
        
        'zh' => '中文简体',
        'hk' => '中文繁体',
        'en' => '英文',
        'jp' => '日语',
        'kor' => '韩语',
        'xp' => '西班牙语',
        'ydl' => '意大利语'
    ];
/*
import zh from './zh.js';	//中文
import en from './en.js';	//英文
import hk from './hk.js';	//繁体
import th from './th.js';	//泰语
import jp from './jp.js';	//日语
import kor from './kor.js';	//韩语
import fra from './fra.js';	//法语
import spa from './spa.js';	//西班牙
import ger from './ger.js';	//德语
import tur from './tur.js';	//土耳其语
import ita from './ita.js';	//意大利语
*/
    public static function getLangeList()
    {
        return self::$langList;
    }
    /**
     * 定义新闻和分类的一对多相对关联
     */

    public function cate()
    {
        return $this->belongsTo('App\NewsCategory', 'c_id');
    }

    /**
     * 定义新闻和评论的一对多关联
     */

    public function discuss()
    {
        return $this->hasMany('App\NewsDiscuss', 'n_id');
    }
    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    public function getThumbnailAttribute()
    {
        $thumbnail = $this->attributes['thumbnail'];
        return $thumbnail ? $thumbnail : URL("images/zwtp.png");
    }

    public function getUpdateTimeAttribute()
    {
        $value = $this->attributes['update_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    protected static function boot(){
        

    }

}
