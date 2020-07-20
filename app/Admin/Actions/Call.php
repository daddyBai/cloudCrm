<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;

class Call extends RowAction
{
    public $name = '拨号';
    public $id = '';

    public function __construct($id)
    {
        $this->id = $id;
        parent::__construct();
    }

//    public function handle(Model $model)
//    {
//        // $model ...
//
//        return $this->response()->success('Success message.')->refresh();
//    }

    public function href()
    {
        return 'dial/call/'.$this->id;
    }



}
