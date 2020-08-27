<?php

namespace App\Http\Controllers;

use App\JamKerja;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

class JamKerjaController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.jamkerja.index');
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
                    'libur'   => 'required',
                    'pendek'   => 'required',
                    'istirahat'   => 'required',
                    'jam_masuk' => 'date_format:H:i',
                    'jam_keluar' => 'date_format:H:i',
                    'warna' => 'required'
                ],
                [
                    'kode.required'  => 'Kode harus diisi.',
                    'libur.required'  => 'Libur harus diisi.',
                    'pendek.required'  => 'Jam Pendek harus diisi.',
                    'istirahat.required'  => 'Istirahat harus diisi.',
                    'jam_masuk.date_format' => 'Format jam masuk salah.',
                    'jam_keluar.date_format' => 'Format jam keluar salah.',
                    'warna.required' => 'Warna harus diisi.',
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
                    
                    JamKerja::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    JamKerja::find($req['id'])->fill($req)->save();
                    
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

    /**
     * Display the specified resource.
     *
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function show(JamKerja $jamkerja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function edit(JamKerja $jamkerja)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JamKerja $jamkerja)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\JamKerja  $jamkerja
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            JamKerja::find($req['id'])->delete();
            
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
        
        $datas   = JamKerja::with(['createdBy']);  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('kode', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->addColumn('action',function($datas)
                {
                    $str    = '<div class="btn-group">';
                    $str    .= '<button class="editrow btn btn-primary" data-toggle="modal" data-target="#modal-form" title="Ubah"><i class="fas fa-pencil-alt"></i></button>';
                    $str    .= '<button class="delrow btn btn-danger" title="Hapus"><i class="fas fa-eraser"></i></button>';
                    $str    .= '</div>';

                    return $str;
                })
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function select2(Request $request)
    {        
        $ret    = array();
        $data  = array();
        if($request->input('id'))
        {
            $data = JamKerja::where('id',$request->input('id'));
        }
        else
        {
            $data = JamKerja::where('kode','like','%'.$request->input('q').'%')->limit(10);
        }
        $datas = $data->get();
        
        foreach($datas as $tags)
        {
            $ret[] = array('id' => $tags->id, 
                            'kode' => $tags->kode, 
                            'jam_masuk' => $tags->jam_masuk, 
                            'jam_keluar' => $tags->jam_keluar, 
                            'warna' => $tags->warna);
        }
        
        echo json_encode(array('items' => $ret));
    }
}
