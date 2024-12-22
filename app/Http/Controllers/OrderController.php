<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    protected $cartPrefix = 'cart:user:';

    /**
     * Place an order based on the current cart items in Redis.
     */
    public function placeOrder(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve the cart from Redis
        $cartKey = 'cart:' . $user->id;
        $cartItems = Redis::hgetall($cartKey);

        if (empty($cartItems)) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Create a new order
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Create order items and attach to the order
        foreach ($cartItems as $productId => $quantity) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        // Clear the cart in Redis
        Redis::del($cartKey);

        return response()->json([
            'message' => 'Order placed successfully',
            'order_id' => $order->id,
        ], 201);
    }

    public function viewOrders(Request $request)
    {
        $userId = $request->user()->id;

        $orders = Order::where('user_id', $userId)->with('orderItems.product')->get();

        return response()->json(['orders' => $orders], 200);
    }
}
