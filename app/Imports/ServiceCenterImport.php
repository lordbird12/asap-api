<?php

namespace App\Imports;

use App\ServiceCenter;
use Maatwebsite\Excel\Concerns\ToModel;

class ServiceCenterImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ServiceCenter([
            //
        ]);
    }
}
