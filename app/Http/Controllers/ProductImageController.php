<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductImageResource;
use App\Http\Resources\ProductResource;

class ProductImageController extends ApiController
{
    public function store($request)
    {


        if ($request->has('primary_image')) {

            $primaryImage = generateFileName($request->primary_image->getClientOriginalName());

            $request->primary_image->move(public_path(env('PRODUCT_IMAGE_UPLOAD_PATH')), $primaryImage);
        }
        if ($request->has('image')) {

            $productImages = [];
            foreach ($request->image  as $image) {

                $ImageFileName = generateFileName($image->getClientOriginalName());

                $image->move(public_path(env('PRODUCT_IMAGE_UPLOAD_PATH')), $ImageFileName);

                array_push($productImages, $ImageFileName);
            }
        }

        if (!$request->has('image')) {
            $productImages = null;
        }

        return ['primaryImage' => $primaryImage, 'ImageFileName' => $productImages];
    }


    public function update($request, $product)
    {
        if ($request->has('primary_image')) {

            $primaryImage = generateFileName($request->primary_image->getClientOriginalName());

            $request->primary_image->move(public_path(env('PRODUCT_IMAGE_UPLOAD_PATH')), $primaryImage);

            $product->update([
                'primary_image' => $primaryImage,
            ]);
        }

        if ($request->has('image')) {

            foreach ($request->image  as $image) {

                $ImageFileName = generateFileName($image->getClientOriginalName());

                $image->move(public_path(env('PRODUCT_IMAGE_UPLOAD_PATH')), $ImageFileName);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $ImageFileName
                ]);
            }
        }
    }

    public function showAllImage(Product $product){
        return $this->successResponse(ProductImageResource::collection($product->images) , 200);
    }

    public function showImage(ProductImage $productImage){
        return $this->successResponse(new ProductImageResource($productImage )  , 200);
    }
    public function deleteImage(ProductImage $productImage){
        $productImage->delete();
        return $this->successResponse(new ProductImageResource($productImage ) , 200);
    }

    public function setPrimary(Product $product , ProductImage $productImage){

       $primary_image = ProductImage::find($productImage);

        $product->update([
            'primary_image' => $primary_image->first()->image,
        ]);

        return $this->successResponse(new ProductResource($product) , 200);
    }
}
