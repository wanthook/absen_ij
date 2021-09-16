<?php

namespace App\Http\Controllers;

use App\Karyawan;
use App\Prosesabsen;
use App\MasterOption as MasterOption;
use App\Jadwal;
use App\Perusahaan;
use App\JamKerja;
use App\Jabatan;
use App\Divisi;
use App\Alasan;
use App\ExceptionLog;
use App\Prosesgaji;
use App\Prosesgajiedit;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;
use DB;
use Illuminate\Support\Facades\Cache;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Settings;

use App\Http\Traits\TraitGaji;

class ProsesgajiController extends Controller
{
    use TraitGaji;
    
    private $gajiPokok = 0;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payroll.proses_gaji.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexEdit()
    {
        return view('payroll.update_proses.form');
    }

    public function setGajiPokok($nilai)
    {
        $this->gajiPokok = $nilai;
    }
    
    public function proses(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'tanggal'   => 'required'
                ],
                [
                    'tanggal.required'  => 'Kode harus diisi.'
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
                $req = $request->all();$karyawanId = array();
                $tanggal = null;

                $karyawan = Karyawan::KaryawanAktif();

                if(isset($req['pin']))
                {
                    $karyawan->where('id', $req['pin']);
                }            
                else if(isset($req['divisi']))
                {
                    $karyawan->where('divisi_id', $req['divisi']);
                }
                
                $tgl = Carbon::createFromFormat('Y-m-d' ,$req['tanggal'].'-21');
                
                $periode = CarbonPeriod::create($tgl->copy()->subMonth()->addDay(), $tgl)->toArray();
                
                $karyawan->chunk(100, function($kar) use($periode, $req)
                {
                    foreach($kar as $rKar)
                    {
                        foreach($this->prosesGaji($rKar->id, $periode) as $dt)
                        {
                            $proses = Prosesgaji::where('periode_awal', $dt['periode_awal'])
                                                ->where('periode_akhir', $dt['periode_akhir'])
                                                ->where('karyawan_id', $rKar->id);
                            if($proses->count())
                            {
                                $dt = array_merge($dt, ['updated_by' => Auth::user()->id]);
                                $proses->update($dt);
                            }
                            else
                            {
                                $dt = array_merge($dt, ['created_by' => Auth::user()->id,'updated_by' => Auth::user()->id]);
                                Prosesgaji::create($dt);
                            }
                        }
                    }
                });
                echo json_encode(array(
                    'status' => 1,
                    'msg'   => "Data Berhasil disimpan."
                ));
            }
        }
        catch(Exception $e)
        {
            $err = array('file_target' => 'ProsesgajiController.php',
                         'message_log' => json_encode($e->getMessage()),
                         'created_by' => Auth::user()->id);
            
            ExceptionLog::create($err);
            
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal diproses'
                ));
        }
    }

    public function prosesEdit(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'periode'   => 'required',
                    'pin'   => 'required'
                ],
                [
                    'periode.required'  => 'Periode harus diisi.',
                    'pin.required'  => 'PIN harus diisi.'
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
                $tglAwal = Carbon::createFromFormat('Y-m-d', $req['periode'].'-22')->subMonth();

                $tglAkhir = $tglAwal->copy()->addMonth(1)->subDay(1);

                if(isset($req['pin']))
                {
                    $proses = Prosesgaji::with('karyawan', 'editlist')
                    ->where('id', $req['id'])->first();
                                // ->where('periode_awal', $tglAwal->toDateString())
                                // ->where('periode_akhir', $tglAkhir->toDateString())
                                // ->where('karyawan_id', $req['pin'])->first();
                    // dd($proses);
                    if($proses)
                    {
                        $req['prosesgaji_id'] = $proses->id;
                        $req['created_by'] = Auth::user()->id;
                        $req['updated_by'] = Auth::user()->id;
                        // dd($req);
                        // $req = array_merge($req, ['prosesgaji_id' => $proses->id,'created_by' => Auth::user()->id,'updated_by' => Auth::user()->id]);
                        // if($proses->editlist->first())
                        // {                            
                        //     Prosesgajiedit::where('prosesgaji_id', $req['id'])->delete();
                        // }

                        Prosesgajiedit::create($req);

                        echo json_encode(array(
                            'status' => 1,
                            'msg'   => 'Data berhasil disimpan'
                        ));
                    }
                    else
                    {
                        echo json_encode(array(
                            'status' => 0,
                            'msg'   => 'Data proses tidak ditemukan'
                        ));
                    }
                }
                else
                {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
            }
        }
        catch(Exception $e)
        {
            $err = array('file_target' => 'ProsesgajiController.php',
                         'message_log' => json_encode($e->getMessage()),
                         'created_by' => Auth::user()->id);
            
            ExceptionLog::create($err);
            
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal diproses'
                ));
        }
    }

    public function getProses(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'periode'   => 'required',
                    'pin'       => 'required'
                ],
                [
                    'periode.required'  => 'Periode harus diisi.',
                    'pin.required'  => 'PIN harus diisi.'
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
                $req = $request->only(['pin', 'periode']);
                
                $tglAwal = Carbon::createFromFormat('Y-m-d', $req['periode'].'-22')->subMonth();

                $tglAkhir = $tglAwal->copy()->addMonth(1)->subDay(1);

                if(isset($req['pin']))
                {
                
                    $karyawan = Karyawan::find($req['pin']);

                    if($karyawan)
                    {
                        $proses = Prosesgaji::with('karyawan', 'editlistlast')
                        ->where('karyawan_id', $req['pin'])
                        ->where('periode_awal', $tglAwal->toDateString())
                        ->where('periode_akhir', $tglAkhir->toDateString())
                        ->first();
                        // dd($proses);
                        echo json_encode(array(
                            'status' => 1,
                            'msg'   => $proses
                        ));
                    }
                    else
                    {
                        echo json_encode(array(
                            'status' => 0,
                            'msg'   => 'Data tidak ditemukan'
                        ));
                    }
                }
                else
                {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'   => 'Data tidak ditemukan'
                    ));
                }
                // Prosesgaji::
            }
        }        
        catch(Exception $e)
        {
            $err = array('file_target' => 'ProsesgajiController.php',
                         'message_log' => json_encode($e->getMessage()),
                         'created_by' => Auth::user()->id);
            
            ExceptionLog::create($err);
            
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal diproses'
                ));
        }
    }
}
