<?php

namespace App\Models;

use Encore\Admin\Traits\ModelTree;
use Illuminate\Support\Facades\Redis;

class Department extends BaseModel
{
    protected $table = 'crm_department';

    use ModelTree;


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
            $dp = self::query()->pluck('title','id')->toArray();
            $r_data = json_encode($dp);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    public function employees()
    {
        return $this->hasMany(User::class,'department_id','id');
    }

}
