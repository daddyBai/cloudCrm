<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    public static function UserWithoutDepartment()
    {
        $cacheFile = date('Ymd',time()).'-UserWithoutDepartment-';

        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $users = DB::table('admin_users')
                ->whereNull('department_id')
                ->pluck('name', 'id')
                ->toArray();
            $r_data = json_encode($users);
            Redis::set($cacheFile,$r_data);
        }

        return json_decode($r_data, true);
    }

    public static function getDepartment($employee_id){
        $user = self::query()->where('id',$employee_id)->first()->toArray();
        if($user && isset($user['department_id'])){
            return $user['department_id'];
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
        return self::query()->whereIn('id',$employee_ids)->update(['department_id'=>$department_id]);
    }


    /**
     * 退出部门
     * @param $employee_ids
     * @return int
     */
    public static function quitDepartment($employee_ids)
    {
        return self::query()->whereIn('id',$employee_ids)->update(['department_id'=>NULL]);
    }

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
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

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    public static function myTitle($id,$name=true)
    {
        $cacheFile = date('Ymd',time()).'-myTitle-$name-'.$id;
        if(Redis::exists($cacheFile)) {
            $r_data = Redis::get($cacheFile);
        }else{
            $me = self::query()->where('id',$id)->with('department')->first();

            $firstDep = isset( $me->department->title) ? $me->department->title : '';
            if(isset($me->department->parent_id)){
                $secDep = $me->department->parent_id > 0 ? Department::allDepartment()[$me->department->parent_id].' > ':'';
            }else{
                $secDep ='';
            }
            $myname = $name ? $me->name.'<br/>' : '';
            $r_data = "$myname( $secDep $firstDep )";
            Redis::set($cacheFile,$r_data);
        }
        return $r_data;
    }
}
