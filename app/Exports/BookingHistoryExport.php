<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingHistoryExport implements FromArray , WithHeadings
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
           'วันเวลา',
           'ไมล์',
           'กิจกรรม',
           'ศูนย์บริการ',
           'พนักงานที่ทำรายการ',
       ];
   }
}
