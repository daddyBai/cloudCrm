<?php

namespace App\Models;
/**
 * 跟进记录表
 * Class ClientRecords
 * @package App\Models
 */
class ClientRecords extends BaseModel
{
    protected $table = 'crm_client_records';

    public function client(){
        return $this->belongsTo(Client::class,'client_id','id');
    }

}
