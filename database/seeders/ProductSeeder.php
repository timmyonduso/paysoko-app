<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run():void
    {
        Product::create(['name' => 'Samsung Galaxy S22', 'description' => 'High end phone', 'price' => 1499]);
        Product::create(['name' => 'HP Probook 250 G8', 'description' => 'Decent laptop', 'price' => 699]);
        Product::create(['name' => 'Hisense 55" 4K LED', 'description' => 'High resolution TV', 'price' => 499]);
        Product::create(['name' => 'Sony SRB-X33 Ultra', 'description' => 'Good quality speake', 'price' => 599]);       
    }
}
