<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Support\Facades\Redis;
use function Pest\Laravel\actingAs;

it('places an order successfully', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);
    $pendingStatus = Status::factory()->create(['name' => 'pending']);

    $cartKey = "cart:user:{$user->id}";
    Redis::hset($cartKey, $product->id, json_encode([
        'product_id' => $product->id,
        'quantity' => 2,
    ]));

    actingAs($user)
        ->postJson('/api/orders')
        ->assertStatus(200)
        ->assertJson(['message' => 'Order placed successfully']);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status_id' => $pendingStatus->id,
        'total' => 200, // Price * Quantity
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    $this->assertEmpty(Redis::hgetall($cartKey));
});

it('fails to place an order with an empty cart', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/orders')
        ->assertStatus(400)
        ->assertJson(['error' => 'Cart is empty']);
});
