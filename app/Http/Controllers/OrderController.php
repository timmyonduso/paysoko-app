<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Status;
use App\Http\Requests\PlaceOrderRequest;

class OrderController extends Controller
{
    protected $cartPrefix = 'cart:user:';

    /**
     * Place an order based on the current cart items in Redis.
     */

    public function placeOrder(PlaceOrderRequest $request)
    {
        $userId = $request->user()->id;

        $cartKey = "{$this->cartPrefix}{$userId}";
        $cartItems = Redis::hgetall($cartKey);

        if (empty($cartItems)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        try {
            $pendingStatus = Status::where('name', 'pending')->firstOrFail();

            $total = 0;
            $orderItems = [];

            foreach ($cartItems as $productId => $item) {
                $cartItem = json_decode($item, true);
                $product = Product::find($cartItem['product_id']);

                if (!$product) {
                    return response()->json(['error' => "Product with ID {$cartItem['product_id']} not found"], 404);
                }

                $total += $product->price * $cartItem['quantity'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $cartItem['quantity'],
                    'price' => $product->price,
                ];
            }

            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $userId,
                'status_id' => $pendingStatus->id,
                'total' => $total,
            ]);

            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            Redis::del($cartKey);

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order_id' => $order->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error placing order', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to place order'], 500);
        }
    }


    /**
     * Display a list of orders for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $orders = Order::with('orderItems.product')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['orders' => $orders], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching orders', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch orders'], 500);
        }
    }


    /**
     * Display details of a specific order.
     */
    public function show(Request $request, Order $order)
    {
        try {
            if ($order->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            $order->load('orderItems.product', 'status');

            return response()->json(['order' => $order], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching order details', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch order details'], 500);
        }
    }

}
