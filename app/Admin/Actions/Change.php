<?php

namespace App\Admin\Actions;

use App\Models\Client;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class Change extends BatchAction
{
    public $name = '转化为客户';
    public $id = '';

    public function handle(Collection $collection,Request $request)
    {
        $ids = [];
        foreach ($collection as $model){
            array_push($ids,$model['id']);
        }
        $rs = Client::query()->whereIn('id',$ids)->update(['status'=>1]);

        if($rs > 0){
            return $this->response()->success('转化成功 '. $rs . ' 个客户')->refresh();
        }else{
            return $this->response()->error('转化成功 '. $rs . ' 个客户')->refresh();
        }

    }

}
