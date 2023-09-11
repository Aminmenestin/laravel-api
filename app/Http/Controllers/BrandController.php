<?php

namespace App\Http\Controllers;

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
        $brands = Brand::paginate(10);

        return $this->successResponse([
            'brands' => brandResource::collection($brands) ,
            'links' => brandResource::collection($brands)->response()->getData()->links,
            'meta' => brandResource::collection($brands)->response()->getData()->meta,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required',
            'display_name' => 'required',
        ]);

        if($validator->fails()){
            return $this->errorResponse($validator->messages() , 400);
        }

        $brand = Brand::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return $this->successResponse(new brandResource($brand) , 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return $this->successResponse(new brandResource(Brand::find($brand)) , 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required',
            'display_name' => 'required',
        ]);

        if($validator->fails()){
            return  $this->errorResponse($validator->messages() , 400);
        }

        $brand->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return $this->successResponse(new brandResource($brand) , 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return $this->successResponse(new brandResource($brand) , 201);
    }

    public function products(Brand $brand){
        return $this->successResponse(new brandResource($brand->load('products')) , 201);
    }
}
