<?php

namespace App\Exports;

use App\Models\Event;
use Auth;
use Maatwebsite\Excel\Concerns\FromCollection;

class SchedulesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Event::where('users_id',Auth::user()->id)->get();
    }
}
