<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\PayloadFactoty;
use Tymon\JWTAuth\JWTManager as JWT;

class UserController extends Controller
{
//    protected $username = 'username'; 
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        $this->middleware('auth:api', ['except' => ['login']]);
//    }
    
    public function register(Request $request)
    {
        // protected $username;
        $validator = Validator::make($request->json()->all(),
        [
            'username'  => 'required|string|max:255',
            'name'      => 'required|string|max:255',
            'password'  => 'required|string|min:6|confirmed'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(),400);
        }

        $user = User::create(
            [
                'username'  => $request->json()->get('username'),
                'password'  => Hash::make($request->json()->get('password')),
                'name'      => $request->json()->get('name'),
                'email'     => $request->json()->get('email'),
                'type'      => $request->json()->get('type'),
            ]
        );

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'),201);
    }  
    
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);
        try
        {
            $token = JWTAuth::attempt($credentials);
            if(! $token)
            {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }

            return response()->json(compact('token'));
        }
        catch(JWTException $e)
        {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
    }

    public function getAuthenticatedUser()
    {
        try
        {
            $user = JWTAuth::parseToken()->authenticate();
            if(! $user)
            {
                return response()->json(['user_not_found'],404);
            }

            return response()->json(compact('user'));            
            
        }
        catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e)
        {
            return response()->json(['token_expire'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\TokenInvalidException $e)
        {
            return response()->json(['token_invalid'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\JWTException $e)
        {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    } 
}
