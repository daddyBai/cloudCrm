<?php
namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model{

    public static function current_id()
    {
        return Admin::user()->id;
    }

    public $true_client = [1=>'客户',2=>'线索'];
    public $real_finished = [1=>'是','否'];

}
