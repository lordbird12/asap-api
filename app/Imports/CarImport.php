<?php

namespace App\Imports;

use App\Car;
use Maatwebsite\Excel\Concerns\ToModel;

class CarImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Car([
            //
        ]);
    }
}
