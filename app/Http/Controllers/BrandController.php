<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\brandResource;
use Illuminate\Support\Facades\Validator;

class BrandController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return 'ok';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return 'ok';
        $validator = Validator::make($request->all() , [
            'name' => 'required',
            'display_name' => 'required',
        ]);

        if($validator->fails()){
            $this->errorResponse(new brandResource($validator->messages()) , 400);
        }

        $user = User::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return $this->successResponse(new brandResource($user) , 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        //
    }
}
