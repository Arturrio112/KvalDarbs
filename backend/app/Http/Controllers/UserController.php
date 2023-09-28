<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFollowRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\EditProfileRequest;
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
    public function deleteUser(DeleteUserRequest $request, $id)
    {
        $userId = $id;
        $user = User::find($userId);
        if(!$user){
            return $this->error('User not found',[], 404);
        }
        if ($user->profile) {
            $user->profile->delete();
        }
        $user->tokens()->delete();
        $user->delete();
        return $this->success([], 'deleted');
    }

    public function showOne(ShowOneUserRequest $request){
        $request->validated($request->all());
        $userId = $request->input('user_id');
        $user = User::with('profile', 'followers')
            ->withCount(['followers','repost','posts'])
            ->where('id', $userId)
            ->first();
        if($user->profile->picture){
            $user->profile->picture = asset('storage/media/' . $user->profile->picture);
        }
        if(!$user){
            return $this->error('User not found',[], 404);
        }
        return $this->success(['user' => $user]);
    }
    public function addFollow(AddFollowRequest $request){
        $follower_id = $request->input('follower_id');
        $followed_id = $request->input('followed_id');
        $follow = Follow::create([
            'follower_id' =>$follower_id,
            'followed_id'=>$followed_id
        ]);
        return $this->success([
            'follow' => $follow
        ], 'Follow added successfully', 201);
    }
    public function removeFollow(RemoveFollowRequest $request){
        $follower_id = $request->input('follower_id');
        $followed_id = $request->input('followed_id');
        $deleted = Follow::where('follower_id', $follower_id)
            ->where('followed_id', $followed_id)
            ->delete();
        if($deleted){
            return $this->success([], 'Follow removed successfully', 201);
        }else{
            return $this->error([], 'Follow removed unsuccessfully', 201);
        }
    }
    public function searchUser(SearchUserRequest $request){
        $name = $request->input('username');
        $users = User::with('profile')
        ->whereRaw('LOWER(name) LIKE ?', [strtolower($name) . '%'])
            ->get();
        if($users->isEmpty()){
            return $this->error('Users not found',[], 404);
        }
        return $this->success([
            'users'=>$users
        ], 'Users found successfully', 201);
    }
    public function editProfile(EditProfileRequest $request){
        $profileId = $request->input('profileId');
        $profile = Profile::where('id', $profileId)->first();
        
        if (!$profile) {
            return $this->error('Profile not found', [], 404);
        }
    
        // Update the profileName if it's provided in the request.
        if ($request->has('profileName')) {
            $profile->nickname = $request->input('profileName');
        }
    
        // Update the profilePicture if it's provided in the request.
        if ($request->hasFile('profilePicture')) {
            $profilePicture = $request->file('profilePicture');
            if ($profilePicture->isValid()) {
                // Get the original file extension (e.g., jpg, png)
                $fileFormat = $profilePicture->getClientOriginalExtension();
    
                // Generate a unique filename
                $fileName = uniqid() . '_' . time() . '.' . $fileFormat;
    
                // Store the file in the desired folder with the generated filename
                $profilePicture->storeAs('public/media', $fileName);
    
                // Update the 'picture' attribute in the profile
                $profile->picture = $fileName;
            } else {
                // Handle invalid profile picture file
                return $this->error('Invalid profile picture file', [], 400);
            }
        }
    
        $profile->save();
    
        return $this->success(['profile'=>$profile], 'Profile updated successfully', 200);
    }
    
}
