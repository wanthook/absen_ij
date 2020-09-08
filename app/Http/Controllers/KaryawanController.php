<?php

namespace App\Http\Controllers;

use App\Karyawan as Karyawan;
use App\KaryawanAsuransi as KaryawanAsuransi;
use App\KaryawanKeluarga as KaryawanKeluarga;
use App\KaryawanPendidikan as KaryawanPendidikan;
use App\Jadwal;
use App\Perusahaan;
use App\JamKerja;

use App\MasterOption as MasterOption;
use App\Jabatan;
use App\Divisi;
use App\Alasan;
use App\ExceptionLog;

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

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.karyawan.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAlasan()
    {
        return view('admin.transaksi.alasan.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexStatusKaryawan()
    {
        return view('admin.transaksi.status_karyawan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $var = new Karyawan;
        return view('admin.karyawan.form', ['var' => $var]);
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
                    'pin'   => 'required',
                    'nik'   => 'required',
                    'perusahaan_id'      => 'required',
                    'divisi_id'      => 'required',
                    'jabatan_id'      => 'required',
                    'status_karyawan_id'      => 'required',
                    'tanggal_masuk'      => 'required',
                    'tanggal_probation'      => 'required_if:status_karyawan_id,372',
                    'tanggal_kontrak'      => 'required_if:status_karyawan_id,373',
                ],
                [
                    'pin.required'  => 'PIN harus diisi.',
                    'nik.required'     => 'NIK harus diisi.',
                    'perusahaan_id.required'     => 'Perusahaan harus diisi.',
                    'divisi_id.required'     => 'Divisi harus diisi.',
                    'jabatan_id.required'     => 'Jabatan harus diisi.',
                    'status_karyawan_id.required'     => 'Status Karyawan harus diisi.',
                    'tanggal_masuk.required'     => 'Tanggal Masuk harus diisi.',
                    'tanggal_probation.required_if'     => 'Tanggal Percobaan harus diisi.',
                    'tanggal_kontrak.required_if'     => 'Tanggal Kontrak harus diisi.',
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
                
                if(empty($req['id']))
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    $req['created_by']   = Auth::user()->id;
                    $req['created_at']   = Carbon::now();
                    
                    $save = Karyawan::create($req);
                    
                    KaryawanKeluarga::whereNull('karyawan_id')
                            ->where('created_by', Auth::user()->id)->update(['karyawan_id' => $save->id]);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    KaryawanKeluarga::find($req['id'])->fill($req)->save();
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil diubah'
                    ));
                    
                }
            }
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'.$er->getMessage()
            ));
        }
    }
    
    public function storeUpload(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadKaryawan');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadKaryawan');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                
//                $fileStorage = fopen(storage_path('tmp').'/tempFileUploadKaryawan','r');
                
                $x = 0;    
                $arrKey = null;
                
                foreach($sheetData as $csv)
                {                    
                    if(empty($csv[0]))
                    {
                        break;
                    }
                    if($x == 0)
                    {
                        foreach($csv as $k => $v)
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
                    
                    $karyawan = Karyawan::where('pin',$csv[$arrKey->pin])->first();
                    if(!$karyawan)
                    {
                        $row = array();
                        $row['pin'] = $csv[$arrKey->pin];
                        
                        if(!empty($csv[$arrKey->rekening]))
                        {
                            $row['rekening'] = trim($csv[$arrKey->rekening]);
                        }
                        
                        if(!empty($csv[$arrKey->npwp]))
                        {
                            $row['npwp'] = trim($csv[$arrKey->npwp]);
                        }                        
                        
                        $perusahaan = Perusahaan::where('pin_min', '<=', $csv[$arrKey->pin])->where('pin_max', '>=', $csv[$arrKey->pin])->first();
                        if($perusahaan)
                        {
                            $row['perusahaan_id'] = $perusahaan->id;
                        }
                        else
                        {
                            continue;
                        }
                        $row['key'] = trim($csv[$arrKey->key]);
                        $row['nik'] = trim($csv[$arrKey->kode]);
                        $row['nama'] = trim($csv[$arrKey->nama]);
                        $row['ktp'] = trim($csv[$arrKey->ktp]);
                        
                        if(!empty(trim($csv[$arrKey->tanggal_lahir])))
                        {
                            $row['tanggal_lahir'] = trim($csv[$arrKey->tanggal_lahir]);
                        }
                        
                        $row['hp'] = trim($csv[$arrKey->hp]);
                        $row['kota'] = trim($csv[$arrKey->kota]);
                        $row['kode_pos'] = trim($csv[$arrKey->kode_pos]);
                        $row['alamat'] = trim($csv[$arrKey->alamat]);
                        
                        if(!empty($csv[$arrKey->tanggal_status]))
                        {
                            $row['tanggal_masuk'] = trim($csv[$arrKey->tanggal_status]);
                        }
                        
                        $status = MasterOption::where('kode','STATUS')->where('nama', trim($csv[$arrKey->status_karyawan]))->first();
                        if($status)
                        {
                            $row['status_karyawan_id'] = $status->id;
                        }
                        else
                        {
                            ExceptionLog::create(['file_target' => 'KaryawanController.php', 'message_log' => json_encode(['PIN' => $csv[$arrKey->pin], 
                            'message' => 'status_karyawan_id kosong']), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                            continue;
                        }
                        
                        $status = MasterOption::where('kode','JENKEL')->where('nama', trim($csv[$arrKey->jenis_kelamin]))->first();
                        if($status)
                        {
                            $row['jenis_kelamin_id'] = $status->id;
                        }
                        
                        $jabatan = Jabatan::where('kode', trim($csv[$arrKey->kode_jabatan]))->first();
                        if($jabatan)
                        {
                            $row['jabatan_id'] = $jabatan->id;
                        }
                        
                        $divisi = Divisi::where('kode', trim($csv[$arrKey->kode_divisi]))->first();
                        if($divisi)
                        {      
                            $row['divisi_id'] = $divisi->id;
                        }
                        
                        
                        if(!empty(trim($csv[$arrKey->tanggal_kontrak])))
                        {
                            $row['tanggal_kontrak'] = trim($csv[$arrKey->tanggal_kontrak]);
                        }
                        
                        if(!empty(trim($csv[$arrKey->active_status])))
                        {
                            $row['active_status'] = (int)trim($csv[$arrKey->active_status]);
                            
                            if($row['active_status'] > 1)
                            {
                                if(!empty($csv[$arrKey->active_status_date]))
                                {
                                    $row['active_status_date'] = $csv[$arrKey->active_status_date];
                                }
                                
                                if(!empty($csv[$arrKey->active_comment]))
                                {
                                    $row['active_comment'] = $csv[$arrKey->active_comment];
                                }
                            }
                        }
                        
                        $row['updated_by']   = Auth::user()->id;  
                        $row['created_by']   = Auth::user()->id;

                        $kar = Karyawan::create($row);
                        if($kar->id)
                        {
                            $jadwal = Jadwal::where('kode',trim($csv[$arrKey->kode_jadwal]))->first();
                            $tanggalJadwal = trim($csv[$arrKey->tanggal_jadwal]);
                            if($jadwal && $tanggalJadwal)
                            {
                                Karyawan::find($kar->id)->jadwals()->attach($jadwal->id, ['tanggal' => $tanggalJadwal, 'keterangan' => 'Upload Karyawan']);
                            }
                        }
                    }
                    else
                    {
                        $row = array();
                        
                        $row['pin'] = $csv[$arrKey->pin];
                        
                        $perusahaan = Perusahaan::where('pin_min', '<=', $csv[$arrKey->pin])->where('pin_max', '>=', $csv[$arrKey->pin])->first();
                        if($perusahaan)
                        {
                            $row['perusahaan_id'] = $perusahaan->id;
                        }
                        else
                        {
                            ExceptionLog::create(['file_target' => 'KaryawanController.php', 'message_log' => json_encode(['PIN' => $csv[$arrKey->pin], 
                            'message' => 'perusahaan_id kosong']), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                            continue;
                        }
                        $row['key'] = trim($csv[$arrKey->key]);
                        $row['nik'] = trim($csv[$arrKey->kode]);
                        $row['nama'] = trim($csv[$arrKey->nama]);
                        $row['ktp'] = trim($csv[$arrKey->ktp]);
                        
                        if(!empty(trim($csv[$arrKey->tanggal_lahir])))
                        {
                            $row['tanggal_lahir'] = trim($csv[$arrKey->tanggal_lahir]);
                        }
                        
                        $row['hp'] = trim($csv[$arrKey->hp]);
                        $row['kota'] = trim($csv[$arrKey->kota]);
                        $row['kode_pos'] = trim($csv[$arrKey->kode_pos]);
                        $row['alamat'] = trim($csv[$arrKey->alamat]);
                        
                        if(!empty($csv[$arrKey->tanggal_status]))
                        {
                            $row['tanggal_masuk'] = trim($csv[$arrKey->tanggal_status]);
                        }
                        
                        $status = MasterOption::where('kode','STATUS')->where('nama', trim($csv[$arrKey->status_karyawan]))->first();
                        if($status)
                        {
                            $row['status_karyawan_id'] = $status->id;
                        }
                        else
                        {
                            ExceptionLog::create(['file_target' => 'KaryawanController.php', 'message_log' => json_encode(['PIN' => $csv[$arrKey->pin], 
                            'message' => 'status_karyawan_id kosong']), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                            continue;
                        }
                         
                       $status = MasterOption::where('kode','JENKEL')->where('nama', trim($csv[$arrKey->jenis_kelamin]))->first();
                        if($status)
                        {
                            $row['jenis_kelamin_id'] = $status->id;
                        }
                        
                        $jabatan = Jabatan::where('kode', trim($csv[$arrKey->kode_jabatan]))->first();
                        if($jabatan)
                        {
                            $row['jabatan_id'] = $jabatan->id;
                        }
                        
                        $divisi = Divisi::where('kode', trim($csv[$arrKey->kode_divisi]))->first();
                        if($divisi)
                        {      
                            $row['divisi_id'] = $divisi->id;
                        }
                        
                        $jadwal = Jadwal::where('kode',trim($csv[$arrKey->kode_jadwal]))->first();
                        $tanggalJadwal = trim($csv[$arrKey->tanggal_jadwal]);
                        if($jadwal && $tanggalJadwal)
                        {
                            Karyawan::find($kar->id)->jadwals()->attach($jadwal->id, ['tanggal' => $tanggalJadwal, 'keterangan' => 'Upload Karyawan']);
                        }
                        
                        if(!empty(trim($csv[$arrKey->tanggal_kontrak])))
                        {
                            $row['tanggal_kontrak'] = trim($csv[$arrKey->tanggal_kontrak]);
                        }
                        
                        if(!empty(trim($csv[$arrKey->active_status])))
                        {
                            $row['active_status'] = (int)trim($csv[$arrKey->active_status]);
                            
                            if($row['active_status'] > 1)
                            {
                                if(!empty($csv[$arrKey->active_status_date]))
                                {
                                    $row['active_status_date'] = $csv[$arrKey->active_status_date];
                                }
                                
                                if(!empty($csv[$arrKey->active_comment]))
                                {
                                    $row['active_comment'] = $csv[$arrKey->active_comment];
                                }
                            }
                        }
                        
                        $row['updated_by']   = Auth::user()->id;  
                        
                        Karyawan::find($karyawan->id)->fill($row)->save();
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

    public function manualStore(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'karId'   => 'required',
//                'jamId'   => 'required',
                'tgl'   => 'required',
            ],
            [
                'karId.required'  => 'Karyawan harus dipilih.',
//                'jamId.required'  => 'Jam Kerja harus dipilih.',
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

                $req['updated_by']   = Auth::user()->id;        
                $req['updated_at']   = Carbon::now();

                $jd = Karyawan::find($req['karId']);
                
                $par = $jd->jadwal_manual()->wherePivot('tanggal', $req['tgl']);
                
//                dd($par->detach());
//                
                if($jd->jadwal_manual()->wherePivot('tanggal', $req['tgl']))
                {
                    $par->detach();
                }
                
                if($req['jamId'])
                {
                    $jd->jadwal_manual()->attach($req['jamId'],['tanggal' => $req['tgl'], 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
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
    
    public function storeUploadAlasan(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadAlasanKaryawan');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadAlasanKaryawan');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
//                $fileStorage = fopen(storage_path('tmp').'/tempFileUploadAlasanKaryawan','r');
                
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
                    
                    $dS = array();
                    
                    $karyawan = Karyawan::where('pin',trim($sD[$arrKey->pin]));
                    
                    if($karyawan->count())
                    {
                        if(!empty(trim($sD[$arrKey->alasan])))
                        {
                            $karId = $karyawan->first()->id;
                            $kar = Karyawan::find($karId);
                            $par = $kar->alasan()->wherePivot('tanggal', trim($sD[$arrKey->tanggal]));
                            $alasan = Alasan::where('kode', trim($sD[$arrKey->alasan]))->first();
//                            dd($alasan);
                            if($par)
                            {
                                $par->detach($alasan->id);
                            }
                            
                            $attach = ['tanggal' => trim($sD[$arrKey->tanggal]), 'created_by' => Auth::user()->id];
                    
                            if(trim($sD[$arrKey->waktu]))
                            {
                                $attach['waktu'] = trim($sD[$arrKey->waktu]);
                            }
                            if(trim($sD[$arrKey->keterangan]))
                            {
                                $attach['keterangan'] = trim($sD[$arrKey->keterangan]);
                            }
                            
                            $kar->alasan()->attach($alasan->id, $attach);
                        }
                        else
                        {
                            continue;
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
    
    public function storeUploadJadwal(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadJadwalKaryawan');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadJadwalKaryawan');
                
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
                    
                    if($karyawan->count())
                    {
                        
                        $karId = $karyawan->first()->id;
                        $kar = Karyawan::find($karId);
                        $par = $kar->jadwals()->wherePivot('tanggal', trim($sD[$arrKey->tanggal]));
                        $jadwal = Jadwal::where('kode', trim($sD[$arrKey->kode]))->first();
//                            dd($par);
                        if($par)
                        {
                            $par->detach();
                        }

                        $attach = ['tanggal' => trim($sD[$arrKey->tanggal])];

                        $kar->jadwals()->attach($jadwal->id, $attach);

                        $kar->fill(['updated_by' => Auth::user()->id ,'updated_at' => Carbon::now()])->save();
                        
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
    
    public function storeUploadJadwalManual(Request $request)
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
                
                
                $sheetData = [];
                $fileVar->move(storage_path('tmp'),'tempFileUploadJadwalManualKaryawan');
                
                if($fileVar->getClientMimeType() == 'text/csv')
                {
                    $fileStorage = fopen(storage_path('tmp').'/tempFileUploadJadwalManualKaryawan','r');
                    while(! feof($fileStorage))
                    {
                        $csv = fgetcsv($fileStorage, 1024, "\t");
//                        dd($csv);
                        $sheetData[] = $csv;
                    }
                }
                else
                {
                    $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadJadwalManualKaryawan');

                    $sheetData = $spreadsheet->getActiveSheet()->toArray();
                }
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
                        $par = $kar->jadwal_manual()->wherePivot('tanggal', trim($sD[$arrKey->tanggal]));
                        $jadwal = JamKerja::where('kode', trim($sD[$arrKey->kode_jam]))->first();

                        if($par)
                        {
                            $par->detach();
                        }

                        $attach = ['tanggal' => trim($sD[$arrKey->tanggal]), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()];

                        $kar->jadwal_manual()->attach($jadwal->id, $attach);
                        
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeKeluarga(Request $request)
    {
        
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'kel_nama'   => 'required',
                    'kel_relasi_id'   => 'required',
                    'kel_telpon'      => 'required',
                ],
                [
                    'kel_nama.required'  => 'Nama harus diisi.',
                    'kel_relasi_id.required'     => 'Relasi harus diisi.',
                    'kel_telpon.required'     => 'Telpon harus diisi.',
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
                
                $req['nama']            = $req['kel_nama']; unset($req['kel_nama']);
                $req['ktp']             = $req['kel_ktp']; unset($req['kel_ktp']);
                $req['relasi_id']       = $req['kel_relasi_id']; unset($req['kel_relasi_id']);
                $req['tempat_lahir']    = $req['kel_tempat_lahir']; unset($req['kel_tempat_lahir']);
                $req['tanggal_lahir']    = $req['kel_tanggal_lahir']; unset($req['kel_tempat_lahir']);
                $req['telpon']          = $req['kel_telpon']; unset($req['kel_telpon']);
                $req['alamat']          = $req['kel_alamat']; unset($req['kel_alamat']);
                $req['kota']            = $req['kel_kota']; unset($req['kel_kota']);
                $req['kode_pos']        = $req['kel_kode_pos']; unset($req['kel_kode_pos']);
                
                
                if(empty($req['kel_id']))
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    $req['created_by']   = Auth::user()->id;
                    $req['created_at']   = Carbon::now();
                    
                    KaryawanKeluarga::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    KaryawanKeluarga::find($req['kel_id'])->fill($req)->save();
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil diubah'
                    ));
                    
                }
            }
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'.$er->getMessage()
            ));
        }
    }

    public function storeAlasanKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sKar'   => 'required',
                'sAlasan'   => 'required',
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sKar.required'  => 'Karyawan harus dipilih.',
                'sAlasan.required'  => 'Alasan harus dipilih.'
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
                
                $tglA = Carbon::createFromFormat("Y-m-d", $req['sTanggal']);
                $tglB = Carbon::createFromFormat("Y-m-d", $req['sTanggal']);

                $jd = Karyawan::find($req['sKar']);
                
                $al = Alasan::find($req['sAlasan']);
                
                if($al->kode == 'H2' || $al->kode == 'SD' || $al->kode == 'SKK')
                {
                    if($req['sTanggalAkhir'])
                    {
                        $tglB = Carbon::createFromFormat("Y-m-d", $req['sTanggalAkhir']);
                    }
                }
//                dd($tglA->toDateString());
                $tglPer = CarbonPeriod::create($tglA->toDateString(), $tglB->toDateString())->toArray();
//                dd($tglPer);
                foreach($tglPer as $vTgl)
                {
                    $par = $jd->alasan()->wherePivot('tanggal', $vTgl);
//                
                    if($par)
                    {
                        $par->detach($req['sAlasan']);
                    }

                    if($req['sAlasan'])
                    {
                        $attach = ['tanggal' => $vTgl, 'keterangan' => $req['sKeterangan'], 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()];

                        if($req['sWaktu'])
                        {
                            $attach['waktu'] = $req['sWaktu'];
                        }

                        $jd->alasan()->attach($req['sAlasan'], $attach);
                    }
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

    public function storeJadwalKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sJadwal'   => 'required',
                'sKar'   => 'required',
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sJadwal.required'  => 'Jadwal harus dipilih.',
                'sKar.required'  => 'Karyawan harus dipilih.',
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
                
                $karyawan = Karyawan::find($req['sKar']);
                
                if($karyawan->id)
                {
                    $tgl = Carbon::createFromFormat('Y-m-d', $req['sTanggal']);
                    $jad = $karyawan->jadwals()->wherePivot('tanggal', $tgl->toDateString());
                    
                    if($jad)
                    {
                        $jad->detach();
                    }
                    
                    $karyawan->jadwals()->attach($req['sJadwal'], ['tanggal' => $req['sTanggal'], 'keterangan' => $req['sKeterangan']]);
                    
                    $karyawan->fill(['updated_by' => Auth::user()->id ,'updated_at' => Carbon::now()])->save();
                    
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

    public function storeJadwalManualKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sJadwal'   => 'required',
                'sKar'   => 'required',
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sJadwal.required'  => 'Jadwal harus dipilih.',
                'sKar.required'  => 'Karyawan harus dipilih.',
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
                
                $karyawan = Karyawan::find($req['sKar']);
                
                if($karyawan->id)
                {
                    $tgl = Carbon::createFromFormat('Y-m-d', $req['sTanggal']);
                    $jad = $karyawan->jadwals()->wherePivot('tanggal', $tgl->toDateString());
                    
                    if($jad)
                    {
                        $jad->detach();
                    }
                    
                    $karyawan->jadwals()->attach($req['sJadwal'], ['tanggal' => $req['sTanggal'], 'keterangan' => $req['sKeterangan']]);
                    
                    $karyawan->fill(['updated_by' => Auth::user()->id ,'updated_at' => Carbon::now()])->save();
                    
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

    public function storeStatusKaryawan($kode, Request $request)
    {
        try
        {
//            echo $kode;
            if($kode == 'tambah')
            {
                $validation = Validator::make($request->all(), 
                [
                    'sTanggal'   => 'required',
                    'sKar'   => 'required',
                    'sKeterangan'   => 'required',
                ],
                [
                    'sTanggal.required'  => 'Tanggal harus diisi.',
                    'sKar.required'  => 'Karyawan harus dipilih.',
                    'sKeterangan.required'  => 'Keterangan harus dipilih.'
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

                    $jd = Karyawan::find($req['sKar']);

                    if($req['sKeterangan'])
                    {
                        $jd->active_status = 2;
                        $jd->active_comment = $req['sKeterangan'];
                        $jd->active_status_date = $req['sTanggal'];
                        
                        
                        $jd->updated_by = Auth::user()->id;
                        $jd->updated_at = Carbon::now();
                        
                        $jd->save();

                        echo json_encode(array(
                            'status' => 1,
                            'msg'   => 'Data berhasil diubah'
                        ));
                    }
                    else
                    {
                        echo json_encode(array(
                            'status' => 0,
                            'msg'   => 'Data gagal diubah'
                        ));
                    }

                }
            }
            else if($kode == 'kembali')
            {
                $validation = Validator::make($request->all(), 
                [
                    'sKar'   => 'required',
                ],
                [
                    'sKar.required'  => 'Karyawan harus dipilih.',
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

                    $jd = Karyawan::find($req['sKar']);
                    
                    $jd->active_status = 1;
                    $jd->active_comment = null;
                    $jd->active_status_date = null;
                    
                    $jd->updated_by = Auth::user()->id;
                    $jd->updated_at = Carbon::now();
                    
                    $jd->save();

                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil diubah'
                    ));
                }
            }
            else if($kode == 'nonaktif')
            {
                $validation = Validator::make($request->all(), 
                [
                    'sKar'   => 'required',
                ],
                [
                    'sKar.required'  => 'Karyawan harus dipilih.',
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

                    $jd = Karyawan::find($req['sKar']);
                    
                    $jd->active_status = 3;
                    $jd->updated_by = Auth::user()->id;
                    $jd->updated_at = Carbon::now();
//                    $jd->active_status_date = Carbon::now()->toDateString();

                    $jd->save();

                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil diubah'
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Karyawan  $karyawan
     * @return \Illuminate\Http\Response
     */
    public function show(Karyawan $karyawan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Karyawan  $karyawan
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $var = Karyawan::find($id);
        
        return view('admin.karyawan.form', ['var' => $var]); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Karyawan  $karyawan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        //
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
            Karyawan::find($req['id'])->delete();
            
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Karyawan  $Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroyAlasanKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sAlasan'   => 'required',
                'sKar'   => 'required'
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sAlasan.required'  => 'Alasan harus diisi.',
                'sKar.required'  => 'Karyawan harus dipilih.'
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

                $jd = Karyawan::find($req['sKar']);
                
                $par = $jd->alasan()->wherePivot('tanggal', $req['sTanggal']);
                
//                dd($par->detach());
//                
                if($jd->alasan()->wherePivot('tanggal', $req['sTanggal']))
                {
                    $par->detach($req['sAlasan']);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Karyawan  $Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroyJadwalKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sKar'   => 'required'
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sKar.required'  => 'Karyawan harus dipilih.'
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

                $jd = Karyawan::find($req['sKar']);
                
                $par = $jd->jadwals()->wherePivot('tanggal', $req['sTanggal']);
                
//                dd($par->detach());
//                
                if($par)
                {
                    $par->detach();
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Karyawan  $Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroyKeluarga(Request $request)
    {
        $req = $request->all();
        try 
        {
            KaryawanKeluarga::find($req['id'])->delete();
            
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
        
        $datas   = Karyawan::with(['jabatan', 'divisi', 'perusahaan', 'status', 'jadwals','createdBy'])->author();  
        
        if(!empty($req['sNama']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('pin', 'like', '%'.$req['sNama'].'%')
                    ->orWhere('key', 'like', '%'.$req['sNama'].'%')
                    ->orWhere('nama', 'like', '%'.$req['sNama'].'%')
                    ->orWhere('nik', 'like', '%'.$req['sNama'].'%');
            });
        }
        
        if(!empty($req['sJabatan']))
        {
            $datas->where('jabatan_id', $req['sJabatan']);
        }
        if(!empty($req['sDivisi']))
        {
            $datas->where('divisi_id', $req['sDivisi']);
        }
        if(!empty($req['sStatus']))
        {
            $datas->where('status_karyawan_id', $req['sStatus']);
        }
        if(!empty($req['sPerusahaan']))
        {
            $datas->where('perusahaan_id', $req['sPerusahaan']);
        }
                
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<a class="editrow btn btn-primary btn-xs" href="'.route('mkaryawane',$datas->id).'" title="Ubah"><i class="fas fa-pencil-alt"></i></a>';
                    $str    .= '<button class="delrow btn btn-danger btn-xs" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';
                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function dtStatus(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Karyawan::with(['jabatan', 'divisi', 'perusahaan', 'status', 'jadwal','createdBy']);  
        
        if(!empty($req['sPin']))
        {
            $datas->where('pin', $req['sPin']);
        }
        
        if(!empty($req['sTanggal']))
        {
            $datas->where('active_status_date', $req['sTanggal']);
        }
        
        $datas->whereIn('active_status', [2,3])->author();
        
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<a class="editrow btn btn-primary btn-xs" href="'.route('mkaryawane',$datas->id).'" title="Ubah"><i class="fas fa-pencil-alt"></i></a>';
                    $str    .= '<button class="delrow btn btn-danger btn-xs" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';
                    return $str;
                })
                ->setRowClass(function ($datas)
                {
                    $ret = '';
                    switch($datas->active_status)
                    {
                        case 3:
                            $ret = 'danger alert-danger';
                            break;
                        case 2:
                            $ret = 'warning alert-warning';
                            break;
                        case 1:
                            $ret = 'success alert-success';
                            break;
                        
                    }
                    
                    return $ret;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function dtTransaksiAlasan(Request $request)
    {
        $req    = $request->all();
              
        
        $datas = DB::table('alasan_karyawan')
                  ->selectRaw('alasan_karyawan.tanggal as tanggal, alasan_karyawan.alasan_id as alasan_id, alasan_karyawan.waktu as waktu, alasan_karyawan.keterangan as keterangan, karyawans.id as karyawan_id, karyawans.pin as pin, karyawans.nik as nik, karyawans.nama as nama, divisis.kode as divisi_kode, divisis.deskripsi as divisi_deskripsi, alasans.kode as alasan_kode, alasans.deskripsi as alasan_deskripsi')
                  ->join('karyawans', 'karyawans.id', '=', 'alasan_karyawan.karyawan_id')
                  ->join('alasans', 'alasans.id', '=', 'alasan_karyawan.alasan_id')
                  ->join('divisis', 'divisis.id', '=', 'karyawans.divisi_id');
        
        if(isset($req['sTanggal']))
        {
            $datas->where('alasan_karyawan.tanggal',$req['sTanggal']);
        }
        
        if(Auth::user()->type->nama == 'REKANAN')
        {
            $datas->where('karyawans.perusahaan_id', Auth::user()->perusahaan_id);
        }
        
        if(isset($req['perusahaan']))
        {
            $datas->where('karyawans.perusahaan_id', $req['perusahaan']);
        }
        
        $datas->orderBy('alasan_karyawan.created_at', 'desc');        
                
        return  Datatables::of($datas)
                ->make(true);
    }
    
    public function dtJadwal(Request $request)
    {
        $req    = $request->all();
                        
        $datas = Karyawan::with(['jadwals' => function($q){
            $q->orderBy('tanggal', 'desc');
        },'divisi'])->author()->KaryawanAktif()->orderBy('updated_at', 'desc');        
        
        if(isset($req['sKar']))
        {
            $datas->where('id', $req['sKar']);
        }
        
        return  Datatables::of($datas)
                ->make(true);
    }
    
    public function dtKel(Request $request)
    {
        $req    = $request->all();
        
        $datas   = KaryawanKeluarga::with(['relasi', 'createdBy'])->where('karyawan_id', $req['karyawan_id']) ;
        
        if(Auth::user()->type == 'ADMIN')
        {
            $datas->where('created_by', Auth::user()->id);
        }
        
//        if(!empty($req['search']))
//        {
//            $datas->where(function($q) use($req)
//            {
//                $q->where('nama', $req['search']);
//                $q->orWhere('deskripsi', $req['search']);
//            });
//        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrowkel btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-form-keluarga" title="Ubah"><i class="fas fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrowkel btn btn-danger btn-sm" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';
                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function select2(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = Karyawan::author()->where(function($q) use($term)
        {
            $q->where('pin','like','%'.$term.'%')
              ->orWhere('nik','like','%'.$term.'%')
              ->orWhere('nama','like','%'.$term.'%')
              ->orWhere('id',$term);
        });
        
        if(!$request->input('t'))
        {
            $tags = $tags->where('active_status',1);
        }
        
        $formatted_tags = [];
        foreach ($tags->limit(20)->get() as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->pin.' - '.$tag->nama];
        }
        
        echo json_encode(array('items' => $formatted_tags));
    }
}
