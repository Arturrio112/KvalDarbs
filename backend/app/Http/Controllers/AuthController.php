<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Profile;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request){
        $request->validated($request->all());
        if(!Auth::attempt($request->only(['email', 'password']))){
            return $this->error('','Credintial dont match', 401);
        }
        $user = User::where('email', $request->email)->first();
        return $this->success([
            'user'=>$user,
            'token'=>$user->createToken('API Token' . $user->name)->plainTextToken
        ]);
    }
    public function register(StoreUserRequest $request){
        $request->validated($request->all());
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'birthdate'=>$request->birthdate
        ]);
        $profile = Profile::create([
            'user_id'=>$user->id,
            'nickname'=>$user->name,
            'fontColor'=>'#000000',
            'borderColor'=> '#000000',
            'picture'=>'655f7aa731654_1700756135.png'
        ]);
        
        return $this->success([
            'user'=>$user,
            'profile'=>$profile,
            'token'=>$user->createToken('API Token' . $user->name)->plainTextToken
        ]);
    }
    public function logout(){
        return response()->json('This is my logout');
    }
}
