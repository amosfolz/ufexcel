<?php


namespace UserFrosting\Sprinkle\LaravelExcel\Exports;


use UserFrosting\Sprinkle\Address\Database\Models\Address;
use Maatwebsite\Excel\Concerns\FromCollection;




class AddressExport implements FromCollection {


  public function collection()
     {
         return Address::all();
     }

}
