<?php

namespace App\Exports;

use App\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TicketExport implements FromArray , WithHeadings
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
           'รหัส',
           'ลูกค้า',
           'เบอร์โทร',
           'รายละเอียด',
           'สถานะ',
           'วันที่',
       ];
   }
}
