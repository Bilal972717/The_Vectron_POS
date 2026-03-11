<?php

namespace App\Http\Controllers\Backend\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    /**
     * Feature 1 & 7: Process a return for specific items on an order.
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::with('products.product')->findOrFail($orderId);

        $request->validate([
            'returns' => 'required|array',
            'returns.*.order_product_id' => 'required|exists:order_products,id',
            'returns.*.return_qty' => 'required|integer|min:1',
        ]);

        $totalReturnAmount = 0;

        foreach ($request->returns as $ret) {
            $orderProduct = OrderProduct::findOrFail($ret['order_product_id']);

            // Ensure we don't return more than originally sold minus already returned
            $maxReturnable = $orderProduct->quantity - $orderProduct->return_qty;
            $returnQty = min((int)$ret['return_qty'], $maxReturnable);

            if ($returnQty <= 0) continue;

            $unitPrice = $orderProduct->quantity > 0
                ? ($orderProduct->total / $orderProduct->quantity)
                : 0;
            $returnAmount = round($unitPrice * $returnQty, 2);

            $orderProduct->return_qty += $returnQty;
            $orderProduct->return_amount += $returnAmount;
            $orderProduct->save();

            // Restore stock
            $orderProduct->product->increment('quantity', $returnQty);

            $totalReturnAmount += $returnAmount;
        }

        // Adjust order totals
        $order->total = max(0, $order->total - $totalReturnAmount);
        $order->sub_total = max(0, $order->sub_total - $totalReturnAmount);
        $order->due = max(0, $order->due - $totalReturnAmount);
        $order->is_returned = true;
        $order->save();

        session()->flash('success', 'Return processed successfully.');
        return redirect()->route('backend.admin.orders.invoice', $orderId);
    }

    /**
     * Show return form for an order.
     */
    public function create($orderId)
    {
        $order = Order::with(['customer', 'products.product'])->findOrFail($orderId);
        return view('backend.orders.return', compact('order'));
    }
}
