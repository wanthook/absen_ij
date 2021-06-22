<?php

namespace App\Http\Controllers;

use App\Mesin;
use App\Activity;
use App\Karyawan;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;
use Ping;
use ZKLib\ZKLib;
use ZKLib\User;
use GuzzleHttp\Client;

class MesinController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.mesin.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexTarik()
    {
        return view('admin.transaksi.tarik.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
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
                    'kode'   => 'required',
                    'lokasi'   => 'required',
                    'merek'   => 'required',
                    'ip'   => 'required|ip',
                ],
                [
                    'kode.required'  => 'Kode harus diisi.',
                    'merek.required'  => 'Merek harus diisi.',
                    'lokasi.required'  => 'Lokasi harus diisi.',
                    'ip.required'  => 'IP harus diisi.',
                    'ip.ip'  => 'IP harus berformat xxx.xxx.xxx.xxx.',
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
                    
                    Mesin::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    Mesin::find($req['id'])->fill($req)->save();
                    
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
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editActivity(Request $request)
    {
        if(Auth::user()->type->nama != 'ADMIN')
            abort (404);
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'id'   => 'required',
                    'tanggal'   => 'required',
                ],
                [
                    'id.required'  => 'Data tidak dipilih.',
                    'tanggal.required'  => 'Tanggal harus diisi.',
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
                $req = $request->only(['id', 'tanggal']);

                if(!empty($req['id']))
                {
                    Activity::find($req['id'])->fill(['tanggal' => $req['tanggal']])->save();
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
            }
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Mesin  $mesin
     * @return \Illuminate\Http\Response
     */
    public function show(Mesin $mesin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Mesin  $mesin
     * @return \Illuminate\Http\Response
     */
    public function edit(Mesin $mesin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Mesin  $mesin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mesin $mesin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Mesin  $mesin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            Mesin::find($req['id'])->delete();
            
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
    
    public function tarikAbsen(Request $request)
    {
        $timeProcess = microtime(true);
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'id'   => 'required',
            ],
            [
                'id.required'  => 'Id mesin harus diisi.',
            ]);

            if($validation->fails())
            {
                echo json_encode(array(
                    'status' => 0,
                    'msg'   => $validation->errors()->all(),
                    'time'  => intval(microtime(true) - $timeProcess)
                ));
            }
            else
            {
                
                $req = $request->all();
                
                foreach($req['id'] as $valId)
                {
                    $mesin = Mesin::find($valId);
                    
                    if($mesin->api_address)
                    {
                        $countData = 0;
                        $requ = new Client(); 
                        $res = null;
                        if($mesin->api_db == 'Y')
                        {
                            $res = $requ->request('GET', $mesin->api_address.'/api/v1/tarik_db?ip='.$mesin->ip.'&periode='.$req['periode']);
                        }
                        else
                        {
                            $res = $requ->request('GET', $mesin->api_address.'/api/v1/tarik?ip='.$mesin->ip.'&key='.$mesin->key.'&periode='.$req['periode']);
                        }
                        
                        sleep(1);
                        $tarik = json_decode($res->getBody()->getContents());
                        if(!empty($tarik->logs))
                        {
                            foreach($tarik->logs as $rTarik)
                            {
                                $cnt = Activity::where('pin', $rTarik->pin)
                                                ->where('tanggal', $rTarik->tanggal)
                                                ->where('mesin_id', $mesin->id)->count();
                                
                                if($cnt == 0)
                                {
                                    $storeAct = array(
                                        "pin" => $rTarik->pin,
                                        "tanggal" => $rTarik->tanggal,
                                        "verified" => $rTarik->verified,
                                        "status" => $rTarik->status,
                                        "workcode" => $rTarik->workcode,
                                        "mesin_id" => $mesin->id,
                                        "created_by" => Auth::user()->id
                                    );
                                    Activity::create($storeAct);

                                }
                                // $countData++;
                            }
                        }
                        $mesin->lastlog = Carbon::now();
                        $mesin->total_log = $tarik->totaldata;
                        $mesin->save();
                    }
                    else
                    {
                        $con = fsockopen($mesin->ip, 80);

                        if($con)
                        {
                            $soapReq = '<GetAttLog><ArgComKey xsi:type="xsd:integer">'.$mesin->key.'</ArgComKey><Arg><PIN xsi:type="xsd:integer">All</PIN></Arg></GetAttLog>';
                            $new_line = "\r\n";

                            fputs($con, "POST /iWsService HTTP/1.0".$new_line);
                            fputs($con, "Host:".$mesin->ip.$new_line);
                            fputs($con, "Content-Type: text/xml".$new_line);
                            fputs($con, "Content-Length: ".strlen($soapReq).$new_line.$new_line);
                            fputs($con, $soapReq.$new_line);

                            $countData = 0;

                            while($res = fgets($con,1024))
                            {

                                if(substr($res,0,1)!="<")
                                {
                                    continue;
                                }

                                if(stristr($res, "GetAttLogResponse"))
                                {
                                    continue;
                                }

                                $vals = null;
                                $req = null;

                                $parser = xml_parser_create();
                                xml_parse_into_struct($parser, $res, $vals);
                                if(isset($req['periode']))
                                {
                                    if(!empty($req['periode']))
                                    {
                                        // $tgl = Carbon::createFromFormat('Y-m-d H:i:s', $vals[2]['value'])->format('Y-m');
                                        $tgl = substr($vals[2]['value'],0,7);
                                        if($tgl != $req['periode'])
                                        {
                                            $countData++;
                                            continue;
                                        }
                                    }
                                }
                                $cnt = Activity::where('pin', $vals[1]['value'])
                                               ->where('tanggal', $vals[2]['value'])
                                               ->where('mesin_id', $mesin->id)->count();

                                if($cnt == 0)
                                {
                                    if(array_key_exists(1, $vals) && array_key_exists(2, $vals) && array_key_exists(3, $vals) && array_key_exists(4, $vals) && array_key_exists(5, $vals))
                                    {
                                        $storeAct = array(
                                            "pin" => $vals[1]['value'],
                                            "tanggal" => $vals[2]['value'],
                                            "verified"=>$vals[3]['value'],
                                            "status" => $vals[4]['value'],
                                            "workcode" => $vals[5]['value'],
                                            "mesin_id" => $mesin->id,
                                            "created_by" => Auth::user()->id
                                        );

                                        Activity::create($storeAct);
                                    }

                                }
                                $countData++;
                                xml_parser_free($parser);
                            }

                            $mesin->lastlog = Carbon::now();
                            $mesin->total_log = $countData;
                            $mesin->save();
                        }
                    }
                    
                }
//                $request->session()->put('tarik.percent', 100);

                echo json_encode(array(
                    'status' => 1,
                    'msg'   => 'Data berhasil diubah',
                    'time'  => intval(microtime(true) - $timeProcess)
                ));
            }
//            $request->session()->forget('tarik');
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'.$er->getMessage(),
                'time'  => intval(microtime(true) - $timeProcess)
            ));
        }
    }
    
    public function hapusAbsen(Request $request)
    {
        $timeProcess = microtime(true);
        $arrHapus = [];
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'id'   => 'required',
            ],
            [
                'id.required'  => 'Id mesin harus diisi.',
            ]);

            if($validation->fails())
            {
                echo json_encode(array(
                    'status' => 0,
                    'msg'   => $validation->errors()->all(),
                    'time'  => intval(microtime(true) - $timeProcess)
                ));
            }
            else
            {
                
                $req = $request->all();
                
                foreach($req['id'] as $valId)
                {
                    $mesin = Mesin::find($valId);
                    
                    if($mesin->api_address)
                    {
                        $countData = 0;
                        $req = new Client(); 
                        $res = null;
                        if($mesin->api_db != 'Y')
                        {
                            $res = $req->request('GET', $mesin->api_address.'/api/v1/hapus?ip='.$mesin->ip.'&key='.$mesin->key);
                        }
                        
                        sleep(1);
                        $rets = json_decode($res->getBody()->getContents());
                        
                        if($rets->status)
                        {
                            
                        }

                        // echo json_encode(array(
                        //     'status' => 1,
                        //     'msg'   => 'Data berhasil dihapus',
                        //     'time'  => intval(microtime(true) - $timeProcess)
                        // ));
                    }
                    else
                    {
                        $con = fsockopen($mesin->ip, 80);

                        if($con)
                        {
                            $soap_req = '<ClearData>
                                <ArgComKey xsi:type="xsd:integer">'.$mesin->key.'</ArgComKey>
                                <Arg><Value xsi:type="xsd:integer">3</Value></Arg>
                              </ClearData>';

                            $new_line = "\r\n";

                            fputs($con, "POST /iWsService HTTP/1.0".$new_line);
                            fputs($con, "Host:".$mesin->ip.$new_line);
                            fputs($con, "Content-Type: text/xml".$new_line);
                            fputs($con, "Content-Length: ".strlen($soap_req).$new_line.$new_line);
                            fputs($con, $soap_req.$new_line);

                            while($res = fgets($con,1024))
                            {
                            
                            }
                            // echo json_encode(array(
                            //     'status' => 1,
                            //     'msg'   => 'Data berhasil dihapus',
                            //     'time'  => intval(microtime(true) - $timeProcess)
                            // ));

                        }
                    }
                    $mesin->lastlog = Carbon::now();
                    $mesin->total_log = 0;
                    $mesin->save();
                }
//                $request->session()->put('tarik.percent', 100);

                
            }
            echo json_encode(array(
                'status' => 1,
                'msg'   => 'Mesin berhasil dihapus',
                'time'  => intval(microtime(true) - $timeProcess)
            ));
//            $request->session()->forget('tarik');
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'.$er->getMessage(),
                'time'  => intval(microtime(true) - $timeProcess)
            ));
        }
    }

    public function uploadAbsen(Request $request)
    {
        if(Auth::user()->type->nama != 'ADMIN')
            abort (404);
        try
        {
            $validation = Validator::make($request->all(), 
                [
                    'sMesin'   => 'required',
                    'formUpload'   => 'required',
                ],
                [
                    'sMesin.required'  => 'Data mesin tidak dipilih.',
                    'formUpload.required'  => 'File kosong.',
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
                $req = $request->only(['sMesin', 'formUpload']);
                $mesin = Mesin::find($req['sMesin']);
                $fileVar = $req['formUpload'];

                $fileStorage = fopen($fileVar->getRealPath(),'r');
                $databaru = 0;
                $tData = 0;
                while(! feof($fileStorage))
                {
                    $csv = fgets($fileStorage, 1024);
                    $eCsv = explode("\t",$csv);
                    if(count($eCsv) > 1)
                    {
                        $tData += 1;
                        $cnt = Activity::where('pin', trim($eCsv[0]))
                                               ->where('tanggal', trim($eCsv[1]))
                                               ->where('mesin_id', $mesin->id)->count();
                        if($cnt == 0)
                        {
                            $ret = array(
                                "pin"       => trim($eCsv[0]),
                                "tanggal"   => trim($eCsv[1]),
                                "verified"  => trim($eCsv[2]),
                                "status"    => trim($eCsv[3]),
                                "workcode"  => trim($eCsv[4]),
                                "mesin_id"  => $req['sMesin'],
                                "created_by"=> Auth::user()->id
                            );
                            Activity::create($ret);
                            $databaru+=1;
                        }
                    }

                }
                $mesin->lastlog = Carbon::now();
                $mesin->total_log = $tData;
                $mesin->save();
                if($databaru)
                {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => $databaru.' Data berhasil disimpan'
                    ));
                }
                else
                {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'   => 'Tidak ada data baru'
                    ));
                }
            }
        }
        catch (QueryException $er)
        {
            echo json_encode(array(
                'status' => 0,
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }
    
    public function dt(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Mesin::with(['createdBy']);  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('kode', $req['search']);
                $q->orWhere('deskripsi', $req['search']);
                $q->orWhere('ip', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrow btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fas fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrow btn btn-danger btn-sm" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';

                    return $str;
                })
                ->addColumn('ping_status', function($datas)
                {
                    if(!empty($datas->api_address))
                    {
                        $req = new Client();   
                        $res = $req->request('GET', $datas->api_address.'/api/v1/ping?ip='.$datas->ip);
                        sleep(1);
                        $status = json_decode($res->getBody()->getContents())->status;
                        
                        if($status)
                        {
                            return 0;
                        }
                        return 1;
                    }
                    else
                    {
                        return $this->pingIp($datas->ip);
                    }
                    return 1;
                    
                })
                ->editColumn('id', '{{$id}}')
                ->rawColumns(['action'])
                ->make(true);
    }
    
    public function dtActivityShow(Request $request)
    {
        $req    = $request->all();
        
        $datas   = null;  
        
        $datas = Activity::with('mesin', 'karyawan');
        
        if(!empty($req['sMesin']))
        {
            $datas->where('mesin_id', $req['sMesin']);
        }

        $datas->orderBy('id','desc');

        return  Datatables::of($datas)
            ->editColumn('id', '{{$id}}')
            ->make(true);
        
    }
    
    public function dtActivity(Request $request)
    {
        $req    = $request->all();
        
        $datas   = null;  
        
        $datas = Activity::with('mesin', 'karyawan');
        
        $pin = Karyawan::find($req['sPin']);
        
        if($pin)
        {
            $datas->where('pin', $pin->pin);
        }
        else
        {
            $datas->where('pin', '');
        }
        

        if(!empty($req['sTanggal']))
        {
            $tgl = explode(' - ', $req['sTanggal']);

            $datas->whereBetween('tanggal', [reset($tgl).' 00:00:00', end($tgl).' 23:59:59']);
        }

        $datas->orderBy('id','desc');

        return  Datatables::of($datas)
            ->addColumn('action',function($datas)
            {
                $str = '';
                if(Auth::user()->type->nama == 'ADMIN')
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrow btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fas fa-pencil-alt"></i></button>';
                    $str    .= '</div>';
                }
                return $str;
            })
            ->addColumn('ping_status', function($datas)
            {
                if(!empty($datas->api_address))
                {
                    $req = new Client();   
                    $res = $req->request('GET', $datas->api_address.'/api/v1/ping?ip='.$datas->ip);
                    sleep(1);
                    $status = json_decode($res->getBody()->getContents())->status;

                    if($status)
                    {
                        return 0;
                    }
                    return 1;
                }
                else
                {
                    return $this->pingIp($datas->ip);
                }

                return 1;

            })
            ->editColumn('id', '{{$id}}')
            ->rawColumns(['action'])
            ->make(true);
        
    }
    
    public function select2(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = Mesin::where(function($q) use($term)
        {
            $q->where('kode','like','%'.$term.'%')
              ->orWhere('lokasi','like','%'.$term.'%')
              ->orWhere('keterangan','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->kode.' - '.$tag->lokasi.' - '.$tag->ip];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    private function pingIp($ip)
    {
        
        exec("ping -c 1 $ip", $output, $status);
        //exec("ping $ip", $output, $status);
        return $status;
    }

}
