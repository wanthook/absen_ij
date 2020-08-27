<?php

namespace App\Http\Controllers;

use App\Libur;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\QueryException;

use Auth;
use Validator;

class LiburController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.libur.index');
    }

    public function fc(Request $request)
    {
        try
        {            
            $req = $request->all();
//            if(!empty($req['id']))
//            {
                $row = Libur::where('tanggal', '>=', Carbon::now()->subYear(2)->toDateString())->get();
//                dd($res);
                $ret = array();
                
                foreach($row as $res)
                {
                    $ret[] = array(
                        'title' => $res->keterangan,
                        'start' => $res->tanggal,
                        'end' => $res->tanggal,
                        'id' => $res->id
                    );
                }
                
                echo json_encode($ret);
//            }
//            else
//            {
//                echo "";
//            }
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal diambil',
                'err' => $er->getMessage()
            ));
        }
    }
    
    public function store(Request $request)
    {
         try
        {
            $validation = Validator::make($request->all(), 
            [                
//                'keterangan'   => 'required',
                'tgl'   => 'required'
            ],
            [
//                'keterangan.required'  => 'Keterangan harus diisi.',
                'tgl.required'  => 'Tanggal harus dipilih.'
            ]);

            if($validation->fails())
            {
                echo json_encode(array(
                    'status' => 0,
                    'msg'   => $validation->errors()->all()
                ));
            }
            else
            {
                $req = $request->all();
                
                if($req['keterangan'])
                {
                    $datas = [
                        'tanggal' => trim($req['tgl']),
                        'keterangan' => strtoupper(trim($req['keterangan'])),
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ];

                    Libur::create($datas);
                }
                else
                {
                    Libur::where('tanggal', $req['tgl'])->delete();
                }

                echo json_encode(array(
                    'status' => 1,
                    'msg'   => 'Data berhasil diubah'
                ));
                    
            }
            
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan',
                'err' => $er->getMessage()
            ));
        }
    }
}
