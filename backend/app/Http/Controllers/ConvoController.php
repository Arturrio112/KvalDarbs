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
use Exception;

class ConvoController extends Controller
{
    use HttpResponses;
    //Funkcija, kas atgriež visas viena lietotāja privātās sarunas
    public function getUserConvos(GetUserConvoRequest $request){
        $userId = $request->input('user_id');
        //Atgriež sarunas ar otru lietotāju, kas tajās piedalās
        $conversations = Convo::select('convo.id as conversation_id')
            ->join('user_conversation', 'convo.id', '=', 'user_conversation.conversation_id')
            ->join('user', function ($join) use ($userId) {
                $join->on('user_conversation.user_id', '!=', 'user.id')
                    ->where('user.id', '!=', $userId);
            })
            ->where('user_conversation.user_id', $userId)
            ->groupBy('convo.id')
            ->get();
        //Katrai sarunai pievieno visas vēstules
        foreach ($conversations as $conversation) {
            $messages = Message::select('id', 'user_id', 'text', 'created_at')
                ->where('conversation_id', $conversation->conversation_id)
                ->orderBy('created_at', 'desc')
                ->get();
            $latestMessage = $messages[0];
            //Pārveido izveides datumu no laika momenta uz datumu
            foreach ($messages as $message) {
                $message->created_at = $message->created_at->format('Y-m-d H:i:s');
            }

            // Pievieno vēstules pie sarunas
            $conversation->messages = $messages;
            $conversation->latestMessage = $latestMessage;
            // Atrod otru lietotāju, kas piedalās sarunā un pievieno tā datus
            $usersInConversation = UserConversation::select('user_id')
                ->where('conversation_id', $conversation->conversation_id)
                ->where('user_id', '!=', $userId)
                ->get();
            $otherUser= null;
            foreach ($usersInConversation as $userInConversation) {
                $otherUser = User::with('profile')->find($userInConversation->user_id);
                $otherUser->profile->picture = asset('storage/media/' . $otherUser->profile->picture);
            }
            $conversation->other_user = $otherUser;
        }
        //Sakārto sarunas pēc jaunākās vēstules
        $conversations = $conversations->sortByDesc(function ($conversation) {
            return optional($conversation->latestMessage)->created_at;
        })->values();
        //Atgriež privāto sarunu datus
        return $this->success(['conversations' => $conversations], 'User conversations retrieved successfully', 200);
    }
    //Funkcija, kas ļauj saglabāt jaunu privāto sarunu
    public function startNewConvo(StartNewConvoRequest $request){
        try{
            $user1 = $request->input('user1_id');
            $user2 = $request->input('user2_id');
            $msg = $request->input('message');
            //Izveido privāto sarunu un pievieno tai lietotājus
            $convo = Convo::create();
            $newConvUser1 = UserConversation::create([
                'conversation_id'=> $convo->id,
                'user_id'=>$user1
            ]);
            $newConvUser2 = UserConversation::create([
                'conversation_id'=> $convo->id,
                'user_id'=>$user2
            ]);
            //Pievieno vēstuli pie jaunās privātās sarunas
            $msg = Message::create([
                'user_id'=>$user1,
                'conversation_id'=>$convo->id,
                'text'=>$msg
            ]);
            //Atgriež privātās sarunas datus
            return $this->success(['convo'=>$convo], 'New conversation added', 200);
        }catch(Exception $error){
            //Atgriež kļūdas paziņojumu
            return $this->error($error->getMessage(), 'Error adding conversation', 500);
        }
    }
    //Funkcija, kas ļauj pievienot jaunu vēstuli pie privātās sarunas
    public function postNewMessage(PostNewMessageRequest $request){
       
        $user = $request->input('user_id');
        $convo = $request->input('conversation_id');
        $text = $request->input('text');
        //Saglabā vēstuli
        $msg = Message::create([
            'user_id'=>$user,
            'conversation_id'=>$convo,
            'text'=>$text
        ]);
        //Atgriež vēstules datus
        return $this->success(['msg'=>$msg], 'New message added', 200);

    }
    //Funkcija, kas ļauj izdzēst privāto sarunu
    public function deleteConvo(DeleteConvoRequest $request){
        
        $convoId = $request->input('convo_id');
        $userId = $request->input('user_id');
        $conversationExists = UserConversation::where('conversation_id', $convoId)
            ->where('user_id', $userId)
            ->exists();
        //Pārbauda vai eksistē šī privātā saruna
        if ($conversationExists) {
            //Izdzēš privāto sarunu un atgriež paziņojumu
            Convo::where('id', $convoId)->delete();
            return $this->success([], 'Conversation deleted successfully', 200);
        }else{
            //Atgriež kļūdu
            return $this->error([], 'Unauthorized to delete the conversation', 403);
        }
        

    }
}
