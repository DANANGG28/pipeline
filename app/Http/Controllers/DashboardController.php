<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function api(Request $request): JsonResponse
    {
        $range = $request->integer('range', 30);
        $start = $range > 0 ? now()->subDays($range)->startOfDay() : null;

        $orders = Order::query()
            ->when($start, fn ($q) => $q->where('order_date', '>=', $start))
            ->where('status', '!=', 'cancelled');

        $revenue = (clone $orders)->sum('total');
        $orderCount = (clone $orders)->count();
        $avgOrder = $orderCount > 0 ? (int) ($revenue / $orderCount) : 0;
        $newCustomers = \App\Models\Customer::when($start, fn ($q) => $q->where('created_at', '>=', $start))->count();

        $monthly = $this->monthlyRevenue($range);

        $categorySales = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->when($start, fn ($q) => $q->where('orders.order_date', '>=', $start))
            ->where('orders.status', '!=', 'cancelled')
            ->select('categories.name', 'categories.color', DB::raw('SUM(order_items.qty * order_items.price) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->get();

        $methods = Payment::query()
            ->when($start, fn ($q) => $q->where('paid_at', '>=', $start))
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->when($start, fn ($q) => $q->where('orders.order_date', '>=', $start))
            ->where('orders.status', '!=', 'cancelled')
            ->select('products.name', DB::raw('SUM(order_items.qty) as qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $recentOrders = Order::query()
            ->with('customer:id,name,email,city')
            ->withCount('items')
            ->orderByDesc('order_date')
            ->limit(10)
            ->get();

        $lowStock = Product::query()
            ->with('category:id,name')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(8)
            ->get();

        return response()->json([
            'kpi' => [
                'revenue' => $revenue,
                'orders' => $orderCount,
                'avg_order' => $avgOrder,
                'new_customers' => $newCustomers,
                'low_stock_count' => Product::where('stock', '<=', 5)->count(),
            ],
            'monthly' => $monthly,
            'categories' => $categorySales,
            'methods' => $methods->map(fn ($m) => [
                'method' => $m->method,
                'total' => (int) $m->total,
                'label' => $this->methodLabel($m->method),
            ]),
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders->map(fn (Order $o) => [
                'order_number' => $o->order_number,
                'customer' => $o->customer->name ?? '-',
                'customer_id' => $o->customer->id ?? null,
                'city' => $o->customer->city ?? $o->city,
                'items' => $o->items_count ?? null,
                'total' => (int) $o->total,
                'status' => $o->status,
                'method' => $o->payment_method,
                'date' => $o->order_date->translatedFormat('d M Y H:i'),
            ]),
            'low_stock' => $lowStock->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image,
                'sku' => $p->sku,
                'category' => $p->category->name ?? '-',
                'stock' => $p->stock,
                'price' => (int) $p->price,
                'sold' => $p->sold,
            ]),
        ]);
    }

    private function monthlyRevenue(int $range): array
    {
        $months = $range > 90 ? 12 : min(6, max(2, (int) ceil($range / 30)));

        $rows = DB::table('orders')
            ->where('status', '!=', 'cancelled')
            ->selectRaw("strftime('%Y-%m', order_date) as ym, SUM(total) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $totals = [];
        $now = CarbonImmutable::now();
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = $now->subMonths($i)->format('Y-m');
            $labels[] = $now->subMonths($i)->translatedFormat('M Y');
            $totals[] = (int) ($rows[$ym] ?? 0);
        }

        return ['labels' => $labels, 'totals' => $totals];
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'cod' => 'COD',
            default => ucfirst($method),
        };
    }
}
