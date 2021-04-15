<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Auth;
use App\RequestAlasan;

class PivotRequestAlasan extends Pivot
{
    const updated_at = null;
    /**
     * Convert the amount from pence to pounds.
     *
     * @param $amount
     * @return float|int
     */
    public function getRequestAlasanIdAttribute($id)
    {
        $ret = null;
        if($id)
        {
            $ret = RequestAlasan::find($id);
        }

        return $ret;
    }
}
