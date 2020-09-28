<?php

namespace App\Http\Controllers;

use App\RequestAlasan;
use App\RequestAlasanDetail;
use App\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Validator;
use Mail;
use Ramsey\Uuid\Uuid;

use App\Http\Traits\TraitProses;


class RequestAlasanController extends Controller
{
    use TraitProses;
    private $prefixFile = 'F_DOK_ALS_';
    private $pathFile = 'app/dokumen_alasan';
    private $perusahaan = 'PT. APAC';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('request.alasan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $var = new RequestAlasan;
//        $var->no_dokumen = Carbon::now()->format('Ymd').'/APAC/HR/'.substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 5);
        
        
        return view('request.alasan.form', ['var' => $var]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function edit($kode, Request $request)
    {
        if($datas = RequestAlasan::where('uid_dokumen', $kode)->where('status', 'new')->first())
        {
            if($var = RequestAlasan::find($datas->id))
            {
                return view('request.alasan.form', ['var' => $var]);
            }
            else
            {
                return abort(404);
            }
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function show($kode)
    {
        if($datas = RequestAlasan::where('uid_dokumen', $kode)->first())
        {
            if($var = RequestAlasan::find($datas->id))
            {
                return view('request.alasan.preview', ['var' => $var]);
            }
            else
            {
                return abort(404);
            }
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval($kode, Request $request)
    {
        if($datas = RequestAlasan::where('uid_dokumen', $kode)->where('status', 'send')->first())
        {
            if($var = RequestAlasan::find($datas->id))
            {
                return view('request.alasan.approval', ['var' => $var]);
            }
            else
            {
                return abort(404);
            }
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'file_dokumen_upload' => 'required_if:id,null|mimetypes:application/pdf',
            'tanggal' => 'required',
        ],
        [
            'file_dokumen_upload.required' => 'File harus disertakan',
            'file_dokumen_upload.mimetypes' => 'File harus berupa PDF',
            'tanggal.required' => 'Tanggal Dokumen Tidak boleh kosong',
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
            $req = $request->only(['id','file_dokumen_upload', 'tanggal', 'catatan', 'no_dokumen']);
            
            $cnt = RequestAlasan::where('tanggal', Carbon::now()->toDateString())->count() + 1;
            $fileName = null;
            
            if(isset($req['file_dokumen_upload']))
            {
                $fileVar = $req['file_dokumen_upload']; 

                $fileName = $this->prefixFile.Carbon::now()->format('dmYHis').'.'.$fileVar->getClientOriginalExtension();

                $fileContent = file_get_contents($fileVar->getRealPath());
                file_put_contents(storage_path($this->pathFile).'/'.$fileName, Crypt::encrypt($fileContent));
            }
            
            if($req['id'])
            {
                $upd = [
                    'file_dokumen' => $fileName,
                    'tanggal' => $req['tanggal'],
                    'catatan' => $req['catatan'],
                    'updated_by' => Auth::user()->id
                ];
                
                if(!$fileName)
                {
                    unset($upd['file_dokumen']);
                }
                
                RequestAlasan::find($req['id'])->fill($upd)->save();
            }
            else
            {
                $id = RequestAlasan::create([
                    'uid_dokumen' => Uuid::uuid4()->getHex(),
                    'file_dokumen' => $fileName,
                    'no_dokumen' => Carbon::now()->format('Y/m/d').'/IJ/REQ/'.sprintf('%05d',$cnt),
                    'status' => 'new',
                    'tanggal' => $req['tanggal'],
                    'catatan' => $req['catatan'],
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
                
                RequestAlasanDetail::whereNull('request_alasan_id')->where('created_by', Auth::user()->id)->update([
                    'request_alasan_id' => $id->id
                ]);
            }
            
            echo json_encode(array(
                'status' => 1,
                'msg'   => 'Data berhasil disimpan'
            ));
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDetail(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'dtanggal' => 'required',
            'dpin' => 'required',
            'dalasan' => 'required'
        ],
        [
            'dtanggal.required' => 'Tanggal Alasan Tidak boleh kosong',
            'dpin.required' => 'Karyawan Tidak boleh kosong',
            'dalasan.required' => 'Alasan Tidak boleh kosong',
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
            $req = $request->only(['id','dtanggal', 'dtanggalAkhir', 'dwaktu','dpin', 'dalasan', 'dcatatan', 'did']);

            $id = null;

            if(isset($req['id']))
            {
                $id = $req['id'];
            }
            
            if(isset($req['did']))
            {
                RequestAlasanDetail::find($req['did'])->fill([
                    'tanggal' => $req['dtanggal'],
                    'tanggal_akhir' => $req['dtanggalAkhir'],
                    'waktu' => $req['dwaktu'],
                    'karyawan_id' => $req['dpin'],
                    'alasan_id' => $req['dalasan'],
                    'catatan' => $req['dcatatan'],
                    'updated_by' => Auth::user()->id
                ])->save();
                
                echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
            }
            else if($prev = RequestAlasanDetail::where('tanggal', $req['dtanggal'])->where('karyawan_id', $req['dpin'])->first())
            {
                RequestAlasanDetail::find($prev->id)->fill([
                    'tanggal' => $req['dtanggal'],
                    'tanggal_akhir' => $req['dtanggalAkhir'],
                    'waktu' => $req['dwaktu'],
                    'karyawan_id' => $req['dpin'],
                    'alasan_id' => $req['dalasan'],
                    'request_alasan_id' => $id,
                    'catatan' => $req['dcatatan'],
                    'updated_by' => Auth::user()->id
                ])->save();
                
                echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
            }
            else
            {
            
                if(RequestAlasanDetail::create([
                    'tanggal' => $req['dtanggal'],
                    'tanggal_akhir' => $req['dtanggalAkhir'],
                    'waktu' => $req['dwaktu'],
                    'karyawan_id' => $req['dpin'],
                    'alasan_id' => $req['dalasan'],
                    'request_alasan_id' => $id,
                    'catatan' => $req['dcatatan'],
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]))
                {
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
        
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDokumen(Request $request)
    {
        $this->validate($request, [
            'noDokumen' => 'required'
        ],
        [
            'noDokumen.required' => 'No Dokumen Tidak boleh kosong'
        ]);
        
        if($id = RequestAlasan::create([
            'no_dokumen' => $request->input('noDokumen'),
            'tanggal' => Carbon::now()->toDateString(),
            'status' => 'new',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]))
        {
            return response()->json([
                'status' => 1,
                'id' => $id->id
            ]);
        }
        else
        {
            return response()->json([
                'status' => 0,
                'id' => 0
            ]);
        }
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function showDetailApi(Request $request)
    {
        $ret = [];
        if($req = $request->only(['id']))
        {
            $row = RequestAlasanDetail::find($req['id']);
            
            $ret = [
                'tanggal' => $row->tanggal,
                'tanggal_akhir' => $row->tanggal_akhir,
                'alasan' => [
                    'id' => $row->alasan->id, 
                    'kode' => $row->alasan->kode, 
                    'deskripsi' => $row->alasan->deskripsi,
                    'warna' => $row->alasan->warna
                ],
                'karyawan' => [
                    'id' => $row->karyawan->id,
                    'nama' => $row->karyawan->nama,
                    'pin' => $row->karyawan->pin,
                    'divisi' => [
                        'id' => $row->karyawan->divisi->id,
                        'kode' => $row->karyawan->divisi->kode,
                        'deskripsi' => $row->karyawan->divisi->deskripsi
                    ]
                ],
                'waktu' => $row->waktu,
                'request_alasan_id' => $row->request_alasan_id,
                'status' => $row->status,
                'catatan' => $row->catatan,
                'id' => $row->id
            ];
        }
        return response()->json($ret);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestAlasan $requestAlasan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Karyawan Tidak dipilih'
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
            $req = $request->only(['id']);
            try 
            {
//                $this->send_email($req['id']);
                RequestAlasan::find($req['id'])->fill(['status' => 'send', 'updated_by' => Auth::user()->id])->save();

                echo json_encode(array(
                   "status" => 1,
                    "msg"   => "Data berhasil dikirim."
                ));
            } 
            catch (QueryException $ex) 
            {
                echo json_encode(array(
                   "status" => 0,
                    "msg"   => "Data gagal dikirim."
                ));
            }
        }
    }    
    

    /**
     * decline the specified resource from storage.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function declineDetailApi(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Karyawan Tidak dipilih'
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
            $req = $request->only(['id', 'catatan']);
            try 
            {
                RequestAlasanDetail::find($req['id'])->fill([
                    'status' => 'decline',
                    'declined_by' => Auth::user()->id,
                    'declined_at' => Carbon::now(),
                    'declined_note' => $req['catatan']
                    
                ])->save();

                echo json_encode(array(
                   "status" => 1,
                    "msg"   => "Data berhasil ditolak."
                ));
            } 
            catch (QueryException $ex) 
            {
                echo json_encode(array(
                   "status" => 0,
                    "msg"   => "Data gagal ditolak."
                ));
            }
        }
    }  
    

    /**
     * decline the specified resource from storage.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function declineApi(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Karyawan Tidak dipilih'
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
            $req = $request->only(['id', 'catatan']);
            try 
            {
                RequestAlasan::find($req['id'])->fill([
                    'status' => 'decline',
                    'declined_by' => Auth::user()->id,
                    'declined_at' => Carbon::now(),
                    'declined_note' => $req['catatan']                    
                ])->save();
                
                RequestAlasanDetail::where('request_alasan_id', $req['id'])->update([
                    'status' => 'decline',
                    'declined_by' => Auth::user()->id,
                    'declined_at' => Carbon::now(),
                    'declined_note' => $req['catatan']                    
                ]);

                echo json_encode(array(
                   "status" => 1,
                    "msg"   => "Data berhasil ditolak."
                ));
            } 
            catch (QueryException $ex) 
            {
                echo json_encode(array(
                   "status" => 0,
                    "msg"   => "Data gagal ditolak."
                ));
            }
        }
    } 
    

    /**
     * decline the specified resource from storage.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function appApi(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Dokumen Tidak dipilih'
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
            $req = $request->only(['id']);
            try 
            {
                RequestAlasan::find($req['id'])->fill([
                    'status' => 'approve',
                    'approved_by' => Auth::user()->id,
                    'approved_at' => Carbon::now()                                    
                ])->save();
                
                $det = RequestAlasanDetail::where('request_alasan_id', $req['id'])->where(function($q)
                {
                    $q->whereNull('status');
                    $q->orWhere('status','approve');
                });
                
                foreach($det->get() as $rDet)
                {
                    $kAls = Karyawan::find($rDet->karyawan_id);
                    $dStart = Carbon::createFromFormat('Y-m-d', $rDet->tanggal);
                    $dEnd = Carbon::createFromFormat('Y-m-d', $rDet->tanggal);
                    if($rDet->tanggal_akhir)
                    {
                        $dEnd = Carbon::createFromFormat('Y-m-d', $rDet->tanggal_akhir);
                    }
                    
                    $tglPer = CarbonPeriod::create($dStart->toDateString(), $dEnd->toDateString())->toArray();                    
                    
                    foreach($tglPer as $vTgl)
                    {
                        $par = $kAls->alasan()->wherePivot('tanggal', $vTgl);
    //                
                        if($par)
                        {
                            $par->detach($rDet->alasan_id);
                        }

                        if($rDet->alasan_id)
                        {
                            $attach = ['tanggal' => $vTgl, 'keterangan' => $rDet->catatan, 'created_by' => Auth::user()->id, 'created_at' => Carbon::now()];
//                            dd($attach);
                            if($rDet->waktu)
                            {
                                $attach['waktu'] = $rDet->waktu;
                            }

                            $kAls->alasan()->attach($rDet->alasan_id, $attach);
                            $this->prosesAbsTanggal($kAls->id, $vTgl);
                        }
                    }
                }
                $det->update(['status' => 'approve',
                    'approved_by' => Auth::user()->id,
                    'approved_at' => Carbon::now()  ]);
                echo json_encode(array(
                   "status" => 1,
                    "msg"   => "Data berhasil disetujui."
                ));
            } 
            catch (QueryException $ex) 
            {
                echo json_encode(array(
                   "status" => 0,
                    "msg"   => "Data gagal disetujui."
                ));
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RequestAlasan  $requestAlasan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Karyawan Tidak dipilih'
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
            $req = $request->only(['id']);
            try 
            {
                RequestAlasan::find($req['id'])->delete();

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
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Karyawan  $Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroyDetail(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ],
        [
            'id.required' => 'Karyawan Tidak dipilih'
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
            $req = $request->only(['id']);
            try 
            {
                RequestAlasanDetail::find($req['id'])->delete();

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
    }
    
    public function download($kode)
    {
        $isDownload = false;
        $dok = null;
        
        if($kode)
        {
            $path = storage_path($this->pathFile.'/') . $kode;
            $fileContentEncrypted = file_get_contents($path);
            $fileContentDecrypted = Crypt::decrypt($fileContentEncrypted);
            
            $headers = array(
              'Content-Type' => 'application/pdf',
              'Content-Disposition' => 'inline; ; filename="' . $kode .'"'
            );
            
            return response()->make($fileContentDecrypted, 200,$headers);
            
        }
    }
    
    public function dt(Request $request)
    {
        $req    = $request->all();
        
        $datas   = RequestAlasan::with('detail', 'approve', 'decline');  
        
        if(!empty($req['sDokumen']))
        {
            $datas->where('no_dokumen', 'like', '%'.$req['sDokumen'].'%');
        }
        
        if(!empty($req['sTanggal']))
        {
            $datas->where('tanggal', $req['sTanggal']);
        }
        
        if(!empty($req['sStatus']))
        {
            $datas->where('status', $req['sStatus']);
        }
        
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)               
                ->editColumn('id', '{{$id}}')
                ->addColumn('link_file', function($datas)
                {
                    if($datas->file_dokumen)
                    {
                        return route('downloadrequestalasan',$datas->file_dokumen);
                    }
                    
                    return '';
                })
                ->addColumn('link_edit', function($datas)
                {
                    if($datas->status == 'new')
                        return route('alasanrequestedit', $datas->uid_dokumen);
                    else
                        return '';
                })
                ->addColumn('link_show', function($datas)
                {
                    return route('alasanrequestshow', $datas->uid_dokumen);
                })
                ->addColumn('action', function($datas)
                {
                    $str = '<button class="btn btn-sm btn-default btnshow"><i class="fa fa-search"></i></button>';
                    if($datas->status == 'new')
                    {
                        $str = '<button class="btn btn-sm btn-primary btnedit"><i class="fa fa-edit"></i></button>'.
                               '<button class="btn btn-sm btn-danger btndel"><i class="fa fa-eraser"></i></button>'.
                               '<button class="btn btn-sm btn-success btnsend"><i class="fa fa-paper-plane"></i></button>';
                    }
                    return $str;
                })
                ->make(true);
    }
    
    public function dtDetail(Request $request)
    {
        $req    = $request->only(['id']);
        
        $datas   = RequestAlasanDetail::with('karyawan', 'alasan'); 
        
        if($req['id'])
        {
            $datas->where('request_alasan_id', $req['id']);
        }
        else
        {
            $datas->where('created_by', Auth::user()->id)->whereNull('request_alasan_id');
        }
        
        $datas->orderBy('created_at','desc');
        
        return  Datatables::of($datas)               
                ->editColumn('id', '{{$id}}')
                ->addColumn('divisi', function($q)
                {
                    return $q->karyawan->divisi->kode.' - '.$q->karyawan->divisi->deskripsi;
                })
                ->addColumn('alasan', function($q)
                {
                    return $q->alasan->kode.' - '.$q->alasan->deskripsi;
                })
                ->make(true);
    }
    
    private function send_email($id)
    {
        $reqAlasan = RequestAlasan::find($id);

        $mailSend = "";
        
        
        
        $data = [
            'subject' => 'Notifikasi Request Alasan '.$this->perusahaan.'. '.$reqAlasan->no_dokumen,
            'no'    => $reqAlasan->no_dokumen,
            'tanggal' => Carbon::now()->format('d/m/Y'),
            'jam' => Carbon::now()->format('H:i:s'),
            'url' => route('alasanrequestapproval', $reqAlasan->uid_dokumen)
        ];
        
        $uType = \App\MasterOption::where('kode', 'USERTYPE')->whereIn('nama',['PAYROLL', 'HRD'])->pluck('id');
        
        if($users = \App\User::whereIn('type_id',$uType)->get())
        {
//            dd($users);
            foreach($users as $kS)
            {
                Mail::send('request.alasan.mail', $data, function ($m) use($data, $reqAlasan, $users,$kS,$mailSend)
                {
                    $m->to($kS->email);
                    $m->from('admin@indahjaya.co.id');
                    $m->subject($data['subject']);
                    $mailSend .= $kS->email." ";
                });
                \App\EmailLog::insert(array(
                    'app_id' => $reqAlasan->id,
                    'app_path' => 'RequestAlasan',
                    'email' => $kS->email,
                    'created_by' => Auth::user()->id,
                    'created_at' => Carbon::now()
                ));
                
            }
        }
        
        return $mailSend;
    }
}
