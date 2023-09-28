<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddLikeRequest;
use App\Http\Requests\DeletePostRequest;
use App\Http\Requests\GetFileRequest;
use App\Http\Requests\RemoveLikeRequest;
use App\Http\Requests\RemoveRepostRequest;
use App\Http\Requests\ShowFollowedPosts;
use App\Http\Requests\ShowUsersReposts;
use App\Http\Requests\ShowUsersPosts;
use App\Http\Requests\StoreRepostRequest;
use App\Http\Requests\StorePostRequest;
use App\Models\Like;
use App\Models\Repost;
use App\Models\Statistic;
use App\Models\Follow;
use App\Models\Post;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    use HttpResponses;
    public function store(StorePostRequest $request)
    {
        Log::info('Received request', ['request' => $request->all()]);

        // Validate the request data
        $validatedData = $request->validated();

        // Initialize variables
        $fileFormat = '';
        $fileName = ''; // Initialize fileName
        $fileSize = 0;  // Initialize fileSize

        // Check if 'media' key exists in the request
        if ($request->hasFile('media')) {
            // Get the 'media' file from the request
            $media = $request->file('media');

            // Check if the 'media' file is valid
            if ($media->isValid()) {
                // Get the original file extension (e.g., jpg, png)
                $fileFormat = $media->getClientOriginalExtension();

                // Generate a unique filename
                $fileName = uniqid() . '_' . time() . '.' . $fileFormat;

                // Store the file in the public/media folder with the generated filename
                $media->storeAs('public/media', $fileName);

                // Get the file size
                $fileSize = $media->getSize();
            } else {
                // Handle invalid media file
                return $this->error([], 'Invalid media file', 400);
            }
        }

        
        $post = Post::create([
            'user_id' => $validatedData['user_id'],
            'text' => $validatedData['text'],
            'fileFormat' => $fileFormat,
            'fileName' => $fileName, // Set fileName
            'fileSize' => $fileSize, // Set fileSize
        ]);

        // Create associated statistics
        $stats = Statistic::create([
            'post_id' => $post->id,
        ]);

        return $this->success([
            'post' => $post,
        ], 'Post created successfully', 201);
    }
    public function getFile(GetFileRequest $request){
        $name = $request['fileName'];
        $res =asset('storage/media/' . $name);
        return $this->success([
            'media'=>$res
        ], 'Photo', 200);
    }
    private function filePath($name){
        return asset('storage/media/' . $name);
    }
    public function showAll(Request $request)
    {
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->orderByDesc('profile.verified') 
            ->orderByDesc('post.created_at') 
            ->get();

        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
            
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
            $post->profile = $profile;
        }

        $response = [
            'posts' => $posts
        ];

        return $this->success($response);
    }

    public function addLike(AddLikeRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];

        $like = Like::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->like +=1;
        $stat->save();
        return $this->success([
            'like' => $like
        ], 'Like added successfully', 201);
    }

    public function removeLike(RemoveLikeRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        $deleted = Like::where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->like -=1;
        $stat->save();
        if($deleted){
            return $this->success([], 'Like removed successfully', 201);
        }else{
            return $this->error([], 'Like removed unsuccessfully', 201);
        }
    }

    public function addPost(StoreRepostRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        $repost = Repost::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
        Log::info('Repost',['repost'=>$repost]);
        $stat = Statistic::where('post_id', $repost->post_id)->first();
        $stat->repost += 1;
        $stat->save();
        return $this->success([
            'repost' => $repost
        ], 'Repost added successfully', 201);
    }
    public function deleteRepost(RemoveRepostRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        $deleted = Repost::where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->repost -=1;
        $stat->save();
        if($deleted){
            return $this->success([], 'Repost removed successfully', 201);
        }else{
            return $this->error([], 'Repost removed unsuccessfully', 201);
        }
    }
    public function getAllReposts(Request $request){
        $reposts = Repost::with('post')->get();
        return $this->success([
            'reposts' => $reposts
        ]);
    }
    public function deletePost(DeletePostRequest $request)
    {
        $postId = $request->input('post_id');
        $userId = $request->input('user_id');

        $post = Post::findOrFail($postId);

        if ($post->user_id === $userId) {
            
            if ($post->fileName) {
                $mediaPath = 'public/media/' . $post->fileName;
                Log::info('FMedia path', ['path' => $mediaPath]);
                
                if (Storage::exists($mediaPath)) {
                    Storage::delete($mediaPath);
                    Log::info('File Deleted Successfully', ['path' => $mediaPath]);
                } else {
                    Log::error('File Not Found', ['path' => $mediaPath]);
                }
            }

            $post->delete();
            return $this->success([], 'Post and associated media removed successfully', 201);
        } else {
            return $this->error([], 'Unauthorized to delete the post', 403);
        }
    }
    public function getUsersPosts(ShowUsersPosts $request){
        $userId = $request->input('user_id');
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->where('post.user_id', $userId)
            ->orderByDesc('post.created_at')
            ->get();
        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
           
            $post->profile = $profile;
        }
        $response = [
            'posts' => $posts,
        ];
        return $this->success($response);
    }
    public function getUsersReposts(ShowUsersReposts $request){
        $userId = $request->input('user_id');
        
        $reposts = Repost::select('repost.*')
            ->leftJoin('post', 'repost.post_id', '=', 'post.id')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'post.statistic', 'post.comment', 'post.like', 'user.profile'])
            ->where('repost.user_id', $userId)
            ->orderByDesc('repost.created_at')
            ->get();
    
        
        foreach ($reposts as $repost) {
            $post = $repost->post;
            $user = $post->user;
            $profile = $user->profile;
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
            $userR = $repost->user;
            $profileR = $userR->profile;
            if(!filter_var($profileR->picture, FILTER_VALIDATE_URL)){
                $profileR->picture = $this->filePath($profileR->picture);
            }
            $repost->user->profile = $profileR;
    
    
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
    
            $post->profile = $profile;
        }
    
        $response = [
            'reposts' => $reposts,
        ];
    
        return $this->success($response);
    }
    public function getFollowedPosts(ShowFollowedPosts $request)
    {
        $userId = $request->input('userId');

        $followedUsers = Follow::where('follower_id', $userId)->pluck('followed_id')->toArray();

        
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->whereIn('post.user_id', $followedUsers)
            ->whereNotIn('post.user_id', [$userId]) 
            ->orderByDesc('post.created_at')
            ->get();

        
        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
    
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
    
            
            $post->profile = $profile;
        }
        $response = [
            'posts' => $posts,
        ];

        return $this->success($response);
    }
}
