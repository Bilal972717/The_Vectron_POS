<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{

    public function saleReport(Request $request)
    {

        abort_if(!auth()->user()->can(abilities: 'reports_sales'), 403);
        // Get user input or set default values
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Parse and set start date
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input) ?: Carbon::today()->subDays(29)->startOfDay();
        $start_date = $start_date->startOfDay();

        // Parse and set end date
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input) ?: Carbon::today()->endOfDay();
        $end_date = $end_date->endOfDay();

        // Feature 9: filter credit-only (due > 0 / status = 0) customers
        $creditOnly = $request->boolean('credit_only');

        $ordersQuery = Order::whereBetween('created_at', [$start_date, $end_date])->with('customer');
        if ($creditOnly) {
            $ordersQuery->where('status', 0); // 0 = Due (credit purchase)
        }
        $orders = $ordersQuery->get();

        // Calculate totals
        $data = [
            'orders' => $orders,
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
            'credit_only' => $creditOnly,
        ];

        return view('backend.reports.sale-report', $data);
    }

    /**
     * Feature 4: Due payments report by customer name and city.
     */
    public function duePaymentsReport(Request $request)
    {
        abort_if(!auth()->user()->can('reports_sales'), 403);

        $search = $request->input('search', '');
        $city   = $request->input('city', '');

        $query = \App\Models\Customer::with(['orders' => function ($q) {
            $q->where('status', 0)->where('due', '>', 0);
        }])->whereHas('orders', function ($q) {
            $q->where('status', 0)->where('due', '>', 0);
        });

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }

        $customers = $query->get()->map(function ($customer) {
            $customer->total_due = $customer->orders->sum('due');
            $customer->total_paid = $customer->orders->sum('paid');
            return $customer;
        });

        $cities = \App\Models\Customer::whereNotNull('city')->distinct()->pluck('city');

        return view('backend.reports.due-payments', compact('customers', 'search', 'city', 'cities'));
    }

    /**
     * Feature 8: Sales Ledger by specific customer and date range.
     */
    public function salesLedger(Request $request)
    {
        abort_if(!auth()->user()->can('reports_sales'), 403);

        $customer_id = $request->input('customer_id');
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input   = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input)->startOfDay();
        $end_date   = Carbon::createFromFormat('Y-m-d', $end_date_input)->endOfDay();

        $customers = \App\Models\Customer::orderBy('name')->get();
        $orders = collect();
        $customer = null;

        if ($customer_id) {
            $customer = \App\Models\Customer::find($customer_id);
            $orders = Order::with(['products.product'])
                ->where('customer_id', $customer_id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('created_at')
                ->get();
        }

        $totals = [
            'sub_total' => $orders->sum('sub_total'),
            'discount'  => $orders->sum('discount'),
            'total'     => $orders->sum('total'),
            'paid'      => $orders->sum('paid'),
            'due'       => $orders->sum('due'),
        ];

        return view('backend.reports.sales-ledger', compact(
            'customers', 'orders', 'customer', 'totals',
            'start_date', 'end_date', 'customer_id'
        ));
    }
    public function saleSummery(Request $request)
    {

        abort_if(!auth()->user()->can('reports_summary'), 403);
        // Get user input or set default values
        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Parse and set start date
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input) ?: Carbon::today()->subDays(29)->startOfDay();
        $start_date = $start_date->startOfDay();

        // Parse and set end date
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input) ?: Carbon::today()->endOfDay();
        $end_date = $end_date->endOfDay();
        // Retrieve orders within the date range
        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();

        // Calculate totals
        $data = [
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'start_date' => $start_date->format('M d, Y'),
            'end_date' => $end_date->format('M d, Y'),
        ];

        return view('backend.reports.sale-summery', $data);
    }
    function inventoryReport(Request $request)
    {

        abort_if(!auth()->user()->can('reports_inventory'), 403);
        if ($request->ajax()) {
            $products = Product::latest()->active()->get();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', fn($data) => $data->name)
                ->addColumn('sku', fn($data) => $data->sku)
                ->addColumn(
                    'price',
                    fn($data) => $data->discounted_price .
                        ($data->price > $data->discounted_price
                            ? '<br><del>' . $data->price . '</del>'
                            : '')
                )
                ->addColumn('quantity', fn($data) => $data->quantity . ' ' . optional($data->unit)->short_name)
                ->rawColumns(['name', 'sku', 'price', 'quantity', 'status'])
                ->toJson();
        }
        return view('backend.reports.inventory');
    }
}
