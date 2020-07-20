<?php
namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Redis;

class Manager extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admin_users';

    public static function myGroup()
    {
        if(Admin::user()->isRole('manager')){
            $cacheName = date('Ymd',time()).'-myGroup-'.self::current_id();
            if(Redis::exists($cacheName)){
                $users = json_decode(Redis::get($cacheName), true);
            }else{
                $dep = Department::query()->where('leader',self::current_id())
                    ->pluck('employee','id')
                    ->collapse();
                $users = User::query()
                    ->whereIn('id',$dep)
                    ->orderBy('id')
                    ->pluck('name','id')
                    ->toArray();
                Redis::set($cacheName, json_encode($users));
            }
            return $users;
        }
    }
}
