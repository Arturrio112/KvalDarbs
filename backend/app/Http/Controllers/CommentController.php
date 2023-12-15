<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteCommentRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Statistic;

class CommentController extends Controller
{
    use HttpResponses;
    //Funkcija, kas ļauj saglabāt komentāru
    public function store(StoreCommentRequest $request){
        $request->validated($request->all());
        $fileFormat = '';
        $fileName = null; 
        $fileSize = 0;  
        //Pārbauda vai ir pievienots attēls 
        if ($request->hasFile('media')) {
            $media = $request->file('media');

            // Pārbauda vai pievienotais fails ir augšupielādēts bez kļūdām
            if ($media->isValid()) {
                // Iegūst faila tipu
                $fileFormat = $media->getClientOriginalExtension();

                // Izveido file vārdu
                $fileName = uniqid() . '_'.'comment'. '_' . time() . '.' . $fileFormat;

                // Saglabā failu failu sistēmā
                $media->storeAs('public/media', $fileName);

                // Iegūst faila lielumu
                $fileSize = $media->getSize();
            } else {
                // Atgriež kļūdu, ja fails ir augšupielādēts ar kļūdu
                return $this->error([], 'Invalid media file', 400);
            }
        }
        //Saglabā komentāru datu bāzē
        $comment = Comment::create([
            'user_id' => $request['user_id'],
            'text' => $request['text'],
            'fileFormat' => $fileFormat,
            'fileName' => $fileName, 
            'fileSize' => $fileSize, 
            'post_id' => $request['post_id']
        ]);
        //Palielina raksta komentāru skaita statistiku
        $stats = Statistic::where('post_id', $comment->post_id)->first();
        $stats->comment +=1;
        $stats->save();
        //Atgriež komentāra datus
        return $this->success([
            'comment' => $comment
        ], 'Comment created successfully', 201);

    }
    //Funkcija, kas ļauj izdzēst komentāru
    public function delete(DeleteCommentRequest $request){
        $request->validated($request->all());
        $commentId = $request->input('comment_id');
        $userId = $request->input('user_id');
        //Atrod komentāru
        $comment = Comment::findOrFail($commentId);
        //Pārbauda vai komentāra autora id sakrīt ar pievienotā lietotāja id 
        if($comment->user_id == $userId){
            //Samazina raksta komnentāru skaita statistiku
            $stat = Statistic::where('post_id', $comment->post_id)->first();
            $stat->comment -=1;
            $stat->save();
            $comment->delete();
            //Atgriez, ka komentārs ir izdzēsts veiksmīgi
            return $this->success([], 'Comment removed successfully', 201);
        }else{
            //Ja nesakrīt abi id, tiek paziņots par kļūdu 
            return $this->error([], 'Unauthorized to delete the comment', 403);
        }
    }
}
