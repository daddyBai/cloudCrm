<?php

namespace App\Admin\Actions;

use App\Models\Client;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class Sea extends BatchAction
{
    public $name = '放入公海';
    public $id = '';

    public function handle(Collection $collection,Request $request)
    {
        $ids = [];
        foreach ($collection as $model){
            array_push($ids,$model['id']);
        }
        $rs = Client::query()->whereIn('id',$ids)->update(['in_sea'=>1]);

        if($rs > 0){
            return $this->response()->success('放入成功 '. $rs . ' 个客户')->refresh();
        }else{
            return $this->response()->error('放入成功 '. $rs . ' 个客户')->refresh();
        }

    }

}
