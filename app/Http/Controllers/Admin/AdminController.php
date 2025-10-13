<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard()
    {
        $pageTitle = 'Dashboard';

        $widget['total_customer'] = Customer::count();
        $widget['total_product']  = Product::count();
        $widget['total_category'] = Category::count();
        $widget['total_supplier'] = Supplier::count();


        $widget['total_purchase_count']        = Purchase::count();
        $widget['total_purchase']              = Purchase::sum('payable_amount');
        $widget['total_purchase_return']       = PurchaseReturn::sum('receivable_amount');
        $widget['total_purchase_return_count'] = PurchaseReturn::count();

        $widget['total_sale_count']        = Sale::where('status','!=','pending')->count();
        $widget['total_sale']              = Sale::where('status','!=','pending')->sum('receivable_amount');
        $widget['total_sale_return_count'] = SaleReturn::count();
        $widget['total_sale_return']       = SaleReturn::sum('payable_amount');

        $alertProductsQty = Product::select('products.id', 'products.name', 'units.name as unit_name', 'products.alert_quantity', 'product_stocks.quantity as quantity', 'warehouses.name as warehouse_name')
            ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->join('warehouses', 'warehouses.id', '=', 'product_stocks.warehouse_id')
            ->whereRaw('products.alert_quantity >= product_stocks.quantity')
            ->orderBy('products.alert_quantity')->take(8)->get();

        //top 5 best sales products
        $topSellingProducts =  Product::where('total_sale', '!=', 0)->with('unit:id,name')->orderBy('total_sale', 'desc')->limit(8)->get();
        $saleReturns        = SaleReturn::with('sale.warehouse', 'customer')->orderBy('id', 'desc')->take(8)->get();

        return view('admin.dashboard', compact('pageTitle', 'widget', 'alertProductsQty', 'topSellingProducts', 'saleReturns'));
    }




    public function purchaseAndSaleReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }
        $purchases = Purchase::whereDate('purchase_date', '>=', $request->start_date)
            ->whereDate('purchase_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(purchase_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();


        $sales = Sale::where('status','!=','pending')->whereDate('sale_date', '>=', $request->start_date)
            ->whereDate('sale_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(sale_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {

            $data[] = [
                'created_on' => $date,
                'purchases' => getAmount($purchases->where('created_on', $date)->first()?->amount ?? 0),
                'sales' => getAmount($sales->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'name' => 'Purchases',
                'data' => $data->pluck('purchases')
            ],
            [
                'name' => 'Sales',
                'data' => $data->pluck('sales')
            ]
        ];

        return response()->json($report);
    }

    public function saleAndSaleReturnReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $saleData   = Sale::where('status','!=','pending')->whereDate('sale_date', '>=', $request->start_date)
            ->whereDate('sale_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(sale_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $saleReturnData  = SaleReturn::whereDate('return_date', '>=', $request->start_date)
            ->whereDate('return_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(return_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();


        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'sales' => getAmount($saleData->where('created_on', $date)->first()?->amount ?? 0),
                'sales_return' => getAmount($saleReturnData->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'name' => 'Sales',
                'data' => $data->pluck('sales')
            ],
            [
                'name' => 'Sales Return',
                'data' => $data->pluck('sales_return')
            ]
        ];

        return response()->json($report);
    }


    public function purchaseAndPurchaseReturnReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $saleData   = Purchase::whereDate('purchase_date', '>=', $request->start_date)
            ->whereDate('purchase_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(purchase_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $saleReturnData  = PurchaseReturn::whereDate('return_date', '>=', $request->start_date)
            ->whereDate('return_date', '<=', $request->end_date)
            ->selectRaw('SUM(total_price) AS amount')
            ->selectRaw("DATE_FORMAT(return_date, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();


        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'purchases' => getAmount($saleData->where('created_on', $date)->first()?->amount ?? 0),
                'purchases_return' => getAmount($saleReturnData->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'name' => 'Purchases',
                'data' => $data->pluck('purchases')
            ],
            [
                'name' => 'Purchases Return',
                'data' => $data->pluck('purchases_return')
            ]
        ];

        return response()->json($report);
    }

    private function getAllDates($startDate, $endDate)
    {
        $dates = [];
        $currentDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function  getAllMonths($startDate, $endDate)
    {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }


    public function profile()
    {
        $pageTitle = 'Profile';
        $admin = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }


    public function notificationRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function banned()
    {
        $pageTitle = 'Account Banned';
        if (auth()->guard('admin')->user()->status == 1) {
            return to_route('admin.dashboard');
        }
        return view('admin.banned', compact('pageTitle'));
    }


    public function requestReport()
    {
        $pageTitle = 'Your Listed Report & Request';
        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response = CurlRequest::curlContent($url);
        $response = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type'] = $request->type;
        $arr['message'] = $request->message;
        $response = CurlRequest::curlPostContent($url, $arr);
        $response = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAllNotification()
    {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification()
    {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id)
    {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash)
    {
        $filePath = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
}
