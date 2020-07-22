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
            'name' => $row[0],
            'mobile' => $row[1],
            'sex' => $row[2],
            'marriage' => $row[3],
            'email' => $row[4],
            'education' => $row[0],
            'age' => $row[0],
            'china_id' => $row[0],
            'china_id_valid' => $row[0],
            'income' => $row[0],
            'family_in' => $row[0],
            'family_out' => $row[0],
            'shebao' => $row[0],
            'shebao_in' => $row[0],
            'gongjijin' => $row[0],
            'gongjijin_in' => $row[0],
            'company' => $row[0],
            'company_address' => $row[0],
            'company_type' => $row[0],
            'company_belong' => $row[0],
            'company_post' => $row[0],
            'debt' => $row[0],
            'monthly_debt' => $row[0],
            'note' => $row[0],
        ]);
   }
}
