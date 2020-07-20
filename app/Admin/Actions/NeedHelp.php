<?php

namespace App\Admin\Actions;

use App\Models\ClientHelp;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class NeedHelp extends BatchAction
{
    public $name = '申请协助';

    public function handle(Collection $collection,Request $request)
    {
        $emp = $request->get('employee');

        $ids = [];
        foreach ($collection as $model){
            array_push($ids,$model['id']);
        }

        $rs = (new ClientHelp())->distributionClient($ids, $emp);

        if($rs > 0){
            return $this->response()->success('申请协助成功 '. $rs . ' 个客户')->refresh();
        }else{
            return $this->response()->error('申请协助成功 '. $rs . ' 个客户')->refresh();
        }

    }

    public function form()
    {
        $this->select('employee','申请协助')->options(User::Employees());
    }

}
