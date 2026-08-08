<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tenda', 'slug' => 'tenda', 'color' => '#6366f1'],
            ['name' => 'Carrier & Ransel', 'slug' => 'carrier', 'color' => '#f59e0b'],
            ['name' => 'Sepatu & Sandal', 'slug' => 'sepatu', 'color' => '#10b981'],
            ['name' => 'Jaket & Raincoat', 'slug' => 'jaket', 'color' => '#ef4444'],
            ['name' => 'Sleeping Bag & Matras', 'slug' => 'sleeping-bag', 'color' => '#8b5cf6'],
            ['name' => 'Kompor & Peralatan Masak', 'slug' => 'kompor', 'color' => '#ec4899'],
            ['name' => 'Pencahayaan', 'slug' => 'pencahayaan', 'color' => '#f97316'],
            ['name' => 'Aksesoris', 'slug' => 'aksesoris', 'color' => '#14b8a6'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $products = [
            // [category slug, name, price, stock, emoji]
            ['tenda', 'Tenda Dome Kaldera 2P', 899000, 12, '⛺'],
            ['tenda', 'Tenda Dome Kaldera 4P', 1450000, 6, '⛺'],
            ['tenda', 'Tenda Tunnel Himalaya 6P', 3450000, 3, '⛺'],
            ['tenda', 'Tenda Camping Kecil 1P Ultralight', 560000, 15, '⛺'],
            ['tenda', 'Tenda Pantai Pop Up', 320000, 0, '⛺'],
            ['carrier', 'Carrier 60L Waterproof', 1250000, 8, '🎒'],
            ['carrier', 'Carrier 45L Daypack', 780000, 14, '🎒'],
            ['carrier', 'Ransel Gunung 35L', 540000, 22, '🎒'],
            ['carrier', 'Carrier 75L + Rain Cover', 1680000, 4, '🎒'],
            ['carrier', 'Sling Bag Trail 5L', 215000, 30, '🎒'],
            ['sepatu', 'Sepatu Hiking Mid-Cut', 980000, 9, '🥾'],
            ['sepatu', 'Sepatu Gunung High-Cut', 1350000, 5, '🥾'],
            ['sepatu', 'Sandals Gunung Quick Dry', 350000, 25, '🩴'],
            ['sepatu', 'Sepatu Trail Running', 890000, 11, '🥾'],
            ['sepatu', 'Sepatu Safety Trekking', 1150000, 2, '🥾'],
            ['jaket', 'Jaket Down 800FP', 2100000, 7, '🧥'],
            ['jaket', 'Jaket Windbreaker', 450000, 18, '🧥'],
            ['jaket', 'Raincoat Poncho', 180000, 40, '🧥'],
            ['jaket', 'Jaket Fleece Thermal', 560000, 13, '🧥'],
            ['jaket', 'Parka Hardshell 3L', 2900000, 4, '🧥'],
            ['sleeping-bag', 'Sleeping Bag -5°C', 780000, 10, '🛏️'],
            ['sleeping-bag', 'Sleeping Bag 0°C Rectangular', 650000, 16, '🛏️'],
            ['sleeping-bag', 'Matras Inflatable XL', 490000, 0, '🛏️'],
            ['sleeping-bag', 'Matras Busa Lipat', 120000, 35, '🛏️'],
            ['sleeping-bag', 'Bantal Tiup Neck Pillow', 150000, 28, '🛏️'],
            ['kompor', 'Kompor Portable 2400W', 280000, 20, '🍳'],
            ['kompor', 'Kompor Gas Kanister', 340000, 12, '🍳'],
            ['kompor', 'Cookset Aluminium 6 Pcs', 420000, 17, '🍳'],
            ['kompor', 'Mug Titanium 450ml', 310000, 9, '🍳'],
            ['kompor', 'Trekking Spoon + Fork', 85000, 50, '🍳'],
            ['pencahayaan', 'Headlamp 1200 Lumen', 260000, 24, '🔦'],
            ['pencahayaan', 'Lampu Camping Rechargeable', 190000, 19, '🔦'],
            ['pencahayaan', 'Lantern Gas GL-100', 320000, 6, '🔦'],
            ['pencahayaan', 'Korek Api Stormproof', 45000, 60, '🔦'],
            ['aksesoris', 'Trekking Pole Aluminium', 380000, 21, '🧗'],
            ['aksesoris', 'Water Bottle 1L Insulated', 230000, 33, '🍶'],
            ['aksesoris', 'Sarung HP Waterproof', 120000, 45, '📱'],
            ['aksesoris', 'Kantong Kering 10L', 95000, 38, '🛍️'],
            ['aksesoris', 'Tali Paracord 10m', 45000, 55, '🪢'],
            ['aksesoris', 'First Aid Kit Mini', 210000, 26, '⛑️'],
        ];

        $categoryBySlug = Category::all()->keyBy('slug');

        foreach ($products as $i => [$catSlug, $name, $price, $stock, $emoji]) {
            Product::create([
                'category_id' => $categoryBySlug[$catSlug]->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'sku' => 'KLR-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'price' => $price,
                'stock' => $stock,
                'sold' => 0,
                'image' => $emoji,
            ]);
        }

        $faker = \Faker\Factory::create('id_ID');
        $faker->seed(1234);

        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar', 'Yogyakarta', 'Semarang', 'Denpasar', 'Bogor', 'Tangerang', 'Depok', 'Bekasi', 'Malang', 'Solo', 'Palembang'];

        $customers = collect();
        for ($i = 0; $i < 60; $i++) {
            $customers->push(Customer::create([
                'name' => $faker->name(),
                'email' => strtolower($faker->unique()->safeEmail()),
                'phone' => '08' . $faker->numerify('##########'),
                'city' => $faker->randomElement($cities),
                'created_at' => $faker->dateTimeBetween('-13 months', 'now'),
            ]));
        }

        $products = Product::all();
        $statuses = ['delivered', 'delivered', 'delivered', 'shipped', 'shipped', 'paid', 'pending', 'cancelled'];
        $methods = ['transfer', 'transfer', 'ewallet', 'ewallet', 'qris', 'cod'];
        $monthCounts = [28, 30, 34, 36, 38, 42, 44, 45, 48, 50, 45, 40];
        $orderNumber = 1000;

        for ($m = 0; $m < 12; $m++) {
            $month = now()->subMonths(11 - $m);
            $count = $monthCounts[$m];

            for ($i = 0; $i < $count; $i++) {
                $customer = $customers->random();
                $orderDate = $faker->dateTimeBetween($month->copy()->startOfMonth(), $month->copy()->endOfMonth());

                $items = [];
                $itemCount = $faker->numberBetween(1, 3);
                $selected = $products->random($itemCount);
                $subtotal = 0;

                foreach ($selected as $product) {
                    $qty = $faker->numberBetween(1, 3);
                    $items[] = ['product' => $product, 'qty' => $qty];
                    $subtotal += $product->price * $qty;
                    $product->increment('sold', $qty);
                }

                $discount = $faker->boolean(20) ? $subtotal * $faker->randomElement([0.05, 0.1, 0.15]) : 0;
                $shipping = $subtotal >= 500000 ? 0 : 15000;
                $status = $faker->randomElement($statuses);
                $total = (int) ($subtotal - $discount + $shipping);

                if ($status === 'cancelled') {
                    foreach ($items as $it) {
                        $it['product']->decrement('sold', $it['qty']);
                    }
                }

                $order = Order::create([
                    'order_number' => 'KLR-' . now()->format('ym') . '-' . ($orderNumber++),
                    'customer_id' => $customer->id,
                    'status' => $status,
                    'payment_method' => $faker->randomElement($methods),
                    'shipping' => $shipping,
                    'discount' => (int) $discount,
                    'total' => $total,
                    'city' => $customer->city,
                    'order_date' => $orderDate,
                    'created_at' => $orderDate,
                ]);

                foreach ($items as $it) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $it['product']->id,
                        'qty' => $it['qty'],
                        'price' => $it['product']->price,
                    ]);
                }

                if ($status !== 'cancelled' && $status !== 'pending') {
                    Payment::create([
                        'order_id' => $order->id,
                        'method' => $order->payment_method,
                        'amount' => $total,
                        'status' => 'completed',
                        'paid_at' => $orderDate,
                    ]);
                }
            }
        }
    }
}
