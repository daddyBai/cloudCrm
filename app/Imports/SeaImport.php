<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;

class SeaImport implements ToModel
{

    // todo
    public function model(array $row)
    {
        return new Client([
            'mobile' => $row[0]
        ]);
   }
}
