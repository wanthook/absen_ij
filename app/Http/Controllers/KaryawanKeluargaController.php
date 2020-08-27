<?php

namespace App\Http\Controllers;

use App\KaryawanKeluarga;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

class KaryawanKeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                    'nama_keluarga'   => 'required',
                    'relasi_keluarga'      => 'required',
                ],
                [
                    'nama_keluarga.required'  => 'Nama harus diisi.',
                    'relasi_keluarga.required'     => 'Relasi harus diisi.',
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
                'msg'   => 'Data gagal disimpan'
            ));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\KaryawanKeluarga  $karyawanKeluarga
     * @return \Illuminate\Http\Response
     */
    public function show(KaryawanKeluarga $karyawanKeluarga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\KaryawanKeluarga  $karyawanKeluarga
     * @return \Illuminate\Http\Response
     */
    public function edit(KaryawanKeluarga $karyawanKeluarga)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\KaryawanKeluarga  $karyawanKeluarga
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, KaryawanKeluarga $karyawanKeluarga)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\KaryawanKeluarga  $karyawanKeluarga
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
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
        
        $datas   = KaryawanKeluarga::with(['relasi', 'jenkel', 'agama','createdBy']);  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('nama', $req['search']);
                $q->orWhere('deskripsi', $req['search']);
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
}
