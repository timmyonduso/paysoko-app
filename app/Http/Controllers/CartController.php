<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;
use App\Models\Order;

class CartController extends Controller
{
    protected $cartPrefix = 'cart:user:';

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch cart items
        $cartKey = $this->cartPrefix . $userId;
        $cartItems = Redis::hgetall($cartKey);
        $cartItems = array_map('json_decode', array_values($cartItems));

        // Fetch user orders
        $orders = Order::where('user_id', $userId)->with('orderItems.product')->get();

        // Fetch all products
        $products = Product::all();

        // Log the data to inspect
        // \Log::info(compact('cartItems', 'orders', 'products'));

        // Pass data to Inertia
        return inertia('Dashboard', [
            'cart' => $cartItems,
            'orders' => $orders,
            'products' => $products,
        ]);
    }


    public function addToCart(Request $request)
    {
        $userId = $request->user()->id;
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

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
    }

    public function removeFromCart(Request $request)
    {
        $userId = $request->user()->id;
        $productId = $request->input('product_id');

        $cartKey = $this->cartPrefix . $userId;
        Redis::hdel($cartKey, $productId);

        return response()->json(['message' => 'Product removed from cart', 'cart' => $this->getCart($userId)], 200);
    }

    public function viewCart(Request $request)
    {
        $userId = $request->user()->id;
        return response()->json(['cart' => $this->getCart($userId)], 200);
    }

    private function getCart($userId)
    {
        $cartKey = $this->cartPrefix . $userId;
        $cartItems = Redis::hgetall($cartKey);
        return array_map('json_decode', array_values($cartItems));
    }
}
