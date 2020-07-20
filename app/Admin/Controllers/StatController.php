<?php
namespace App\Admin\Controllers;


use App\Models\User;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;

class StatController extends BaseController
{

    public function index(Content $content)
    {
        $data = $this->MineStat();
        return $content
            ->header('数据统计')
            ->row(Dashboard::title())
            ->row(new Box('Bar chart', view('admin.chartjs',['data'=>json_encode($data)])));
    }


    /**
     * 我的统计
     * @param $employee_ids
     * @return array
     */
    public function MineStat($employee_ids = [])
    {
        if(empty($employee_ids)){
            $employee_ids = [self::current_id()];
        }

        $data = User::query()
            ->whereIn('id',$employee_ids)
            ->withCount(['hasManyCall','hasManyRecord'])
            ->with(['hasManyMoneyOut'])
            ->get()
            ->map(function ($user){
                $user['money_out_sum'] = $user->hasManyMoneyOut->pluck('out_money')->sum();
                return $user;
            })->reduce(function($lookup,$item){
//                $lookup['name'] = $item["name"];
                $lookup['has_many_call_count'] = $item["has_many_call_count"];
                $lookup['has_many_record_count'] = $item["has_many_record_count"];
                $lookup['money_out_sum'] = $item["money_out_sum"];
                return $lookup;
            },[]);
        return $data;
    }
}
