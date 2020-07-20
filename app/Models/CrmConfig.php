<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;

class CrmConfig extends BaseModel
{

    protected $table = 'crm_config';

    const CONFIG_TYPE = [
        'client_status'=>'客户类型',
        'call_status'=>'显示状态',
        'follow_status'=>'跟进状态',
        'client_feeling'=>'客户满意度',
        'money_back_type'=>'还款方式',
        'money_pay_type'=>'付款方式',
        'house_use_for'=>'购房用途',
    ];

    const SWITCH_STATES = [
        'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
        'off' => ['value' => 2, 'text' => '关闭', 'color' => 'default'],
    ];

    const SWITCH_STATES_YN = [
        'on'  => ['value' => 1, 'text' => '是', 'color' => 'primary'],
        'off' => ['value' => 2, 'text' => '否', 'color' => 'default'],
    ];

    const SWITCH_STATES_YM = [
        'on'  => ['value' => 1, 'text' => '有', 'color' => 'primary'],
        'off' => ['value' => 2, 'text' => '无', 'color' => 'default'],
    ];

    public static function getKeyValue($type)
    {
        if(array_key_exists($type,self::CONFIG_TYPE)){

            $cacheFile = date('Ymd',time())."-getKeyValue-$type";

            if(Redis::exists($cacheFile)){
                $r_data = Redis::get($cacheFile);
            }else{
                $r_data = CrmConfig::query()
                    ->where('type',$type)
                    ->where('status',1)
                    ->pluck('value','key')
                    ->toArray();
                $r_data = json_encode($r_data);
                Redis::set($cacheFile,$r_data);
            }
            return json_decode($r_data, true);
        }else{
            admin_toastr('获取配置项出错，'.$type.' 不存在!','error');
            return [];
        }
    }

    public static function refreshCache()
    {
        return Redis::flushDB();
    }

    public static function delCache($cacheName)
    {
        Redis::del($cacheName);
    }


}
