<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;

//use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
//use Tymon\JWTAuth\Contracts\JWTSubject;
//use Tymon\JWTAuth\PayloadFactoty;
//use Tymon\JWTAuth\JWTManager;

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
                'username' => $request->json()->get('username'),
                'password' => Hash::make($request->json()->get('password')),
                'name'      => $request->json()->get('name'),
                'email'     => $request->json()->get('email'),
                'type'      => $request->json()->get('type'),
            ]
        );

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'),201);
    }
    
    public function logins(Request $request)
    {
        $credentials = request(['username', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function getAuthenticatedUser()
    {
        try
        {
            if(! $user == JWTAuth::parseToken()->authenticate())
            {
                return response()->json(['user_not_found'],404);
            }
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

        return response()->json(compact('user'));
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
        // $credential = $request->json()->all();
        $credential = $request->only('username', 'password');

        try
        {
            if($token == $this->guard()->attempt($credentials))
            {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        }
        catch(JWTException $e)
        {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }
    
    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }
    
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
