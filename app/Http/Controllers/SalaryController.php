<?php

namespace App\Http\Controllers;

use App\Salary;
use App\Karyawan;
use App\MasterOption;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payroll.master_salary.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'tanggal'   => 'required',
                'karyawan_id'   => 'required',
                'jenis_id'   => 'required',
                'tipe'   => 'required',
                'nilai'   => 'required',
            ],
            [
                'tanggal.required'  => 'Tanggal harus diisi.',
                'karyawan_id.required'  => 'Karyawan harus dipilih.',
                'jenis_id.required'  => 'Jenis harus dipilih.',
                'tipe.required'  => 'Tipe harus dipilih.',
                'nilai.required'  => 'Nilai harus diisi.',
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
                
                $karyawan = Karyawan::find($req['karyawan_id']);
                $jenis = MasterOption::find($req['jenis_id']);
                
                if($karyawan->id)
                {
                    $tgl = Carbon::createFromFormat('Y-m-d', $req['tanggal']);
                    $gaji = $karyawan->salary()->wherePivot('tanggal', $tgl->toDateString());
                    
                    if($gaji)
                    {
                        $gaji->detach($jenis->id);
                    }
                    
                    $karyawan->salary()->attach($jenis->id, 
                            [
                                'tanggal' => $tgl->toDateString(), 
                                'tipe' => $req['tipe'],
                                'nilai' => Crypt::encryptString($req['nilai']),
                                'created_by' => Auth::user()->id
                            ]);
                    
//                    $karyawan->fill(['updated_by' => Auth::user()->id ,'updated_at' => Carbon::now()])->save();
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'   => 'Data gagal disimpan'
                    ));
                }
                
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
    
    
    
    public function storeUploadSalary(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadSalary');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadSalary');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                
//                $fileStorage = fopen(storage_path('tmp').'/tempFileUploadJadwalKaryawan','r');
                
                $x = 0;    
                $arrKey = null;
                
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
                    
                    $karyawan = Karyawan::where('pin',trim($sD[$arrKey->pin]));                    
                    $jenis = MasterOption::where('nama', strtoupper(trim($sD[$arrKey->jenis])))->where('kode', 'SALARY');
                    
                    if($karyawan->count() && $jenis->count())
                    {
                        $karId = $karyawan->first()->id;
                        $jenId = $jenis->first()->id;
                        $kar = Karyawan::find($karId);
                        $par = $kar->salary()->wherePivot('tanggal', trim($sD[$arrKey->tanggal]));
//                            dd($par);
                        if($par)
                        {
                            $par->detach($jenId);
                        }

                        $attach = ['tanggal' => trim($sD[$arrKey->tanggal]),
                                   'nilai' => Crypt::encryptString(trim($sD[$arrKey->nilai])),
                                   'tipe' => trim($sD[$arrKey->tipe]),
                                   'created_by' => Auth::user()->id];

                        $kar->salary()->attach($jenId, $attach);
                        
                    }
                    else
                    {
                        continue;
                    }
                }
                
                Storage::delete(storage_path('tmp').'/tempFileUploadSalary');
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
     * Display the specified resource.
     *
     * @param  \App\Salary  $salary
     * @return \Illuminate\Http\Response
     */
    public function show(Salary $salary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Salary  $salary
     * @return \Illuminate\Http\Response
     */
    public function edit(Salary $salary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Salary  $salary
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Salary $salary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Salary  $salary
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'tanggal'   => 'required',
                'jenis_id'   => 'required',
                'karyawan_id'   => 'required'
            ],
            [
                'tanggal.required'  => 'Tanggal harus diisi.',
                'karyawan_id.required'  => 'Karyawan harus dipilih.',
                'jenis_id.required'  => 'Jenis harus dipilih.'
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

                $jd = Karyawan::find($req['karyawan_id']);
                
                $par = $jd->salary()->wherePivot('tanggal', $req['tanggal']);
                
                if($par)
                {
                    $par->detach($req['jenis_id']);
                }

                echo json_encode(array(
                    'status' => 1,
                    'msg'   => 'Data berhasil dihapus'
                ));
                    
            }
            
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal dihapus',
                'err' => $er->getMessage()
            ));
        }
    }
    
    
    public function dtMaster(Request $request)
    {
        $req    = $request->all();
                        
        $datas = Karyawan::with(['salary' => function($q){
            $q->orderBy('tanggal', 'desc');
        },'divisi'])->author()->KaryawanAktif();        
        
        if(isset($req['sKar']))
        {
            $datas->where('id', $req['sKar']);
        }
        
        if(isset($req['sDivisi']))
        {
            $datas->where('divisi_id', $req['sDivisi']);
        }
        
        return  Datatables::of($datas)
                ->addColumn('keterangan', function($datas)
                {
                    $ret = [];
                    foreach($datas->salary as $k => $v)
                    {
                        $ret[$v->nama] = $v->deskripsi;
                    }
                    
                    if(count($ret)>0)
                    {
                        return implode(', ', $ret);
                    }
                    return '';
                })
                ->make(true);
    }
}
