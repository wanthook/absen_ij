<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Auth;
use Illuminate\Support\Facades\Crypt;
use App\MasterOption;
use App\User;

class SubTransaction extends Pivot
{
    /**
     * Convert the amount from pence to pounds.
     *
     * @param $amount
     * @return float|int
     */
    public function getNilaiAttribute($amount)
    {
        if(Auth::user()->type->nama == 'PAJAK')
        {
            return Crypt::decryptString($amount);
        }
        else if(Auth::user()->type->nama == 'PAYROLL')
        {
            $mas = MasterOption::find($this->jenis_id);
            if($mas->nama == 'GAPOK')
            {
                return Crypt::decryptString($amount);
            }
        }
        return $amount;
    }
    
    /**
     * Convert the amount from pence to pounds.
     *
     * @param $amount
     * @return float|int
     */
    public function getCreatedByAttribute($amount)
    {
        return User::find($amount);
    }
}
