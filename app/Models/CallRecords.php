<?php

namespace App\Models;

class CallRecords extends BaseModel
{
    protected $table = 'crm_client_call_records';

    protected $fillable = ['client_id',
        'client_id',
        'employee_id',
        'call_type',
        'call_from',
        'call_to',
        'client_name',
        'call_at'];
}
