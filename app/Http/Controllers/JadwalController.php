<?php

namespace App\Http\Controllers;

use App\Jadwal;
use App\JadwalDetail;
use App\JamKerja;
use App\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use JShrink;

use Auth;
use Validator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dayshift()
    {
        // $jss = JShrink\Minifier::minify();
        return view('admin.jadwal_kerja.dsindex');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function shift()
    {
        // $jss = JShrink\Minifier::minify();
        return view('admin.jadwal_kerja.shindex');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function setjadwal()
    {
        // $jss = JShrink\Minifier::minify();
        return view('admin.jadwal_kerja.setindex');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function jadwalmanual()
    {
        // $jss = JShrink\Minifier::minify();
        return view('admin.jadwal_kerja.setmanual');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createsetjadwal($id)
    {
        // $jss = JShrink\Minifier::minify();
        $var = Jadwal::find($id);
        return view('admin.jadwal_kerja.setcreate', ['var' => $var]);
    }

    public function dayshiftStore(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'kode'   => 'required',
            ],
            [
                'kode.required'  => 'Kode harus diisi.',
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
                    $req['tipe']        = 'D'; 
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    $req['created_by']   = Auth::user()->id;
                    $req['created_at']   = Carbon::now();
                    
                    $ress = Jadwal::create($req);
                    
                    // $jd = Jadwal::find($ress->id);
                    // $jd->jadwal_kerja()->detach();

                    if($ress->id > 0)
                    {
                        $jd = Jadwal::find($ress->id);
                        
                        if(!empty($req['senin']))
                        {
                            $jd->jadwal_kerja()->attach($req['senin'],['day' => 1, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['selasa']))
                        {
                            $jd->jadwal_kerja()->attach($req['selasa'],['day' => 2, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['rabu']))
                        {
                            $jd->jadwal_kerja()->attach($req['rabu'],['day' => 3, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['kamis']))
                        {
                            $jd->jadwal_kerja()->attach($req['kamis'],['day' => 4, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['jumat']))
                        {
                            $jd->jadwal_kerja()->attach($req['jumat'],['day' => 5, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['sabtu']))
                        {
                            $jd->jadwal_kerja()->attach($req['sabtu'],['day' => 6, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                        if(!empty($req['minggu']))
                        {
                            $jd->jadwal_kerja()->attach($req['minggu'],['day' => 7, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                    }
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();

                    Jadwal::find($req['id'])->fill($req)->save();
                    
                    $jd = Jadwal::find($req['id']);
                    $jd->jadwal_kerja()->detach();

                    if(!empty($req['senin']))
                    {
                        $jd->jadwal_kerja()->attach($req['senin'],['day' => 1, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['selasa']))
                    {
                        $jd->jadwal_kerja()->attach($req['selasa'],['day' => 2, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['rabu']))
                    {
                        $jd->jadwal_kerja()->attach($req['rabu'],['day' => 3, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['kamis']))
                    {
                        $jd->jadwal_kerja()->attach($req['kamis'],['day' => 4, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['jumat']))
                    {
                        $jd->jadwal_kerja()->attach($req['jumat'],['day' => 5, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['sabtu']))
                    {
                        $jd->jadwal_kerja()->attach($req['sabtu'],['day' => 6, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    if(!empty($req['minggu']))
                    {
                        $jd->jadwal_kerja()->attach($req['minggu'],['day' => 7, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }

                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil diubah'
                    ));
                    
                }
            }
        }
        catch (QueryException $er)
        {
            // echo print_r($er->getMessage());
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }
    
    public function dayshiftUpload(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadDayshift');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadDayshift');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                
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
                    
                    $jadwal = Jadwal::where('tipe','D')->where('kode',$csv[$arrKey->kode]);
//                    echo $csv[$arrKey->kode].',';
                    if($jadwal->count() == 0)
                    {
                        $row = array();
                        $row['kode'] = strtoupper($csv[$arrKey->kode]);
                        $row['deskripsi'] = strtoupper($csv[$arrKey->deskripsi]);
                        $row['tipe']        = 'D'; 
                        $row['updated_by']   = Auth::user()->id;  
                        $row['created_by']   = Auth::user()->id;


                        $jad = Jadwal::create($row);

                        $jadwal = Jadwal::find($jad->id);
                        
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->senin])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 1, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->selasa])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 2, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->rabu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 3, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->kamis])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 4, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->jumat])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 5, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->sabtu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 6, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->minggu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 7, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                    }
                    else
                    {
                        $idJad = $jadwal->first()->id;
                        
                        Jadwal::find($idJad)->fill(
                                array('kode' => strtoupper($csv[$arrKey->kode]),
                                      'deskripsi' => strtoupper($csv[$arrKey->deskripsi]),
                                      'updated_by' => Auth::user()->id))->save();
                        
                        $jadwal = Jadwal::find($idJad);
                        $jadwal->jadwal_kerja()->detach();

                        $jKerja = JamKerja::where('kode', $csv[$arrKey->senin])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 1, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->selasa])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 2, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->rabu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 3, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->kamis])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 4, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->jumat])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 5, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->sabtu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 6, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        $jKerja = JamKerja::where('kode', $csv[$arrKey->minggu])->first();
                        $jadwal->jadwal_kerja()->attach($jKerja->id,['day' => 7, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
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
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }

    public function shiftStore(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'kode'   => 'required',
            ],
            [
                'kode.required'  => 'Kode harus diisi.',
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
                    $req['tipe']        = 'S'; 
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    $req['created_by']   = Auth::user()->id;
                    $req['created_at']   = Carbon::now();
                    
                    $ress = Jadwal::create($req);
                    
                    $jd = Jadwal::find($ress->id);

                    if($ress->id > 0)
                    {
                        foreach($req['data'] as $valX)
                        {
                            if(isset($valX['id']))
                            {
                                $jd->jadwal_kerja()->attach($valX['id'],['tanggal' => $valX['date'], 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                            }
                        }
                    }
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();

                    Jadwal::find($req['id'])->fill($req)->save();
                    
                    $jd = Jadwal::find($req['id']);
                    $jd->jadwal_kerja()->detach();
                    
                    foreach($req['data'] as $valX)
                    {
                        if(isset($valX['id']))
                        {
                            $jd->jadwal_kerja()->attach($valX['id'],['tanggal' => $valX['date'], 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                    }
                    

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
    
    public function shiftUpload(Request $request)
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
                
                $fileVar->move(storage_path('tmp'),'tempFileUploadShift');
                
                $spreadsheet = IOFactory::load(storage_path('tmp').'/tempFileUploadShift');
                
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                              
                
                
                $x = 0;       
                $kdBefore = ""; $jadId = 0;
                $jadwalMaster = null;  
                $arrKey = null;
                
                $jadwal = null;
                
                
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
                    
                    if($kdBefore != $csv[$arrKey->kode])
                    {
                        $kdBefore = $csv[$arrKey->kode];
                        $jadId = 0;
                        $jadwalMaster = Jadwal::where('kode', $csv[$arrKey->kode])->where('tipe', 'S');
                        
                        if($jadwalMaster->count() != 0)
                        {
                            $jadwalMaster = $jadwalMaster->first();
                            
                            $jadId = $jadwalMaster->id;
                            
                            $jadwal = Jadwal::find($jadId);
//                            continue;
                        }
                        else
                        {
                            $jadId = Jadwal::create(['kode' => $csv[$arrKey->kode], 'tipe' => 'S', 'created_by' => Auth::user()->id, 'updated_by' => Auth::user()->id]);
                            $jadId = $jadId->id;
                        }
                    }
                    
                    if($jadId == 0)
                    {
                        continue;
                    }
                    
                    $jKerja = JamKerja::where('kode',$csv[$arrKey->jamkerja])->first();
                    
                    if($jKerja)
                    {
                        $detach = $jadwal->jadwal_kerja()->wherePivot('tanggal',$csv[$arrKey->tanggal]);

                        if($detach)
                        {
                            $detach->detach();
                        }

                        $jadwal->jadwal_kerja()->attach($jKerja->id,
                                ['tanggal' => $csv[$arrKey->tanggal], 
                                 'created_by' => Auth::user()->id, 
                                 'created_at' => Carbon::now()]);
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
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }

    public function shiftCopyStore(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sMaster'   => 'required',
                'eMaster'   => 'required',
                'sTarget'   => 'required',
                'eTarget'   => 'required',
                'jadwalId'   => 'required',
            ],
            [
                'sMaster.required'  => 'Tanggal Master Awal harus diisi.',
                'eMaster.required'  => 'Tanggal Master Akhir harus diisi.',
                'sTarget.required'  => 'Tanggal Target Awal harus diisi.',
                'eTarget.required'  => 'Tanggal Target Akhir harus diisi.',
                'jadwalId.required'  => 'Karyawan harus dipilih.',
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

                $jad = Jadwal::find($req['jadwalId']);
                
                if($jad->id)
                {
                    $targetPeriode = CarbonPeriod::create($req['sTarget'], $req['eTarget'])->toArray();
                    
                    $between = $jad->jadwal_kerja()->wherePivot('tanggal','>=',$req['sMaster'])
                                                   ->wherePivot('tanggal','<=',$req['eMaster'])
                                                   ->orderBy('tanggal','asc');
                                
                    foreach($between->get() as $k => $v)
                    {
                        if(array_key_exists($k, $targetPeriode))
                        {
                            $jad->jadwal_kerja()->wherePivot('tanggal', $targetPeriode[$k]->toDateString())->detach();
                            $jad->jadwal_kerja()->attach($v->id,['tanggal' => $targetPeriode[$k]->toDateString(), 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()]);
                        }
                    }
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
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
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan',
                'err' => $er->getMessage()
            ));
        }
    }
    
    public function dtjadwalday(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Jadwal::with(['jadwal_kerja','createdBy'])->where('tipe','D');  

        if(!empty($req['txtSearch']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('kode', $req['txtSearch']);
                $q->orWhere('deskripsi', $req['txtSearch']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrow btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fa fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrow btn btn-danger btn-xs" title="Hapus"><i class="fa fa-eraser"></i></button>';
                    $str    .= '</div>';
                    
                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function dtjadwalshift(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Jadwal::with(['createdBy'])->where('tipe','S');  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('kode', $req['search']);
                $q->orWhere('nama', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="copyrow btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-copy-form" title="Copy Jadwal"><i class="fa fa-copy"></i></button>';
                    $str    .= '<button class="editrow btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fa fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrow btn btn-danger btn-sm" title="Hapus"><i class="fa fa-eraser"></i></button>';
                    $str    .= '</div>';
                    
                    return $str;
                })
                ->addColumn('kode_url', function($datas)
                {
                    $str = '<a data-toggle="modal" data-target="#modal-show" class="show" href="#">'.$datas->kode.'</a>';
                    
                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->rawColumns(['action', 'kode_url'])
                ->make(true);
    }
    
    public function dtsetjadwal(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Jadwal::with(['karyawan','createdBy']);  
        
        if(!empty($req['sTipe']))
        {
            $datas->where('tipe', $req['sTipe']);
        }
        
        if(!empty($req['sKode']))
        {
            $datas->where('kode', 'like', '%'.$req['sKode'].'%');
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<a href="'.route('jksetadd', $datas->id).'" class="editrow btn btn-primary btn-xs" title="Tambah Karyawan"><i class="fa fa-plus"></i></a>';
//                    $str    .= '<button class="delrow btn btn-danger btn-xs" title="Hapus"><i class="fa fa-eraser"></i></button>';
                    $str    .= '</div>';
                    
                    return $str;
                })
                ->addColumn('tipejadwal', function($datas)
                {
                    $str = '';
                    if($datas->tipe == 'D')
                    {
                        $str = '<div class="text-primary">Dayshift</div>';
                    }
                    else
                    {
                        $str = '<div class="text-warning">Shift</div>';
                    }
                    
                    return $str;
                })
                ->addColumn('jumlahkaryawan', function($datas)
                {
                    return $datas->karyawan->count();
                })
//                ->addColumn('kode_url', function($datas)
//                {
//                    $str = '<a data-toggle="modal" data-target="#modal-show" class="show" href="#">'.$datas->kode.'</a>';
//                    
//                    return $str;
//                })
                ->editColumn('id', '{{$id}}')
                ->rawColumns(['action', 'tipejadwal'])
                ->make(true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function destroyjadwalday(Request $request)
    {
        $req = $request->all();
        try 
        {
            Jadwal::find($req['id'])->delete();
            
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
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function destroyjadwalshift(Request $request)
    {
        $req = $request->all();
        try 
        {
            Jadwal::find($req['id'])->delete();
            
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

    public function fc(Request $request)
    {
        try
        {            
            $req = $request->all();
            if(!empty($req['id']))
            {
                $res = Jadwal::find($req['id']);
                
                $ret = array();
                
                foreach($res->jadwal_kerja as $jk)
                {
                    $ret[] = array(
                        'title' => $jk->kode."\n".$jk->jam_masuk." - ".$jk->jam_keluar,
                        'start' => $jk->pivot->tanggal,
                        'end' => $jk->pivot->tanggal,
                        'color' => $jk->warna,
                        'id' => $jk->id
                    );
                }
                
                echo json_encode($ret);
            }
            else
            {
                echo "";
            }
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

    public function fcmanual(Request $request)
    {
        try
        {            
            $req = $request->all();
            if(!empty($req['id']))
            {
                $res = Karyawan::find($req['id']);
                
                $ret = array();
                
                foreach($res->jadwal_manual as $jk)
                {
                    $ret[] = array(
                        'title' => $jk->kode."\n".$jk->jam_masuk." - ".$jk->jam_keluar,
                        'start' => $jk->pivot->tanggal,
                        'end' => $jk->pivot->tanggal,
                        'color' => $jk->warna,
                        'id' => $jk->id
                    );
                }
                
                echo json_encode($ret);
            }
            else
            {
                echo "";
            }
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
    
    public function select2(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = Jadwal::where(function($q) use($term)
        {
            $q->where('kode','like','%'.$term.'%')
              ->orWhere('tipe','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $lbl = "";
            
            if($tag->tipe == 'D')
            {
                $lbl = 'Dayshift';
            }
            else if($tag->tipe == 'S')
            {
                $lbl = 'Shift';
            }
            
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->kode.' - '.$tag->tipe, 'label' => $lbl, 'tipe' => $tag->tipe];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
 
}
