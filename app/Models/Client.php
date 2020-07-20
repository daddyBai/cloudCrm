<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Cache;
use Illuminate\Support\Facades\Redis;


class Client extends BaseModel
{
    use SoftDeletes;

    protected $table = 'crm_client';

    protected $fillable = ['name','mobile','status','employee_id','employee_status','last_updated_at'];

    const marriage = [
        1=>'已婚',
        2=>'未婚',
        3=>'离异'
    ];

    const education = [
        1=>'小学',
        2=>'初中',
        3=>'高中',
        4=>'专科',
        5=>'本科',
        6=>'硕士',
        7=>'博士'
    ];

    const company_type = [
        1=>'国企',
        2=>'私企',
        3=>'外企',
        4=>'事业单位'
    ];

    const company_post = [
        1=>'个体老板',
        2=>'高层管理人员',
        3=>'中层管理人员',
        4=>'基层管理人员',
        5=>'普通基层员工',
        6=>'自由职业者',
    ];

    const pay_type = [
        1=>'全款',2=>'按揭'
    ];

    const house_use = [ 1=>'自住',2=>'出租'];

    /**
     * 分配客户
     * @param $client_ids
     * @param $employee_id
     * @return int
     */
    public function distributionClient($client_ids, $employee_id)
    {
        return Client::query()->whereNull('employee_id')->whereIn('id',$client_ids)->update(['employee_id'=>$employee_id]);
    }

    public function GiveClient($client_ids, $employee_id)
    {
        return Client::query()->whereIn('id',$client_ids)->update(['employee_id'=>$employee_id]);
    }

    public function records()
    {
        return $this->hasMany(ClientRecords::class,'client_id','id')->orderBy('id','desc')->limit(1);
    }

    public function houses()
    {
        return $this->hasMany(Assets::class,'client_id','id');
    }

    public function cars()
    {
        return $this->hasMany(Assets::class,'client_id','id')->where('category','car');
    }

    public function updateLastUpdateTime()
    {
        return $this->update(['last_updated_at'=>Carbon::now()->toDateTimeString()]);
    }

    public static function getAllClients()
    {
        $client_count = self::query()->count();
        $cacheFile = date('Ymd',time())."client_$client_count";
        if(Redis::exists($cacheFile)){
            $r_data = Redis::get($cacheFile);
        }else{
            $r_data = self::query()->pluck('name','id')->toArray();
            $r_data = json_encode($r_data);
            Redis::set($cacheFile,$r_data);
        }
        return json_decode($r_data, true);
    }


}
