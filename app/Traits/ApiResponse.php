<?php


namespace App\Traits;

trait ApiResponse{
    public function successResponse($data , $code , $message = null){
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ],$code);
    }
    public function errorResponse($code , $message = null){
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ],$code);
    }
}

