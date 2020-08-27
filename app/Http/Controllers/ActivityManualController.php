<?php

namespace App\Http\Controllers;

use App\ActivityManual;
use App\Karyawan;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ActivityManualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.transaksi.absen_manual.index');
    }
    
    public function store(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sKar'   => 'required',
                'sWaktuIn'   => 'required',
                'sWaktuOut'   => 'required',
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sKar.required'  => 'Karyawan harus dipilih.',
                'sWaktuIn.required'  => 'Jam Masuk harus diisi.',
                'sWaktuOut.required'  => 'Jam Keluar harus diisi.',
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

                $req['created_by']   = Auth::user()->id;  
                $req['updated_by']   = Auth::user()->id;  
                
                $act = ActivityManual::where('tanggal', $req['sTanggal'])->where('karyawan_id', $req['sKar']);
                
                $req['tanggal'] = $req['sTanggal'];
                $req['karyawan_id'] = $req['sKar'];
                $req['jam_masuk'] = $req['sWaktuIn'];
                $req['jam_keluar'] = $req['sWaktuOut'];
                
                if(!$act->count())
                {
                    ActivityManual::create($req);
                }
                else
                {
                    $act = $act->first();
                    ActivityManual::find($act->id)->fill($req)->save();
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
    
    public function storeUploadAbsenManual(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'formUpload'   => 'required',
            ],
            [
                'formUpload.required'  => 'File harus diisi.',
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
                
                $fileVar = $req['formUpload'];
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadAbsenManualKaryawan');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadAbsenManualKaryawan');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                //                $fileStorage = fopen(storage_path('tmp').'/tempFileUploadJadwalManualKaryawan','r');
                
                $x = 0;    
                $arrKey = null;
                
//                while(! feof($fileStorage))
                foreach($sheetData as $sD)
                {
                    if(empty($sD[0]))
                    {
                        break;
                    }
                    if($x == 0)
                    {       
                        foreach($sD as $k => $v)
                        {
                            if(empty($v))
                            {
                                break;
                            }
                            $arrKey[$v] = $k;
                        }
                        $arrKey = (object) $arrKey;
                        
                        $x++;
                        continue;
                    }
                    
                    $dS = array();
                    
                    $karyawan = Karyawan::where('pin',trim($sD[$arrKey->pin]));
                    
                    if($karyawan->count())
                    {
                        $karId = $karyawan->first()->id;
                        $kar = Karyawan::find($karId);
                        
                        $req['created_by']   = Auth::user()->id;  
                        $req['updated_by']   = Auth::user()->id;  

                        $act = ActivityManual::where('tanggal', $sD[$arrKey->tanggal])->where('karyawan_id', $karId);

                        $req['tanggal'] = $sD[$arrKey->tanggal];
                        $req['karyawan_id'] = $karId;
                        $req['jam_masuk'] = $sD[$arrKey->jam_masuk];
                        $req['jam_keluar'] = $sD[$arrKey->jam_pulang];
                        $req['keterangan'] = $sD[$arrKey->keterangan];

                        if(!$act->count())
                        {
                            ActivityManual::create($req);
                        }
                        else
                        {
                            $act = $act->first();
                            ActivityManual::find($act->id)->fill($req)->save();
                        }
                        
                    }
                    else
                    {
                        continue;
                    }
                }
            }
            echo json_encode(array(
                    'status' => 1,
                    'msg'   => 'Data berhasil disimpan'
                ));
        }
        catch (QueryException $er)
        {
            // echo print_r($er->getMessage());
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'.$er->getMessage()
            ));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Karyawan  $Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            ActivityManual::find($req['id'])->delete();
            
            echo json_encode(array(
               "status" => 1,
                "msg"   => "Data berhasil dihapus."
            ));
        } 
        catch (QueryException $ex) 
        {
            echo json_encode(array(
               "status" => 0,
                "msg"   => "Data gagal dihapus."
            ));
        }
    }
    
    
    public function dt(Request $request)
    {
        $req    = $request->all();
                        
        $datas = ActivityManual::with(['karyawan'])->where('tanggal', $req['sTanggal']);        
                
        return  Datatables::of($datas)
                ->make(true);
    }
}
