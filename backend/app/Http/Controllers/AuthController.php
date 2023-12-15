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
    //Funkcija, kas nodrošina lietotāja pieslēgšanos
    public function login(LoginUserRequest $request){
        $request->validated($request->all());
        if(!Auth::attempt($request->only(['email', 'password']))){
            return $this->error('','Credintial dont match', 401);
        }
        $user = User::where('email', $request->email)->first();
        //Tiek izveidots autentifikācijas talons un atgriezts kopā ar lietotāja datiem
        return $this->success([
            'user'=>$user,
            'token'=>$user->createToken('API Token' . $user->name)->plainTextToken
        ]);
    }
    //Funkcija, kas nodrošina lietotāja reģistrēšanos
    public function register(StoreUserRequest $request){
        $request->validated($request->all());
        //Izveido jaunu lietotāju
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'birthdate'=>$request->birthdate
        ]);
        //izveido lietotāja profilu
        $profile = Profile::create([
            'user_id'=>$user->id,
            'nickname'=>$user->name,
            'fontColor'=>'#000000',
            'borderColor'=> '#000000',
            'picture'=>'655f7aa731654_1700756135.png'
        ]);
        //Atgriež lietotāja, profila un autentifikācijas talona datus
        return $this->success([
            'user'=>$user,
            'profile'=>$profile,
            'token'=>$user->createToken('API Token' . $user->name)->plainTextToken
        ]);
    }
    //Funkcija, kas ļauj atslēgties no sistēmas
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return $this->success([], 'User logged out successfully');
    }
}
