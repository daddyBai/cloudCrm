<?php

namespace App\Admin\Actions;

use App\Models\Client;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GiveOther extends BatchAction
{
    public $name = '客户转交';

    public function handle(Collection $collection,Request $request)
    {
        $emp = $request->get('employee');

        $ids = [];
        foreach ($collection as $model){
            array_push($ids,$model['id']);
        }

        $rs = (new Client())->GiveClient($ids, $emp);

        if($rs > 0){
            return $this->response()->success('转交成功 '. $rs . ' 个客户')->refresh();
        }else{
            return $this->response()->error('转交成功 '. $rs . ' 个客户')->refresh();
        }

    }

    public function form()
    {
        $this->select('employee','客户转交')->options(User::Employees());
    }

}
