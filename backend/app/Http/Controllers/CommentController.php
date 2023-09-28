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

    public function store(StoreCommentRequest $request){
        $request->validated($request->all());
        $fileFormat = '';
        $fileName = ''; // Initialize fileName
        $fileSize = 0;  // Initialize fileSize
        
        if ($request->hasFile('media')) {
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

        $comment = Comment::create([
            'user_id' => $request['user_id'],
            'text' => $request['text'],
            'fileFormat' => $fileFormat,
            'fileName' => $fileName, // Set fileName
            'fileSize' => $fileSize, // Set fileSize
            'post_id' => $request['post_id']
        ]);
        $stats = Statistic::where('post_id', $comment->post_id)->first();
        $stats->comment +=1;
        $stats->save();
        return $this->success([
            'comment' => $comment
        ], 'Comment created successfully', 201);

    }
    public function delete(DeleteCommentRequest $request){
        $request->validated($request->all());
        $commentId = $request->input('comment_id');
        $userId = $request->input('user_id');
        $comment = Comment::findOrFail($commentId);
        if($comment->user_id == $userId){
            $stat = Statistic::where('post_id', $comment->post_id)->first();
            $stat->comment -=1;
            $stat->save();
            $comment->delete();
            return $this->success([], 'Comment removed successfully', 201);
        }else{
            return $this->error([], 'Unauthorized to delete the comment', 403);
        }
    }
}
