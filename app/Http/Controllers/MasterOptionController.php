<?php

namespace App\Http\Controllers;

use App\MasterOption;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;

class MasterOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.administrator.master_option.index');
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
                    'nama'   => 'required',
                    'deskripsi'   => 'required',
                    'kode'   => 'required',
                ],
                [
                    'nama.required'  => 'Nama harus diisi.',
                    'deskripsi.required'  => 'Deskripsi harus diisi.',
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
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    $req['created_by']   = Auth::user()->id;
                    $req['created_at']   = Carbon::now();
                    
                    MasterOption::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    MasterOption::find($req['id'])->fill($req)->save();
                    
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
     * @param  \App\MasterOption  $masterOption
     * @return \Illuminate\Http\Response
     */
    public function show(MasterOption $masterOption)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MasterOption  $masterOption
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterOption $masterOption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MasterOption  $masterOption
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterOption $masterOption)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MasterOption  $masterOption
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            MasterOption::find($req['id'])->delete();
            
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
        
        $datas   = MasterOption::with(['createdBy']);  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('nama', $req['search']);
                $q->orWhere('deskripsi', $req['search']);
                $q->orWhere('kode', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }
    
    public function select2agama(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','AGAMA')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2jenkel(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','JENKEL')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama.' - '.$tag->deskripsi];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2goldar(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','GOLDAR')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2kawin(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','KAWIN')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2relasi(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','RELASI')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2keteranganstatus(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','KETSTAT')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->nama, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2karyawanstatus(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','STATUS')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama.' - '.$tag->deskripsi, 'deskripsi' => $tag->deskripsi];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2tipeuser(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','USERTYPE')->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
    
    public function select2jenissalary(Request $request)
    {
        $tags = null;
        
        $term = trim($request->input('q'));
        $tags = MasterOption::where(function($q) use($term)
        {
            $q->where('nama','like','%'.$term.'%')
              ->orWhere('deskripsi','like','%'.$term.'%')
              ->orWhere('id',$term);
        })->where('kode','SALARY')->where('nilai',null)->limit(100)->get();
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->deskripsi];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
}
