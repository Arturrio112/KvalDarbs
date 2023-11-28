<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteConvoRequest;
use App\Http\Requests\GetUserConvoRequest;
use App\Http\Requests\PostNewMessageRequest;
use App\Http\Requests\StartNewConvoRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\Convo;
use App\Models\User;
use App\Models\Message;
use App\Models\UserConversation;

class ConvoController extends Controller
{
    use HttpResponses;
    public function getUserConvos(GetUserConvoRequest $request){
        $userId = $request->input('user_id');

        // Retrieve conversations for the user along with associated messages and other users
        $conversations = Convo::select('convo.id as conversation_id')
            ->join('user_conversation', 'convo.id', '=', 'user_conversation.conversation_id')
            ->join('user', function ($join) use ($userId) {
                $join->on('user_conversation.user_id', '!=', 'user.id')
                    ->where('user.id', '!=', $userId);
            })
            ->where('user_conversation.user_id', $userId)
            ->groupBy('convo.id')
            ->get();

        // Retrieve messages and other users for each conversation
        foreach ($conversations as $conversation) {
            $messages = Message::select('id', 'user_id', 'text', 'created_at')
                ->where('conversation_id', $conversation->conversation_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform timestamps to a readable format if needed
            foreach ($messages as $message) {
                $message->created_at = $message->created_at->format('Y-m-d H:i:s');
            }

            // Add messages to the conversation data
            $conversation->messages = $messages;

            // Retrieve all users associated with the conversation
            $usersInConversation = UserConversation::select('user_id')
                ->where('conversation_id', $conversation->conversation_id)
                ->where('user_id', '!=', $userId)
                ->get();
            $otherUser= null;
            // Add information about other users in the conversation
            foreach ($usersInConversation as $userInConversation) {
                $otherUser = User::with('profile')->find($userInConversation->user_id);
                $otherUser->profile->picture = asset('storage/media/' . $otherUser->profile->picture);
            }

            // Add other users to the conversation data
            $conversation->other_user = $otherUser;
        }

        return $this->success(['conversations' => $conversations], 'User conversations retrieved successfully', 200);
    }

    public function startNewConvo(StartNewConvoRequest $request){
        $user1 = $request->input('user1_id');
        $user2 = $request->input('user2_id');
        $msg = $request->input('message');
        $convo = Convo::create();
        $newConvUser1 = UserConversation::create([
            'conversation_id'=> $convo->id,
            'user_id'=>$user1
        ]);
        $newConvUser2 = UserConversation::create([
            'conversation_id'=> $convo->id,
            'user_id'=>$user2
        ]);
        $msg = Message::create([
            'user_id'=>$user1,
            'conversation_id'=>$convo->id,
            'text'=>$msg
        ]);
        return $this->success(['convo'=>$convo], 'New conversation added', 200);
    }

    public function postNewMessage(PostNewMessageRequest $request){
        $user = $request->input('user_id');
        $convo = $request->input('conversation_id');
        $text = $request->input('text');
        $msg = Message::create([
            'user_id'=>$user,
            'conversation_id'=>$convo,
            'text'=>$text
        ]);
        return $this->success(['msg'=>$msg], 'New message added', 200);

    }
    public function deleteConvo(DeleteConvoRequest $request){
        $convoId = $request->input('convo_id');
        $userId = $request->input('user_id');
        $conversationExists = UserConversation::where('conversation_id', $convoId)
            ->where('user_id', $userId)
            ->exists();

        if ($conversationExists) {
            Convo::where('id', $convoId)->delete();
            return $this->success([], 'Conversation deleted successfully', 200);
        }else{
            return $this->error([], 'Unauthorized to delete the conversation', 403);
        }
        

    }
}
