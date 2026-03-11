<?php

namespace App\Http\Controllers\Backend\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\PosCart;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with('customer', 'packer', 'deliverer')->get();
            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('saleId', fn($data) => "#" . $data->id)
                ->addColumn('customer', fn($data) => $data->customer->name ?? '-')
                ->addColumn('item', fn($data) => $data->total_item)
                ->addColumn('sub_total', fn($data) => number_format($data->sub_total, 2, '.', ','))
                ->addColumn('discount', fn($data) => number_format($data->discount, 2, '.', ','))
                ->addColumn('total', fn($data) => number_format($data->total, 2, '.', ','))
                ->addColumn('paid', fn($data) => number_format($data->paid, 2, '.', ','))
                ->addColumn('due', fn($data) => number_format($data->due, 2, '.', ','))
                ->addColumn('packed_by', fn($data) => $data->packer->name ?? '<span class="text-muted">—</span>')
                ->addColumn('delivered_by', fn($data) => $data->deliverer->name ?? '<span class="text-muted">—</span>')
                ->addColumn('status', fn($data) => $data->status
                    ? '<span class="badge bg-primary">Paid</span>'
                    : '<span class="badge bg-danger">Due</span>')
                ->addColumn('action', function ($data) {
                    $buttons = '';
                    $buttons .= '<a class="btn btn-info btn-sm" href="' . route('backend.admin.orders.detail', $data->id) . '"><i class="fas fa-eye"></i> Detail</a>';
                    $buttons .= '<a class="btn btn-success btn-sm" href="' . route('backend.admin.orders.invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> Invoice</a>';
                    $buttons .= '<a class="btn btn-secondary btn-sm" href="' . route('backend.admin.orders.pos-invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> Pos Invoice</a>';
                    if (!$data->status) {
                        $buttons .= '<a class="btn btn-warning btn-sm" href="' . route('backend.admin.due.collection', $data->id) . '"><i class="fas fa-receipt"></i> Due Collection</a>';
                    }
                    $buttons .= '<a class="btn btn-primary btn-sm" href="' . route('backend.admin.orders.transactions', $data->id) . '"><i class="fas fa-exchange-alt"></i> Transactions</a>';
                    return $buttons;
                })
                ->rawColumns(['saleId', 'customer', 'item', 'sub_total', 'discount', 'total', 'paid', 'due', 'packed_by', 'delivered_by', 'status', 'action'])
                ->toJson();
        }
        return view('backend.orders.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => [
                'required',
                'exists:customers,id',
                'integer',
            ],
            'order_discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            // Feature 11: promised payment date
            'promised_payment_date' => ['nullable', 'date'],
            // Feature 12: delivery status
            'is_delivered' => ['nullable', 'boolean'],
            'delivery_note' => ['nullable', 'string', 'max:255'],
            // Staff assignments
            'packed_by'    => ['nullable', 'exists:users,id'],
            'delivered_by' => ['nullable', 'exists:users,id'],
            // Feature 2: manual prices per product keyed by product_id
            'manual_prices' => ['nullable', 'array'],
            'manual_prices.*' => ['nullable', 'numeric', 'min:0'],
        ], [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'order_discount.numeric' => 'The order discount must be a number.',
            'paid.numeric' => 'The amount paid must be a number.',
        ]);
        $carts = PosCart::with('product')->where('user_id', auth()->id())->get();
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
            // Feature 11
            'promised_payment_date' => $request->promised_payment_date,
            // Feature 12
            'is_delivered' => $request->boolean('is_delivered'),
            'delivery_note' => $request->delivery_note,
            // Staff packer & deliverer
            'packed_by'    => $request->packed_by ?: null,
            'delivered_by' => $request->delivered_by ?: null,
        ]);
        $totalAmountOrder = 0;
        $orderDiscount = $request->order_discount;
        $manualPrices = $request->manual_prices ?? [];
        foreach ($carts as $cart) {
            // Feature 2: use manual price if provided for this product
            $manualPrice = isset($manualPrices[$cart->product_id])
                ? (float)$manualPrices[$cart->product_id]
                : null;
            $effectivePrice = ($manualPrice !== null && $manualPrice >= 0)
                ? $manualPrice
                : $cart->product->discounted_price;
            $mainTotal = $cart->product->price * $cart->quantity;
            $totalAfterDiscount = $effectivePrice * $cart->quantity;
            $discount = $mainTotal - $totalAfterDiscount;
            $totalAmountOrder += $totalAfterDiscount;
            $order->products()->create([
                'quantity' => $cart->quantity,
                'price' => $cart->product->price,
                'manual_price' => $manualPrice,
                'purchase_price' => $cart->product->purchase_price,
                'sub_total' => $mainTotal,
                'discount' => $discount,
                'total' => $totalAfterDiscount,
                'product_id' => $cart->product->id,
            ]);
            $cart->product->quantity = $cart->product->quantity - $cart->quantity;
            $cart->product->save();
        }
        $total = $totalAmountOrder - $orderDiscount;
        $due = $total - $request->paid;
        $order->sub_total = $totalAmountOrder;
        $order->discount = $orderDiscount;
        $order->paid = $request->paid;
        $order->total = round((float)$total, 2);
        $order->due = round((float)$due, 2);
        $order->status = round((float)$due, 2) <= 0;
        $order->save();
        //create order transaction
        if ($request->paid > 0) {
            $orderTransaction = $order->transactions()->create([
                'amount' => $request->paid,
                'customer_id' => $order->customer_id,
                'user_id' => auth()->id(),
                'paid_by' => 'cash',
            ]);
        }

        $carts = PosCart::where('user_id', auth()->id())->delete();
        return response()->json(['message' => 'Order completed successfully', 'order' => $order], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Order detail page — shows all info including packer, deliverer, items, totals.
     */
    public function detail($id)
    {
        $order = Order::with([
            'customer',
            'products.product.category',
            'products.product.unit',
            'packer',
            'deliverer',
            'transactions',
        ])->findOrFail($id);

        return view('backend.orders.detail', compact('order'));
    }

    public function invoice($id)
    {
        $order = Order::with(['customer', 'products.product', 'packer', 'deliverer'])->findOrFail($id);
        // Feature 6: increment print count so reprints show "Duplicate"
        $order->increment('print_count');
        $isDuplicate = $order->print_count > 1;
        return view('backend.orders.print-invoice', compact('order', 'isDuplicate'));
    }
    public function collection(Request $request, $id)
    {

        $order = Order::findOrFail($id);
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);


            $due = $order->due - $data['amount'];
            $paid = $order->paid + $data['amount'];
            $order->due = round((float)$due, 2);
            $order->paid = round((float)$paid, 2);
            $order->status = round((float)$due, 2) <= 0;
            $order->save();
            $collection_amount = $data['amount'];
            //create order transaction

            $orderTransaction = $order->transactions()->create([
                'amount' => $data['amount'],
                'customer_id' => $order->customer_id,
                'user_id' => auth()->id(),
                'paid_by' => 'cash',
            ]);
            return to_route('backend.admin.collectionInvoice', $orderTransaction->id);
        }
        return view('backend.orders.collection.create', compact('order'));
    }

    //collection invoice by order_transaction id
    public function collectionInvoice($id)
    {
        $transaction = OrderTransaction::findOrFail($id);
        $collection_amount = $transaction->amount;
        $order = $transaction->order;
        return view('backend.orders.collection.invoice', compact('order', 'collection_amount', 'transaction'));
    }
    //transactions by order id
    public function transactions($id)
    {
        $order = Order::with('transactions')->findOrFail($id);
        return view('backend.orders.collection.index', compact('order'));
    }

    public function posInvoice($id)
    {
        $order = Order::with(['customer', 'products.product', 'packer', 'deliverer'])->findOrFail($id);
        $order->increment('print_count');
        $isDuplicate = $order->print_count > 1;
        $maxWidth = readConfig('receiptMaxwidth')??'300px';
        return view('backend.orders.pos-invoice', compact('order', 'maxWidth', 'isDuplicate'));
    }
}
