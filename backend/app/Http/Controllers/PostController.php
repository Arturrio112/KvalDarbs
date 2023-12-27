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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    use HttpResponses;
    //Funkcija, kas ļauj saglabāt rakstu
    public function store(StorePostRequest $request)
    {
        $validatedData = $request->validated();
        $fileFormat = '';
        $fileName = ''; 
        $fileSize = 0;  

        // Pārbauda vai ir pievienots attēls
        if ($request->hasFile('media')) {
            
            $media = $request->file('media');

            // Pārbauda vai attēls ir augšupielādēts bez kļūdām
            if ($media->isValid()) {
                // Iegūst faila tipu
                $fileFormat = $media->getClientOriginalExtension();

                // Izveido faila vārdu
                $fileName = uniqid() . '_' . time() . '.' . $fileFormat;

                // Saglabā failu failu sistēmā
                $media->storeAs('public/media', $fileName);

                //Iegūst faila izmēru
                $fileSize = $media->getSize();
            } else {
                // Atgriež kļūdu
                return $this->error([], 'Invalid media file', 400);
            }
        }

        //Saglabā rakstu
        $post = Post::create([
            'user_id' => $validatedData['user_id'],
            'text' => $validatedData['text'],
            'fileFormat' => $fileFormat,
            'fileName' => $fileName, 
            'fileSize' => $fileSize, 
        ]);

        // Izveido failam statistiku
        $stats = Statistic::create([
            'post_id' => $post->id,
        ]);
        //Atgriež raksta datus
        return $this->success([
            'post' => $post,
        ], 'Post created successfully', 201);
    }
    //Funkcija, kas atgriež failu no failu sistēmas
    public function getFile(GetFileRequest $request){
        $name = $request['fileName'];
        $res =asset('storage/media/' . $name);
        return $this->success([
            'media'=>$res
        ], 'Photo', 200);
    }
    //Funkcija, kas atgriež faila linku
    private function filePath($name){
        return asset('storage/media/' . $name);
    }
    //Funkcija, kas ļauj apskatīt visus rakstus
    public function showAll(Request $request)
    {   
        $dayAgo = Carbon::now()->subDay();
        //Atgriež visus rakstus, kas ir jaunāki par dienu un sakārto tos pēc vecumu
        // un pēc tā
        //vai lietotājas ir verificēts
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->where('post.created_at','>',$dayAgo)
            ->orderByDesc('profile.verified') 
            ->orderByDesc('post.created_at') 
            ->get();
        //Atgriež visus rakstus, kas ir vecāki par dienu un sakārto tos pēc vecuma
        $olderPosts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->where('post.created_at', '<=', $dayAgo) 
            ->orderByDesc('post.created_at') 
            ->get();
        //Abus raksta masīvus saliek vienā
        $posts = $posts->merge($olderPosts);    

        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            //Atrod raksta autora profila attēlu
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
            //Katram raksta komentāram pievieno tā autoru, autora profilu un attēlu
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
            //Palielina rakstam apskatu skaitu statistikā
            $post->profile = $profile;
            $stat =  Statistic::where('post_id', $post->id)->first();
            $stat->views +=1;
            $stat->save();
        }
        //Atgriež rakstu datus
        $response = [
            'posts' => $posts
        ];

        return $this->success($response);
    }
    //Funkcija, kas ļauj pievienot "Patīk' rakstam
    public function addLike(AddLikeRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        //Pievieno patīk rakstam
        $like = Like::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
        //Palielina rakstam patīk skaitu statistikā
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->like +=1;
        $stat->save();
        return $this->success([
            'like' => $like
        ], 'Like added successfully', 201);
    }
    //Funkcija, kas ļauj dalīties ar rakstu
    public function addRepost(StoreRepostRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        //Izveido dalīto rakstu
        $repost = Repost::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
        //Paliena raksta dalīto rakstu skaitu statistikā
        $stat = Statistic::where('post_id', $repost->post_id)->first();
        $stat->repost += 1;
        $stat->save();
        return $this->success([
            'repost' => $repost
        ], 'Repost added successfully', 201);
    }
    //Funkcija, kas ļauj noņemt "Patīk" no raksta
    public function removeLike(RemoveLikeRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        //Izdzēšs patīk novērtējumu no raksta
        $deleted = Like::where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();
        //Samazina rakstam novērtējumu skaitu statistikā
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->like -=1;
        $stat->save();
        if($deleted){
            return $this->success([], 'Like removed successfully', 201);
        }else{
            return $this->error([], 'Like removed unsuccessfully', 201);
        }
    }
    //Funkcija, kas ļauj izdzēst dalīto rakstu
    public function deleteRepost(RemoveRepostRequest $request){
        $postId = $request['post_id'];
        $userId = $request['user_id'];
        //Izdzēšs dalīto rakstu
        $deleted = Repost::where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();
        //Samazina rakstam dalīto rakstu skaitu statistikā
        $stat =  Statistic::where('post_id', $postId)->first();
        $stat->repost -=1;
        $stat->save();
        if($deleted){
            return $this->success([], 'Repost removed successfully', 201);
        }else{
            return $this->error([], 'Repost removed unsuccessfully', 201);
        }
    }
    //Funkcija, kas izdzēšs rakstu
    public function deletePost(DeletePostRequest $request)
    {
        $postId = $request->input('post_id');
        $userId = $request->input('user_id');

        $post = Post::findOrFail($postId);
        //Pārbauda vai raksta autora id ir vienāds ar pieprasījumā norādīto
        if ($post->user_id === $userId) {
            //Pārbauda vai ir fails, ja ir tad to izdzēšs
            if ($post->fileName) {
                $mediaPath = 'public/media/' . $post->fileName;
                if (Storage::exists($mediaPath)) {
                    Storage::delete($mediaPath);
                    Log::info('File Deleted Successfully', ['path' => $mediaPath]);
                } else {
                    Log::error('File Not Found', ['path' => $mediaPath]);
                }
            }
            //Idzēšs rakstus un atgriež paziņojumu
            $post->delete();
            return $this->success([], 'Post and associated media removed successfully', 201);
        } else {
            return $this->error([], 'Unauthorized to delete the post', 403);
        }
    }
    //Funkcija, kas ļauj redzēt viena lietotāja rakstus
    public function getUsersPosts(ShowUsersPosts $request){
        $userId = $request->input('user_id');
        //Atrod visus rakstus, kuriem autors ir ar $userId
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->where('post.user_id', $userId)
            ->orderByDesc('post.created_at')
            ->get();
        //Katram rakstam pievieno autoru, autora attēlu un komentārus
        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
           
            $post->profile = $profile;
            //Palielina rakstam skatījumu skaitu statistikā
            $stat =  Statistic::where('post_id', $post->id)->first();
            $stat->views +=1;
            $stat->save();
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
        }
        $response = [
            'posts' => $posts,
        ];
        return $this->success($response);
    }
    //Funkcija, kas atgriež visus lietotāja dalītos rakstus
    public function getUsersReposts(ShowUsersReposts $request){
        $userId = $request->input('user_id');
        //Iegūst visus lietotāja dalītos rakstus un sakārto tos pēc jaunākā
        $reposts = Repost::select('repost.*')
            ->leftJoin('post', 'repost.post_id', '=', 'post.id')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'post.statistic', 'post.comment', 'post.like', 'user.profile'])
            ->where('repost.user_id', $userId)
            ->orderByDesc('repost.created_at')
            ->get();
    
        //Pievieno rakstam autoru, autora profilu ar attēlu un komentārus
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
            //Palielina raksta skatījumu skaitu statistikā
            $post->profile = $profile;
            $stat =  Statistic::where('post_id', $post->id)->first();
            $stat->views +=1;
            $stat->save();
        }
    
        $response = [
            'reposts' => $reposts,
        ];
    
        return $this->success($response);
    }
    //Funkcija, kas atgriež rakstus no lietotājiem, kuriem seko
    public function getFollowedPosts(ShowFollowedPosts $request)
    {
        $userId = $request->input('userId');

        $followedUsers = Follow::where('follower_id', $userId)->pluck('followed_id')->toArray();

        $dayAgo = Carbon::now()->subDay();
        //Atgriež visus rakstus, kas ir jaunāki par dienu un sakārto tos pēc vecumu
        // un pēc tā
        //vai lietotājas ir verificēts
        $posts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->whereIn('post.user_id', $followedUsers)
            ->whereNotIn('post.user_id', [$userId]) 
            ->where('post.created_at','>',$dayAgo)
            ->orderByDesc('profile.verified') 
            ->orderByDesc('post.created_at') 
            ->get();
        //Atgriež visus rakstus, kas ir vecāki par dienu un sakārto tos pēc vecuma
        $olderPosts = Post::select('post.*')
            ->leftJoin('profile', 'post.user_id', '=', 'profile.user_id')
            ->with(['user', 'statistic', 'comment', 'like'])
            ->whereIn('post.user_id', $followedUsers)
            ->whereNotIn('post.user_id', [$userId]) 
            ->where('post.created_at', '<=', $dayAgo) 
            ->orderByDesc('post.created_at') 
            ->get();
        //Abus raksta masīvus saliek vienā
        $posts = $posts->merge($olderPosts); 
        
        foreach ($posts as $post) {
            $user = $post->user; 
            $profile = $user->profile; 
            //Atrod raksta autora profila attēlu
            if(!filter_var($profile->picture, FILTER_VALIDATE_URL)){
                $profile->picture = $this->filePath($profile->picture);
            }
            //Katram raksta komentāram pievieno tā autoru, autora profilu un attēlu
            foreach ($post->comment as $comment) {
                $userC = $comment->user;
                $profileC = $userC->profile;
                if(!filter_var($profileC->picture, FILTER_VALIDATE_URL)){
                    $profileC->picture = $this->filePath($profileC->picture);
                }
                $comment->user = $userC;
                $comment->profile = $profileC;
            }
    
            //Palielina rakstam apskatu skaitu statistikā
            $post->profile = $profile;
            $stat =  Statistic::where('post_id', $post->id)->first();
            $stat->views +=1;
            $stat->save();
        }
        $response = [
            'posts' => $posts,
        ];

        return $this->success($response);
    }
}
