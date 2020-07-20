<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Department extends BaseModel
{
    protected $table = 'crm_department';


    public function getEmployeeAttribute($value)
    {
        return json_decode($value);
    }

    public function setEmployeeAttribute($value)
    {
        return $this->attributes['employee'] = json_encode($value);
    }

    public function delete()
    {
        User::quitDepartment($this->employee);
        return parent::delete();
    }

    public static function allDepartment()
    {
        $cacheFile = date('Ymd',time()).'-allDepartment-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $dp = self::query()->pluck('name','id')->toArray();
            $r_data = json_encode($dp);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

}
