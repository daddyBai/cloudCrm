<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_users';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 所有员工
     * @return mixed
     */
    public static function Employees()
    {
        $cacheFile = date('Ymd',time()).'-Employees-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $users = DB::table('admin_role_users')->where('role_id', 3)->pluck('user_id')->toArray();
            $users = DB::table('admin_users')->whereIn('id', $users)->pluck('name', 'id')->toArray();
            $r_data = json_encode($users);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    public static function Users()
    {
        $cacheFile = date('Ymd',time()).'-Users-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $users = DB::table('admin_users')->pluck('name', 'id')->toArray();
            $r_data = json_encode($users);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    public static function Manager()
    {
        $cacheFile = date('Ymd',time()).'-Manager-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $users = DB::table('admin_role_users')->where('role_id', 2)->pluck('user_id')->toArray();
            $users = DB::table('admin_users')->whereIn('id', $users)->pluck('name', 'id')->toArray();
            $r_data = json_encode($users);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    /**
     * 没有部门的员工
     * @return mixed
     */
    public static function EmployeesWithoutDepartment()
    {
        $cacheFile = date('Ymd',time()).'-EmployeesWithoutDepartment-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $users = DB::table('admin_role_users')->where('role_id', 3)->pluck('user_id')->toArray();
            $users = DB::table('admin_users')
                ->whereIn('id', $users)
                ->whereNull('department')
                ->pluck('name', 'id')
                ->toArray();
            $r_data = json_encode($users);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    public static function getDepartment($employee_id){
        $user = self::query()->where('id',$employee_id)->first()->toArray();
        if($user && isset($user['department'])){
            return $user['department'];
        }
        return 0;
    }

    /**
     * 加入部门
     * @param $department_id
     * @param $employee_ids
     * @return int
     */
    public static function joinDepartment($department_id, $employee_ids)
    {
        return self::query()->whereIn('id',$employee_ids)->update(['department'=>$department_id]);
    }


    /**
     * 退出部门
     * @param $employee_ids
     * @return int
     */
    public static function quitDepartment($employee_ids)
    {
        return self::query()->whereIn('id',$employee_ids)->update(['department'=>NULL]);
    }

    public static function Department()
    {
        $cacheFile = date('Ymd',time()).'-Department-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $department = self::query()->pluck('name','id')->toArray();
            $r_data = json_encode($department);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    /**
     * 呼叫次数
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyCall()
    {
        return $this->hasMany(CallRecords::class,'employee_id','id');
    }

    /**
     * 跟进次数
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyRecord()
    {
        return $this->hasMany(ClientRecords::class,'employee_id','id');
    }

    /**
     * 放出贷款
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyMoneyOut()
    {
        return $this->hasMany(MoneyOut::class,'employee_id','id');
    }

    public function hasManyClient()
    {
        return $this->hasMany(Client::class, 'employee_id','id');
    }


}
