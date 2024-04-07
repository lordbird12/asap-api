<?php

namespace App\Exports;

use App\ActivityBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityBookExport implements FromArray , WithHeadings
{
    protected  $data;
    public function __construct(array $data)
   {
       $this->data = $data;

   }
    public function array(): array
   {
       return $this->data;
   }

   public function headings(): array
   {
       return [
           'ทะเบียนรถ',
           'วันที่',
           'เวลา',
           'ไมล์',
           'กิจกรรม',
           'ศูนย์บริการ',
           'อายุรถ',
       ];
   }
}
