<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = Category::paginate(10);
        return $this->successResponse([
            'categories' => CategoryResource::collection($category),
            'links' => CategoryResource::collection($category)->response()->getData()->links,
            'meta' => CategoryResource::collection($category)->response()->getData()->meta,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'parent_id' => 'required | integer',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 400);
        }

        $category = Category::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->successResponse(new CategoryResource($category), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $this->successResponse(new CategoryResource($category), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 400);
        }

        $category->update([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->successResponse(new CategoryResource($category), 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->successResponse(new CategoryResource($category), 201);
    }


    public function children(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('children')), 200);
    }
    public function parent(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('parent')), 200);
    }
    public function products(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('products')), 200);
    }
}
