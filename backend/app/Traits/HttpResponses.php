<?php

namespace App\Traits;


trait HttpResponses{
    //Definēta atgriežamā struktūra, kad pieprasījums ir veiksmīgs
    protected function success($data, $msg = null, $code = 200){
        return response()->json([
            'status'=>"Request was succesful",
            'message'=>$msg,
            'data'=>$data
        ], $code);
    }
    //Definēta atgriežamās kļūdas struktūra
    protected function error($data, $msg = null, $code){
        return response()->json([
            'status'=>"Error has occurred...",
            'message'=>$msg,
            'data'=>$data
        ], $code);
    }


}