<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    protected $cartPrefix = 'cart:user:';

    // public function index(Request $request)
    // {
    //     $userId = $request->user()->id;

    //     // Fetch cart items
    //     $cartKey = $this->cartPrefix . $userId;
    //     $cartItems = Redis::hgetall($cartKey);
    //     $cartItems = array_map('json_decode', array_values($cartItems));

    //     // Fetch user orders
    //     $orders = Order::where('user_id', $userId)->with('orderItems.product')->get();

    //     // Pass data to Inertia
    //     return inertia('Dashboard', [
    //         'cart' => $cartItems,
    //         'orders' => $orders,
    //     ]);
    // }
}
