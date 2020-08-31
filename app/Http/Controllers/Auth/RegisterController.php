<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
//    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('guest');
    }
    
    public function index()
    {
        return view('admin.administrator.users.index');
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
            $req = $request->all();
            $validation = null;
            if(empty($req['id']))
            {
                $validation = Validator::make($req, 
                    [
                        'username'  => 'required|unique:users',
                        'name'      => 'required',
                        'password' => 'required|min:6|confirmed',
                        'email'      => 'required|email',
                        'type_id' => 'required',
                        'perusahaan_id' => 'required',
                        'module' => 'required'
                    ],
                    [
                        'username.required'  => 'Username harus diisi.',
                        'username.unique'  => 'Username sudah ada di database.',
                        'name.required'     => 'Nama harus diisi.',
                        'password.required' => 'Password harus diisi.',
                        'password.min' => 'Password minimal 6 karekter.',
                        'password.confirmed' => 'Password tidak sama',
                        'email.required' => 'Email harus diisi',
                        'email.email' => 'Email tidak sesuai',
                        'type_id.required' => 'Tipe user harus diisi',
                        'perusahaan_id.required' => 'Perusahaan harus diisi',
                        'module.required' => 'Module harus diisi'
                    ]);
            }
            else
            {
                $validation = Validator::make($req, 
                    [
                        'username'  => ['required',
                        Rule::unique('users')->ignore($req['id'])],
                        'name'      => 'required',
                        'password' => 'confirmed',
                        'email'      => 'required|email',
                        'type_id' => 'required',
                        'perusahaan_id' => 'required',
                        'module' => 'required'
                    ],
                    [
                        'username.required'  => 'Username harus diisi.',
                        'username.unique'  => 'Username sudah ada di database.',
                        'name.required'     => 'Nama harus diisi.',
                        'password.required' => 'Password harus diisi.',
                        'password.min' => 'Password minimal 6 karekter.',
                        'password.confirmed' => 'Password tidak sama',
                        'email.required' => 'Email harus diisi',
                        'email.email' => 'Email tidak sesuai',
                        'type_id.required' => 'Tipe user harus diisi',
                        'perusahaan_id.required' => 'Perusahaan harus diisi',
                        'module.required' => 'Module harus diisi'
                    ]);
            }

            if($validation->fails())
            {
                echo json_encode(array(
                    'status' => 0,
                    'msg'   => $validation->errors()->all()
                ));
            }
            else
            {
                
//                dd(Module::ancestorsOf($req['module'][0])->pluck('id'));
                $datas = [
                        'username' => $req['username'],
                        'name' => $req['name'],
                        'email' => $req['email'],
                        'type_id' => $req['type_id'],
                        'perusahaan_id' => $req['perusahaan_id'],  
                        'updated_by' => Auth::user()->id,
                    ];
                if(!empty($req['password']))
                {
                    $datas['password'] = bcrypt($req['password']);
                }
                
                $id = null;
                if(empty($req['id']))
                {
                    $datas['created_by'] = Auth::user()->id;
                    $id = User::create($datas)->id;
                }
                else
                {
                    $id = $req['id'];
                    User::find($id)->fill($datas)->save();
                }
                
                
                if($id)
                {
                    $usr = User::find($id);
                    $usr->modules()->detach();
                    $headList = [];
                    foreach($req['module'] as $rMod => $vMod)
                    {
                        $hd = Module::ancestorsOf($vMod)->pluck('id');
                        if(in_array($hd, $headList))
                        {
                            $usr->modules()->attach($vMod);
                        }
                        else
                        {
                            $headList[] = $hd;
                            $usr->modules()->attach($hd);
                            $usr->modules()->attach($vMod);
                        }
                    }
                }
                
                echo json_encode(array(
                        'status' => 1,
                        'msg'   => 'Data berhasil disimpan'
                    ));
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
            User::find($req['sId'])->delete();
            
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
        
        $datas   = User::with('modules' , 'type', 'perusahaan');  
        
        if(!empty($req['search']))
        {
            $datas->where(function($q) use($req)
            {
                $q->where('username', $req['search']);
                $q->orWhere('name', $req['search']);
            });
        }
        $datas->orderBy('id','desc');
        
        return  Datatables::of($datas)               
                ->editColumn('id', '{{$id}}')
                ->make(true);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
