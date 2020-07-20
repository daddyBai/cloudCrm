<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;

class ClientHelp extends BaseModel
{
    protected $table = 'crm_client_help';


    /**
     * 协助销售
     * @param $client_ids
     * @param $employee_id
     * @return int
     */
    public function distributionClient($client_ids, $employee_id)
    {
        $count = 0;
        $mine_id = Admin::user()->id;
        if(is_array($client_ids)){
            foreach ($client_ids as $c){
                $count += self::insert([
                    'client_id'=>$c,
                    'employee_id'=>$mine_id,
                    'helper_id'=>$employee_id,
                    'dp'=>User::getDepartment($employee_id),
                    'created_at'=>Carbon::now()->toDateTimeString()
                ]);
            }
        }

        return $count;
    }
}
