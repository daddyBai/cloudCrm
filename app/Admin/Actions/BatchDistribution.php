<?php

namespace App\Admin\Actions;

use App\Models\Client;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BatchDistribution extends BatchAction
{
    public $name = '批量分配';

    public function handle(Collection $collection,Request $request)
    {
        $emp = $request->get('employee');

        $ids = [];
        foreach ($collection as $model){
            array_push($ids,$model['id']);
        }

        $rs = (new Client())->distributionClient($ids, $emp);

        if($rs > 0){
            return $this->response()->success('分配成功 '. $rs . ' 个客户')->refresh();
        }else{
            return $this->response()->error('分配成功 '. $rs . ' 个客户')->refresh();
        }

    }

    public function form()
    {
        $this->select('employee','分配给员工')->options(User::Employees());
    }

}
