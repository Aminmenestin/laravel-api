<?php
namespace App\Traits;

trait ApiResponse
{

    protected function successResponse($data , $code , $message = null){
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'status_code' => $code,
            'data' => $data
        ],$code);
    }

    protected function errorResponse($message = null ,$code){
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'status_code' => $code,
        ],$code);
    }
}

