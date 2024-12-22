<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;
use App\Models\Order;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\RemoveFromCartRequest;

class CartController extends Controller
{
    protected $cartPrefix = 'cart:user:';

    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cartKey = $this->cartPrefix . $userId;
    
            $cartItems = Redis::hgetall($cartKey);
            $cartItems = array_map('json_decode', array_values($cartItems));
    
            $orders = Order::where('user_id', $userId)->with('orderItems.product')->get();
            $products = Product::all();
    
            return inertia('Dashboard', [
                'cart' => $cartItems,
                'orders' => $orders,
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch cart data'], 500);
        }
    }


    public function addToCart(AddToCartRequest $request)
    {
        try {
            $userId = $request->user()->id;
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            $product = Product::find($productId);

            $cartKey = $this->cartPrefix . $userId;
            $cartItem = Redis::hget($cartKey, $productId);

            if ($cartItem) {
                $cartItem = json_decode($cartItem, true);
                $cartItem['quantity'] += $quantity;
            } else {
                $cartItem = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                ];
            }

            Redis::hset($cartKey, $productId, json_encode($cartItem));

            return response()->json(['message' => 'Product added to cart', 'cart' => $this->getCart($userId)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add product to cart'], 500);
        }
    }

    public function removeFromCart(RemoveFromCartRequest $request)
    {
        try {
            $userId = $request->user()->id;
            $productId = $request->input('product_id');

            $cartKey = $this->cartPrefix . $userId;
            Redis::hdel($cartKey, $productId);

            return response()->json(['message' => 'Product removed from cart', 'cart' => $this->getCart($userId)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove product from cart'], 500);
        }
    }

    public function viewCart(Request $request)
    {
        try {
            $userId = $request->user()->id;
            return response()->json(['cart' => $this->getCart($userId)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve cart'], 500);
        }
    }

    private function getCart($userId)
    {
        $cartKey = $this->cartPrefix . $userId;
        $cartItems = Redis::hgetall($cartKey);
        return array_map('json_decode', array_values($cartItems));
    }
}
