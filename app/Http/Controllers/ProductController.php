<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get product by page size and keywords
     *
     * @param Request $request
     * @return collection
     */
    public function products(Request $request)
    {
        // for refactor
        // $keyword = '%' . $request->query('keyword', '') . '%';
        // $data = Product::where('name', 'like', $keyword)->paginate();
        $pageSize = 10;

        $page = (int)$request->query('pageNumber', 1);
        $keyword = '%' . $request->query('keyword', '') . '%';
        $isVisible = [true];
        if (Auth::check() && Auth::user()->is_admin) {
            $isVisible = [true, false];
        }
        $count = Product::where('name', 'like', $keyword)
            ->whereIn('is_visible', $isVisible)
            ->count();

        $products = Product::where('name', 'like', $keyword)
            ->whereIn('is_visible', $isVisible)
            ->offset($pageSize * ($page - 1))
            ->limit($pageSize)
            ->get();

        return response()->json([
            'products' => $products,
            'page' => $page,
            'pages' => round($count / $pageSize)
        ], 200);

        return response()->json([
            'products' => $products,
            'page' => $page,
            'pages' => round($count / $pageSize)
        ], 200);
    }
    public function productBySlug($slug)
    {
        $product  = Product::with('reviews')
            ->where('slug', '=', $slug)->firstOrFail();

        return response()->json($product, 200);
    }
    
    public function productsByPrice($price,$gt='>=')
    {
        $products  = Product::with('reviews')
            ->where('price',$gt , $price)->get();

        return response()->json(["products"=>$products], 200);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->noContent();
        }
        return response()->json(['message' => 'Unauthorize'], 401);
    }

    /**
     * Create new Product
     *
     * @return json
     */
    public function store()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            $productName = 'Sample name';
            $slug = Str::slug($productName, '-');
            $next = 2;

            // Loop until we can query for the slug and it returns false
            while (Product::where('slug', '=', $slug)->first()) {
                $slug = $slug . '-' . $next;
                $next++;
            }
              $product = Product::create([
                'slug' => $slug,
                'user_id' => Auth::user()->id,
                'category_id' => 1,
                'sub_category_id' => 1,
                'name' => 'Sample name',
                'description' => 'Sample description',
                'price' => 0,
                'image' => '/images/sample.jpg',
                'brand' => 'Apple',
                'count_stock' => 0,
                'rating' => 0,
                'num_reviews' => 0,
            ]);
          /* $prodreq=json_decode($request->input("product"),true);
           $product = Product::create([
                'slug' => $slug,
                'user_id' => Auth::user()->id,
                'category_id' => $productreq["catId"],
                'sub_category_id' =>$productreq["subcatId"] ,
                'name' => $productreq["Name"],
                'description' => $productreq["description"],
                'price' => intval( $productreq["price"]),
                'image' => '/images/sample.jpg',
                'brand' => 'Apple',
                'count_stock' => 0,
                'rating' => 0,
                'num_reviews' => 0,
            ]);*/
            return response()->json($product, 201);
        }
        return response()->json(['message' => 'Unauthorize'], 401);
    }

    /**
     * Update the product resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'image' => 'required',
            'brand' => 'required',
            'is_visible' => 'required',
            'sub_category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $product = Product::findOrFail($id);

        if ($product && Auth::user()->is_admin) {

            $product->update(array_merge(["count_stock" => $request->count_stock], $validator->validated()));
            return response()->json($product, 200);
        }
        return response()->json(['message' => 'Something went wrong'], 400);
    }
    /**
     * Upload Image to storage
     *
     * @param Request $request
     * @return Json
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        $uploadFolder = 'products';
        $image = $request->file('image');
        $path = $image->store($uploadFolder, 'public');
        //    Storage::disk('public')->url($image_uploaded_path),
        return response()->json('/' . $path, 200);
    }
     public function addAttachment(Request $request,$id)
    {
        $request->validate([
            'attachment' => 'required|mimes:csv,txt,xlx,xls,pdf,mp3,mp4|max:20480'
        ]);
        $uploadFolder = 'products';
        $attachment = $request->file('attachment');
        $path = $attachment->store($uploadFolder, 'public');
        //    Storage::disk('public')->url($image_uploaded_path),
        Product::findOrFail($id)->attachment()->create([
        "product_type"=>$id,
        "content_type"=>$attachment->extension(),
        "path"=>$path
        ]);
       return response()->json('/' . $path, 200);
    }

    /**
     * Add product Review
     *
     * @param Request $request
     * @param inst $id
     * @return JSON
     */
    public function addProductReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $product = Product::findOrFail($id);

        $user = auth()->user();
        $user_id = $user->id;

        $checkProductReview = ProductReview::where('user_id', $user_id)
            ->where('product_id', $product->id)->get();

        if (!$checkProductReview->isEmpty()) {
            return response()->json(['message' => 'Product already reviewed'], 400);
        }
        ProductReview::create(array_merge(
            $validator->validate(),
            [
                'user_id' => $user_id,
                'product_id' => $product->id,
                'user_name' => $user->name,
            ]
        ));
        $productReview = ProductReview::where('product_id', $product->id)->get();
        $productLength = $productReview->count();
        $totalRating = $productReview->sum('rating');

        $product->num_reviews = $productLength;
        $product->rating = $totalRating / $productLength;

        $product->save();

        return response()->json(['message' => 'Review added'], 201);
    }

    /**
     * Get top rated product
     *
     * @return JSON
     */
    public function topProducts()
    {
        $product = Product::orderBy('rating', 'DESC')->take(3)->get();
        return response()->json($product, 200);
    }
}
