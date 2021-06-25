<?php

namespace App\Http\Controllers;

use App\CostCenter;
use App\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Validator;
use DB;

use PhpOffice\PhpSpreadsheet\IOFactory;

// use App\Http\Traits\TraitProses;

class CostCenterController extends Controller
{
    // use TraitProses;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.costcenter.index');
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
                    
                    CostCenter::create($req);
                    
                    echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
                }
                else
                {
                    $req['updated_by']   = Auth::user()->id;        
                    $req['updated_at']   = Carbon::now();
                    CostCenter::find($req['id'])->fill($req)->save();
                    
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
     * @param  \App\CostCenter  $cost_center
     * @return \Illuminate\Http\Response
     */
    public function show(CostCenter $cost_center)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CostCenter  $cost_center
     * @return \Illuminate\Http\Response
     */
    public function edit(CostCenter $cost_center)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CostCenter  $cost_center
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CostCenter $cost_center)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CostCenter  $cost_center
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $req = $request->all();
        try 
        {
            CostCenter::find($req['id'])->delete();
            
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
        
        $datas   = CostCenter::with('createdBy');  
        
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
    
    public function select2(Request $request)
    {        
        $ret    = array();
        $datas  = array();
        if($request->input('id'))
        {
            $datas = CostCenter::where('id',$request->input('id'));
        }
        else
        {
            $datas = CostCenter::where(function($q) use($request)
            {
                $q->where('kode','like','%'.$request->input('q').'%')
                  ->orWhere('deskripsi','like','%'.$request->input('q').'%');
            })->limit(50);
        }
        
        foreach($datas->get() as $tags)
        {
            $ret[] = array('id' => $tags->id, 
                            'text' => $tags->kode.' - '.$tags->deskripsi);
        }
        
        echo json_encode(array('items' => $ret));
    }
}
