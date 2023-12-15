<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFollowRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\GetMentionedUsersRequest;
use App\Http\Requests\RemoveFollowRequest;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\ShowOneUserRequest;
use App\Models\Follow;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use HttpResponses;
    //Funkcija, kas atgriež faila linku
    private function filePath($name){
        return asset('storage/media/' . $name);
    }
    //Funckija, kas atgriež lietotāja datus
    public function getUserData(Request $request){
        $user = $request->user();
        if ($user) {
            $profile = DB::table('profile')->where('user_id', $user->id)->first();
            $profile->user = $user;
            if($profile->picture){
                $profile->picture = asset('storage/media/' . $profile->picture);
            }
            return $this->success(['profile' => $profile]);
        } else {
            return $this->error('User not found', [], 404);
        }
    }
    //Funkcija, kas ļauj izdzēst lietotāju
    public function deleteUser(DeleteUserRequest $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if(!$user){
            return $this->error('User not found',[], 404);
        }
        //tiek izdzēsts lietotāja profils
        if ($user->profile) {
            $user->profile->delete();
        }
        //tiek izdzēsts lietotāja autentifikācijas talons
        $user->tokens()->delete();
        //tiek izdzēsts lietotājs
        $user->delete();
        return $this->success([], 'deleted');
    }
    //Funkcija, kas atgriež viena lietotāja profilu
    public function showOne(ShowOneUserRequest $request){
        $request->validated($request->all());
        $userId = $request->input('user_id');
        //Atgriež lietotāju, tā profilu un sekotājus
        $user = User::with('profile', 'followers')
            ->withCount(['followers','repost','posts'])
            ->where('id', $userId)
            ->first();
        //Pievieno lietotāja attēlu
        if($user->profile->picture){
            $user->profile->picture = asset('storage/media/' . $user->profile->picture);
        }
        if(!$user){
            return $this->error('User not found',[], 404);
        }
        return $this->success(['user' => $user]);
    }
    //Funkcija, kas ļauj sekot lietotājam 
    public function addFollow(AddFollowRequest $request){
        $follower_id = $request->input('follower_id');
        $followed_id = $request->input('followed_id');
        //Tiek sekots lietotājam
        $follow = Follow::create([
            'follower_id' =>$follower_id,
            'followed_id'=>$followed_id
        ]);
        return $this->success([
            'follow' => $follow
        ], 'Follow added successfully', 201);
    }
    //Funkcija, kas ļauj atsekot lietotājam
    public function removeFollow(RemoveFollowRequest $request){
        $follower_id = $request->input('follower_id');
        $followed_id = $request->input('followed_id');
        //Tiek atsekots lietotājam
        $deleted = Follow::where('follower_id', $follower_id)
            ->where('followed_id', $followed_id)
            ->delete();
        if($deleted){
            return $this->success([], 'Follow removed successfully', 201);
        }else{
            return $this->error([], 'Follow removed unsuccessfully', 201);
        }
    }
    //Funkcija, kas ļauj meklēt lietotājus
    public function searchUser(SearchUserRequest $request){
        $name = $request->input('username');
        //Atrod lietotāju pēc ievadītā teksta
        $users = User::with('profile')
        ->whereRaw('LOWER(name) LIKE ?', [strtolower($name) . '%'])
            ->limit(5)
            ->get();
        //Ja neatrod lietotāju, tiek atgriezts paziņojums
        if($users->isEmpty()){
            return $this->error('Users not found',[], 404);
        }
        //Katram atrastajam lietotājam pievieno attēlu
        foreach($users as $user ){
            $user->profile->picture = asset('storage/media/' . $user->profile->picture);
        }
        return $this->success([
            'users'=>$users
        ], 'Users found successfully', 201);
    }
    //Funkcija, kas ļauj rediģēt lietotāja profilu
    public function editProfile(EditProfileRequest $request){
        $profileId = $request->input('profileId');
        $profile = Profile::where('id', $profileId)->first();
        
        if (!$profile) {
            return $this->error('Profile not found', [], 404);
        }
        //Rediģē profilu pēc ievadītajiem datiem
        if ($request->has('fontColor')) {
            $profile->fontColor = $request->input('fontColor');
        }
        if ($request->has('borderColor')) {
            $profile->borderColor = $request->input('borderColor');
        }
        
        if ($request->has('profileName')) {
            $profile->nickname = $request->input('profileName');
        }
    
        
        if ($request->hasFile('profilePicture')) {
            $profilePicture = $request->file('profilePicture');
            if ($profilePicture->isValid()) {
                
                $fileFormat = $profilePicture->getClientOriginalExtension();

                $fileName = uniqid() . '_' . time() . '.' . $fileFormat;

                $profilePicture->storeAs('public/media', $fileName);
    
                $profile->picture = $fileName;
            } else {
                return $this->error('Invalid profile picture file', [], 400);
            }
        }
        //Saglabā veiktās izmaiņas
        $profile->save();
    
        return $this->success(['profile'=>$profile], 'Profile updated successfully', 200);
    }
    //Funkcija, kas atgriež visus lietotājus
    public function getAllUsers(Request $request){
        $users = User::with('profile')->get();
        //Katra lietotāja profilam pievieno tā attēlu
        foreach($users as $user){
            $profile = $user->profile;
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
        }
        // You can further customize the response if needed
        return $this->success(['users'=>$users], 'Users fetched successfully', 200);
    }
    //Funkcija, kas atrod lietotājus, kurus mēģina pieminēt
    public function findUserForMention(Request $request){
        $q = $request->input('query');
        //Atrod lietotājus pēc ievadītā teksta
        $users = User::whereRaw('LOWER(name) LIKE ?', [strtolower($q) . '%'])->limit(5)->get();
        if($users->isEmpty()){
            return $this->error('Users not found',[], 404);
        }
        return $this->success([
            'users'=>$users
        ], 'Users found successfully', 201);
    }
    //Funkcija, kas atgriež lietotājus, kuri ir pieminēti
    public function getMentionedUsers(GetMentionedUsersRequest $request){
        $usernames = $request->input('usernames');
        $usernamesArray = explode(',', $usernames);
        //Atrod visus lietotājus, kuri ir pieminēti
        $mentionedUsers = User::whereIn('name', $usernamesArray)->get();
        return $this->success([
            'users' => $mentionedUsers,
        ], 'Mentioned users fetched successfully', 200);
    }
    
}
