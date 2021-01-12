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
}
