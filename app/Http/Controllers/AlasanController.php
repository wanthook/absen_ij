<?php

namespace App\Http\Controllers;

use App\Alasan;
use App\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;
use DB;

use App\Http\Traits\TraitProses;

class AlasanController extends Controller
{
    use TraitProses;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.alasan.index');
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
                    'deskripsi'      => 'required',
                ],
                [
                    'kode.required'  => 'Kode harus diisi.',
                    'deskripsi.required'     => 'Nama harus diisi.',
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
                    
                    Alasan::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    Alasan::find($req['id'])->fill($req)->save();
                    
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
    
    public function storeAlasanKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
                'sTanggal'   => 'required',
                'sData'   => 'required'
            ],
            [
                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sData.required'  => 'Tidak ada data yang dimasukkan.'
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
                $req = $request->only(['sTanggal', 'sData']);
                
                foreach($req['sData'] as $k => $v)
                {
                    if(isset($v['sKar']))
                    {
                        $kar = Karyawan::find($v['sKar']);

                        $pvt = $kar->alasan()->wherePivot('tanggal', $req['sTanggal']);

                        if(isset($v['sAlasanOld']))
                        {
                            if($v['sAlasanOld'])
                            {
                                $pvt->detach($v['sAlasanOld']);
                            }
                        }

                        $attach = ['tanggal' => $req['sTanggal'], 
                                   'keterangan' => $v['sKeterangan'], 
                                   'waktu' => $v['sWaktu'],
                                   'created_by' => Auth::user()->id, 
                                   'created_at' => Carbon::now()];

                        $kar->alasan()->attach($v['sAlasan'], $attach);

                        if($this->cekProses($kar->id, $req['sTanggal']))
                        {
                            $this->prosesAbsTanggal($kar->id, $req['sTanggal']);
                        }
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
    
    public function storeAlasanRangeKaryawan(Request $request)
    {
        try
        {
            $validation = Validator::make($request->all(), 
            [
//                'sTanggal'   => 'required',
                'sData'   => 'required'
            ],
            [
//                'sTanggal.required'  => 'Tanggal harus diisi.',
                'sData.required'  => 'Tidak ada data yang dimasukkan.'
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
                $req = $request->only(['sData']);
                
                foreach($req['sData'] as $k => $v)
                {
                    if(isset($v['sKar']) && 
                       isset($v['sTanggalAwal']) &&
                       isset($v['sTanggalAkhir']) &&
                       isset($v['sAlasan']))
                    {
                        $kar = Karyawan::find($v['sKar']);

                        $pvt = $kar->alasanRange()->wherePivot('tanggal_awal', $v['sTanggalAwal'])
                                ->wherePivot('tanggal_akhir', $v['sTanggalAkhir']);

                        if(isset($v['sAlasanOld']))
                        {
                            if($v['sAlasanOld'])
                            {
                                $pvt->detach($v['sAlasanOld']);
                            }
                        }

                        $attach = ['tanggal_awal' => $v['sTanggalAwal'], 
                                   'tanggal_akhir' => $v['sTanggalAkhir'], 
                                   'keterangan' => $v['sKeterangan'], 
                                   'waktu' => $v['sWaktu'],
                                   'created_by' => Auth::user()->id, 
                                   'created_at' => Carbon::now()];

                        $kar->alasanRange()->attach($v['sAlasan'], $attach);
                        $this->prosesAbsTanggalRange($kar->id, $v['sTanggalAwal'], $v['sTanggalAkhir']);
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Alasan  $alasan
     * @return \Illuminate\Http\Response
     */
    public function show(Alasan $alasan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Alasan  $alasan
     * @return \Illuminate\Http\Response
     */
    public function edit(Alasan $alasan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Alasan  $alasan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Alasan $alasan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Alasan  $alasan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            Alasan::find($req['id'])->delete();
            
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

                $kar = Karyawan::find($req['sKar']);
                
                $par = $kar->alasan()->wherePivot('tanggal', $req['sTanggal']);
                
//                dd($par->detach());
//                
                if($par)
                {
                    $par->detach($req['sAlasan']);
                }
                $this->prosesAbsTanggal($kar->id, $req['sTanggal']);
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
    
    public function dt(Request $request)
    {
        $req    = $request->all();
        
        $datas   = Alasan::with(['createdBy']);  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('kode', $req['search']);
                $q->orWhere('deskripsi', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrow btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fas fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrow btn btn-danger btn-xs" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';

                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function tableTransaksiAlasan(Request $request)
    {
        $ret = [];
        $total = 0;
        $req    = $request->all();
        
        $datas = DB::table('alasan_karyawan')
                  ->selectRaw('alasan_karyawan.tanggal as tanggal, '
                          . 'alasan_karyawan.alasan_id as alasan_id, '
                          . 'alasan_karyawan.waktu as waktu, '
                          . 'alasan_karyawan.keterangan as keterangan, '
                          . 'karyawans.id as karyawan_id, '
                          . 'karyawans.pin as pin, '
                          . 'karyawans.nik as nik, '
                          . 'karyawans.nama as nama, '
                          . 'divisis.kode as divisi_kode, '
                          . 'divisis.deskripsi as divisi_deskripsi, '
                          . 'alasans.kode as alasan_kode, '
                          . 'alasans.deskripsi as alasan_deskripsi,'
                          . 'alasan_karyawan.created_at as id')
                  ->join('karyawans', 'karyawans.id', '=', 'alasan_karyawan.karyawan_id')
                  ->join('alasans', 'alasans.id', '=', 'alasan_karyawan.alasan_id')
                  ->join('divisis', 'divisis.id', '=', 'karyawans.divisi_id')
                  ->orderBy('alasan_karyawan.tanggal', 'desc')
                  ->groupBy('alasan_karyawan.alasan_id', 'alasan_karyawan.karyawan_id');
        
        $total = DB::table('alasan_karyawan')
                        ->selectRaw('count(*) as cnt');
        
        if(isset($req['sTanggal']))
        {
            $datas->where('alasan_karyawan.tanggal',$req['sTanggal']);
            $total->where('alasan_karyawan.tanggal',$req['sTanggal']);
            
        }
        
        if(Auth::user()->type->nama == 'REKANAN')
        {
            $datas->where('karyawans.perusahaan_id', Auth::user()->perusahaan_id);
            $total->where('karyawans.perusahaan_id', Auth::user()->perusahaan_id);
        }
        
        if(isset($req['page']))
        {
            $datas->offset($req['page']);
        }
        
        if(isset($req['rows']))
        {
            $datas->limit($req['rows']);
        }
        
        $res = $datas->get();
        
        foreach($res as $val)
        {
            $ret[] = [
                'sKar' => $val->karyawan_id,
                'sKarText' => $val->pin.' - '.$val->nama,
                'sAlasan' => $val->alasan_id,
                'sAlasanOld' => $val->alasan_id,
                'sAlsText' => $val->alasan_kode.' - '.$val->alasan_deskripsi,
                'sAlasanKode' => $val->alasan_kode,
                'sAlasanNama' => $val->alasan_deskripsi,
                'sWaktu' => $val->waktu,
                'tanggal' => $val->tanggal,
                'sKeterangan' => $val->keterangan
            ];
        }
        
        echo json_encode(['rows' => $ret, 'total' => $total->first()->cnt]);
    }
    
    public function tableTransaksiAlasanRange(Request $request)
    {
        $ret = [];
        $total = 0;
        $req    = $request->all();
        
        $datas = DB::table('alasan_karyawan_range')
                  ->selectRaw('alasan_karyawan_range.tanggal_awal as tanggal_awal, '
                          . 'alasan_karyawan_range.tanggal_akhir as tanggal_akhir, '
                          . 'alasan_karyawan_range.alasan_id as alasan_id, '
                          . 'alasan_karyawan_range.waktu as waktu, '
                          . 'alasan_karyawan_range.keterangan as keterangan, '
                          . 'karyawans.id as karyawan_id, '
                          . 'karyawans.pin as pin, '
                          . 'karyawans.nik as nik, '
                          . 'karyawans.nama as nama, '
                          . 'divisis.kode as divisi_kode, '
                          . 'divisis.deskripsi as divisi_deskripsi, '
                          . 'alasans.kode as alasan_kode, '
                          . 'alasans.deskripsi as alasan_deskripsi,'
                          . 'alasan_karyawan_range.created_at as id')
                  ->join('karyawans', 'karyawans.id', '=', 'alasan_karyawan_range.karyawan_id')
                  ->join('alasans', 'alasans.id', '=', 'alasan_karyawan_range.alasan_id')
                  ->join('divisis', 'divisis.id', '=', 'karyawans.divisi_id')
                  ->orderBy('alasan_karyawan_range.tanggal_awal', 'desc')
                  ->groupBy('alasan_karyawan_range.alasan_id', 'alasan_karyawan_range.karyawan_id');
        
        $total = DB::table('alasan_karyawan_range')
                        ->selectRaw('count(*) as cnt');
        
        if(isset($req['sRangeTanggal']))
        {
            $datas->where('alasan_karyawan_range.tanggal_awal', '<=',$req['sRangeTanggal']);
            $total->where('alasan_karyawan_range.tanggal_akhir', '>=',$req['sRangeTanggal']);
            
        }
        
        if(Auth::user()->type->nama == 'REKANAN')
        {
            $datas->where('karyawans.perusahaan_id', Auth::user()->perusahaan_id);
            $total->where('karyawans.perusahaan_id', Auth::user()->perusahaan_id);
        }
        
        if(isset($req['page']))
        {
            $datas->offset($req['page']);
        }
        
        if(isset($req['rows']))
        {
            $datas->limit($req['rows']);
        }
        
        $res = $datas->get();
        
        foreach($res as $val)
        {
            $ret[] = [
                'sRangeKar' => $val->karyawan_id,
                'sRangeKarText' => $val->pin.' - '.$val->nama,
                'sRangeAlasan' => $val->alasan_id,
                'sRangeAlasanOld' => $val->alasan_id,
                'sRangeAlsText' => $val->alasan_kode.' - '.$val->alasan_deskripsi,
                'sRangeAlasanKode' => $val->alasan_kode,
                'sRangeAlasanNama' => $val->alasan_deskripsi,
                'sRangeWaktu' => $val->waktu,
                'sRangeTanggalAwal' => $val->tanggal_awal,
                'sRangeTanggalAkhir' => $val->tanggal_akhir,
                'sRangeKeterangan' => $val->keterangan
            ];
        }
        
        echo json_encode(['rows' => $ret, 'total' => $total->first()->cnt]);
    }
    
    public function select2(Request $request)
    {        
        $ret    = array();
        $data  = array();
        if($request->input('id'))
        {
            $data = Alasan::where('id',$request->input('id'));
        }
        else
        {
            $data = Alasan::where(function($q) use($request)
            {
                $q->where('kode','like','%'.$request->input('q').'%')
                  ->orWhere('deskripsi','like','%'.$request->input('q').'%');
            })->limit(20);
        }
        $datas = $data->where('show','Y')->get();
        
        foreach($datas as $tags)
        {
            $ret[] = array('id' => $tags->id, 
                            'kode' => $tags->kode, 
                            'sAlsText' => $tags->kode.' - '.$tags->deskripsi,
                            'deskripsi' => $tags->deskripsi, 
                            'warna' => $tags->warna);
        }
        
        echo json_encode(array('items' => $ret));
    }
    
    public function selectAlasan(Request $request)
    {        
        $ret    = array();
        $data  = Alasan::where('show','Y');
        
        $req = $request->all();
        
        if(isset($req['q']))
        {
            $data->where(function($q) use($req)
            {
                $q->where('kode','like','%'.$req['q'].'%')
                  ->orWhere('deskripsi','like','%'.$req['q'].'%');
            });
        }
        
        foreach($data->get() as $tags)
        {
            $ret[] = array('id' => $tags->id, 
                            'kode' => $tags->kode, 
                            'sAlsText' => $tags->kode.' - '.$tags->deskripsi,
                            'deskripsi' => $tags->deskripsi, 
                            'warna' => $tags->warna);
        }
        
        return  response()->json($ret);
    }
}
