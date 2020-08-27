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
        //
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
    public function destroy(MasterOption $masterOption)
    {
        //
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
            $formatted_tags[] = ['id' => $tag->id, 'text' => $tag->nama];
        }
        echo json_encode(array('items' => $formatted_tags));
    }
}
