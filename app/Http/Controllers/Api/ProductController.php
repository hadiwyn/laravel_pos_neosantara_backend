<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Exists;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //all products
        $products = Product::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'List Data Product',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'category' => 'required|in:food,drink,snack',
            'image' => 'required|image|mimes:png,jpg,jpeg'
        ]);

        $filename = time() . '.' . $request->image->extension();
        $request->image->storeAs('public/products', $filename);
        $product = Product::create([
            'name' => $request->name,
            'price' => (int) $request->price,
            'stock' => (int) $request->stock,
            'category' => $request->category,
            'image' => $filename,
            'is_best_seller' => $request->is_best_seller
        ]);

        if ($product) {
            return response()->json([
                'success' => true,
                'message' => 'Product Created',
                'data' => $product
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Failed to Save',
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // $product->load('category');
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $imagePath = Product::find($id)->image;
        $product = Product::findOrFail($id);


        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists('products/' . $imagePath)) {
                Storage::disk('public')->delete('products/' . $imagePath);
            }
            $imagePath = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/products', $imagePath);

            $product->update([
                'image' => $imagePath
            ]);
        } else if ($request->image != null) {
            $product->update([
                'image' => $request->image,
            ]);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => (int) $request->price,
            'stock' => (int) $request->stock,
            'hpp' => (int) $request->hpp,
            'category' => $request->category,
            'is_best_seller' => $request->is_best_seller
        ]);

        $updateProduct = Product::find($id);




        if ($updateProduct) {
            return response()->json([
                'success' => true,
                'message' => 'Product Updated',
                'data' => $updateProduct
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Failed to Update',
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
