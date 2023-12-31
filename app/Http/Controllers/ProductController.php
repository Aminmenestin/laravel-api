<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::paginate(10);

        return $this->successResponse([
            'product' => ProductResource::collection($products->load('images')),
            'links' => ProductResource::collection($products)->response()->getData()->links,
            'meta' => ProductResource::collection($products)->response()->getData()->meta,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'brand_id' => 'required | integer',
            'category_id' => 'required | integer',
            'primary_image' => 'required | image',
            'description' => 'nullable',
            'image.*' => 'nullable | image',
            'price' => 'required | integer',
            'quantity' => 'required | integer',
            'delivery_amount' => 'nullable | integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 400);
        }


        DB::beginTransaction();

        $ProductImageController = new ProductImageController;

        $ImageFileName = $ProductImageController->store($request);

        $product = Product::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $ImageFileName['primaryImage'],
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount
        ]);

        if ($ImageFileName['ImageFileName']) {
            foreach ($ImageFileName['ImageFileName'] as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image
                ]);
            }
        }

        DB::commit();

        return $this->successResponse(new ProductResource($product), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->successResponse(new ProductResource($product->load('images')), 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'brand_id' => 'nullable | integer',
            'category_id' => 'nullable | integer',
            'primary_image' => 'nullable | image',
            'description' => 'nullable',
            'image.*' => 'nullable | image',
            'price' => 'nullable | integer',
            'quantity' => 'nullable | integer',
            'delivery_amount' => 'nullable | integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 400);
        }

        DB::beginTransaction();

        if ($request->has('primary_image') || $request->has('image')) {
            $ProductImageController = new ProductImageController;
            $ProductImageController->update($request, $product);
        }

        $product->update([
            'name' => $request->has('name') ? $request->name : $product->name,
            'brand_id' => $request->has('brand_id') ? $request->brand_id : $product->brand_id,
            'category_id' => $request->has('category_id') ? $request->category_id : $product->category_id,
            'description' => $request->has('description') ? $request->description : $product->description,
            'price' => $request->has('price') ? $request->price : $product->price,
            'quantity' => $request->has('quantity') ? $request->quantity : $product->quantity,
            'delivery_amount' => $request->has('delivery_amount') ? $request->delivery_amount : $product->delivery_amount
        ]);

        DB::commit();

        return $this->successResponse(new ProductResource($product), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return $this->successResponse(new ProductResource($product), 200);
    }
}
