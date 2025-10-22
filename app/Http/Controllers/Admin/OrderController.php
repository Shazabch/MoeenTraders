<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetails;
use App\Models\Customer;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $pageTitle;

    public function __construct()
    {
        $this->pageTitle = 'All Orders';
    }

    protected function getSales()
    {
        $query = Sale::searchable(['invoice_no', 'due_amount', 'customer:name,mobile', 'status'])
            ->dateFilter('sale_date')
            ->with('customer', 'saleReturn')
            ->where('status', 'pending')
            ->orderBy('id', 'desc');

        $user = Auth::guard('admin')->user();
        if ($user && $user->role_id !== 0) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public function index()
    {
        $pageTitle = $this->pageTitle;
        $sales     = $this->getSales()->paginate(getPaginate());
        $banks     = Bank::all();
        $pdfButton = true;
        $routePDF  = route('admin.order.pdf') . "?";
        $routeCSV  = route('admin.order.csv') . "?";

        if (request()->search) {
            $routePDF .= "search=" . request()->search . "&";
            $routeCSV .= "search=" . request()->search . "&";
        }
        if (request()->date) {
            $routePDF .= "date=" . request()->date;
            $routeCSV .= "date=" . request()->date;
        }

        return view('admin.order.index', compact('pageTitle', 'sales', 'pdfButton', 'routePDF', 'routeCSV', 'banks'));
    }

    public function salePDF()
    {
        $pageTitle = $this->pageTitle;
        $sales     = $this->getSales()->get();
        return downloadPDF('pdf.order.list', compact('pageTitle', 'sales'));
    }

    public function saleCSV()
    {
        $pageTitle = $this->pageTitle;
        $filename  = $this->downloadCsv($pageTitle, $this->getSales()->get());
        return response()->download(...$filename);
    }

    protected function downloadCsv($pageTitle, $data)
    {
        $filename = "assets/files/csv/example.csv";
        $myFile   = fopen($filename, 'w');
        $column   = "Order No.,Date,Customer,Mobile,Total Amount,Status,Discount,Receivable,Paid,Due\n";
        $curSym   = gs('cur_sym');

        foreach ($data as $sale) {
            $invoice        = $sale->invoice_no;
            $date           = showDateTime(@$sale->sale_date, 'd-m-Y');
            $customer       = $sale->customer?->name;
            $customerMobile = $sale->customer?->mobile;
            $status         = ucfirst($sale->status);
            $totalAmount    = $curSym . getAmount($sale->total_price);
            $discount       = $curSym . getAmount($sale->discount_amount);
            $receivable     = $curSym . getAmount($sale->receivable_amount);
            $received       = $curSym . getAmount($sale->received_amount);
            $due            = $curSym . getAmount($sale->due_amount);

            $column .= "$invoice,$date,$customer,$customerMobile,$totalAmount,$status,$discount,$receivable,$received,$due \n";
        }

        fwrite($myFile, $column);
        $headers = [
            'Content-Type' => 'application/csv',
        ];
        $name  = $pageTitle . time() . '.csv';
        $array = [$filename, $name, $headers];
        return $array;
    }

    public function downloadInvoice($id)
    {
        $pageTitle = "INVOICE";
        $sale      = Sale::where('id', $id)
            ->with('customer', 'saleDetails', 'saleDetails.product', 'saleDetails.product.unit')
            ->whereHas('saleDetails')
            ->firstOrFail();

        $customer = $sale->customer;
        $pdf = PDF::loadView('pdf.order.invoice', compact('pageTitle', 'sale', 'customer'));

        $customerName = preg_replace('/[^A-Za-z0-9\-]/', '_', $customer?->name);
        $invoiceNo    = $sale->invoice_no ?? 'INV-00';
        $date         = now()->format('Y-m-d');

        return $pdf->download("Order_Inv_{$invoiceNo}_{$customerName}_{$date}.pdf");
    }

    public function create()
    {
        $pageTitle     = 'New Order';
        $lastSale      = Sale::orderBy('id', 'DESC')->first();
        $lastInvoiceNo = @$lastSale->invoice_no;
        $invoiceNumber = generateInvoiceNumber($lastInvoiceNo);
        $customers     = Customer::select('id', 'name', 'mobile')->get();
        $categories      = Category::all();
        $products      = Product::with('unit')
            ->select('id', 'name', 'sku', 'selling_price', 'image', 'unit_id','category_id')
            ->orderBy('name')
            ->get();

        // Add image URL to each product
        foreach ($products as $product) {
            $product->image_url = getImage(getFilePath('product') . '/' . $product->image, getFileSize('product'));
            $product->display_title = getProductTitle($product->id);
            $product->brand_name = $product->brand->name ?? 'No Brand';
            $product->category_name = $product->category->name ?? 'No Category';
        }

        return view('admin.order.form', compact('pageTitle', 'invoiceNumber', 'customers', 'products','categories'));
    }

    public function edit($id)
    {
        $sale      = Sale::where('id', $id)
            ->with('saleDetails', 'saleDetails.product', 'saleDetails.product.unit', 'customer')
            ->whereHas('saleDetails')
            ->firstOrFail();
        $pageTitle = 'Edit Order';
        $customers = Customer::select('id', 'name', 'mobile')->get();
        $categories      = Category::all();
        $products  = Product::with('unit')
            ->select('id', 'name', 'sku', 'selling_price', 'image', 'unit_id')
            ->orderBy('name')
            ->get();

        // Add image URL to each product
        foreach ($products as $product) {
            $product->image_url = getImage(getFilePath('product') . '/' . $product->image, getFileSize('product'));
            $product->display_title = getProductTitle($product->id);
            $product->brand_name = $product->brand->name ?? 'No Brand';
            $product->category_name = $product->category->name ?? 'No Category';
        }

        return view('admin.order.form', compact('pageTitle', 'sale', 'customers', 'products','categories'));
    }

    public function store(Request $request)
    {
        $this->validation($request);

        $this->products   = collect($request->products);
        $this->productIds = $this->products->pluck('product_id')->toArray();
        $this->totalPrice = $this->getTotalPrice();

        if ($request->discount > $this->totalPrice) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount amount mustn\'t be greater than total price'
                ], 422);
            }
            $notify[] = ['error', 'Discount amount mustn\'t be greater than total price'];
            return back()->withNotify($notify)->withInput();
        }

        $lastSale      = Sale::orderBy('id', 'DESC')->first();
        $lastInvoiceNo = @$lastSale->invoice_no;

        $sale             = new Sale();
        $sale->invoice_no = generateInvoiceNumber($lastInvoiceNo);

        $sale             = $this->saveSaleData($sale);

        $this->storeSaleDetails($sale);

        Action::newEntry($sale, 'CREATED');
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'redirect' => route('admin.order.index'),
                'data' => [
                    'order_id' => $sale->id,
                    'invoice_no' => $sale->invoice_no
                ]
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validation($request);

        $this->products   = collect($request->products);
        $this->totalPrice = $this->getTotalPrice();
        $this->productIds = $this->products->pluck('product_id')->toArray();

        if ($request->discount > $this->totalPrice) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount amount mustn\'t be greater than total price'
                ], 422);
            }
            $notify[] = ['error', 'Discount amount mustn\'t be greater than total price'];
            return back()->withNotify($notify)->withInput();
        }

        $sale = Sale::with('saleDetails')->findOrFail($id);

        if ($sale->return_status) {
            $notify[] = ['error', 'You can\'t update this order'];
            return back()->withNotify($notify);
        }

        $this->saleDetails = $sale->saleDetails;

        $sale = $this->saveSaleData($sale);
        $this->storeSaleDetails($sale);

        Action::newEntry($sale, 'UPDATED');

        $notify[] = ['success', 'Order updated successfully'];
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'redirect' => route('admin.order.index'),
                'data' => [
                    'order_id' => $sale->id,
                    'invoice_no' => $sale->invoice_no
                ]
            ]);
        }
    }

    protected function saveSaleData($sale)
    {
        $request    = request();
        $discount   = $request->discount ?? 0;
        $receivable = $this->totalPrice - $discount;
        $dueAmount  = $receivable - ($sale->received_amount ?? 0);
        $received_amount = 0;

        $sale->customer_id       = $request->customer_id;
        $sale->warehouse_id      = $request->warehouse_id ?? Warehouse::first()->id ?? 1; // No warehouse needed
        $sale->sale_date         = Carbon::parse($request->sale_date);
        $sale->status            = $request->status ?? 'pending';
        $sale->total_price       = $this->totalPrice;
        $sale->discount_amount   = $discount;
        $sale->received_amount   = $received_amount;
        $sale->receivable_amount = $receivable;
        $sale->due_amount        = $dueAmount;
        $sale->note              = $request->note;

        $sale->save();

        return $sale;
    }

    protected function storeSaleDetails($sale)
    {
        $saleDetails = @$this->saleDetails ?? null;

        // Remove old details if updating
        if ($saleDetails) {
            $existingProductIds = $saleDetails->pluck('product_id')->toArray();
            $newProductIds = $this->products->pluck('product_id')->toArray();

            // Delete products that are no longer in the order
            $toDelete = array_diff($existingProductIds, $newProductIds);
            if (!empty($toDelete)) {
                SaleDetails::where('sale_id', $sale->id)
                    ->whereIn('product_id', $toDelete)
                    ->delete();
            }
        }

        foreach ($this->products as $product) {
            $product    = (object) $product;
            $saleDetail = new SaleDetails();

            if ($saleDetails) {
                $saleDetail = $saleDetails->where('product_id', $product->product_id)->first() ?? new SaleDetails();
            }

            $saleDetail->sale_id    = $sale->id;
            $saleDetail->product_id = $product->product_id;
            $saleDetail->quantity   = $product->quantity;
            $saleDetail->price      = $product->price;
            $saleDetail->total      = $product->quantity * $product->price;
            $saleDetail->save();
        }
    }

    protected function getTotalPrice()
    {
        return $this->products->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });
    }

    protected function validation($request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'sale_date'             => 'required|date_format:Y-m-d',
            'status'                => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'products'              => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity'   => 'required|integer|gt:0',
            'products.*.price'      => 'required|numeric|gte:0',
            'discount'              => 'nullable|numeric|gte:0',
            'note'                  => 'nullable|string',
        ]);
    }

    public function searchProduct(Request $request)
    {
        $search = $request->search;

        $products = Product::where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        })
            ->with('unit')
            ->limit(20)
            ->get();

        if ($products) {
            return response()->json([
                'success' => true,
                'data'    => $products,
            ]);
        }

        return response()->json([
            'success' => false,
            'data'    => [],
        ]);
    }

    public function lastInvoice()
    {
        $lastInvoiceNo = Sale::latest()->first()->invoice_no ?? 'ORD-0000';

        return response()->json([
            'status' => true,
            'data'   => $lastInvoiceNo,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
        ]);

        $sale = Sale::findOrFail($id);
        $sale->status = $request->status;
        $sale->save();

        Action::newEntry($sale, 'STATUS_UPDATED');

        $notify[] = ['success', 'Order status updated successfully'];
        return back()->withNotify($notify);
    }
}
