<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;

class China extends BaseModel
{
    protected $table = 'china_area';

    public static function change($code){
        if(self::query()->where('code',$code)->exists()) {
            $cacheFile = "CHINA_$code";
            if (Redis::exists($cacheFile)) {
                $code = Redis::get($cacheFile);
            } else {
                $code = self::query()->where('code', $code)->first()->toArray();
                Redis::set($cacheFile, $code['name']);
            }
        }
        return $code;
    }
}
