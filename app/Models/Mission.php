<?php

namespace App\Models;

class Mission extends BaseModel
{
    protected $table = 'crm_mission';

    protected $fillable = [
        'finished','status'
    ];
}
