<?php

use App\Constants\Status;
use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\Extension;
use Carbon\Carbon;
use App\Lib\ClientInfo;
use App\Lib\Captcha;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Lib\PDFManager;
use App\Models\Product;
use App\Notify\Notify;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

function systemDetails()
{
    $system['name']          = 'Moeeen Traders';
    $system['version']       = '2.0';
    $system['build_version'] = '5.0.9';
    return $system;
}

function slug($string)
{
    return Str::slug($string);
}

function verificationCode($length)
{
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1) . '9';
    return random_int($min, $max);
}

function getNumber($length = 8)
{
    $characters       = '1234567890';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function activeTemplate($asset = false)
{
    $template = session('template') ?? gs('active_template');
    if ($asset) return 'assets/templates/' . $template . '/';
    return 'templates.' . $template . '.';
}

function activeTemplateName()
{
    $template = session('template') ?? gs('active_template');
    return $template;
}

function siteLogo($type = null)
{
    $name = $type ? "/logo_$type.png" : '/logo.png';
    return getImage(getFilePath('logoIcon') . $name);
}
function siteFavicon()
{
    return getImage(getFilePath('logoIcon') . '/favicon.ico');
}


function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003')
{
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}


function getTrx($length = 12)
{
    $characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false, $currencyFormat = true)
{
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    if ($currencyFormat) {
        if (gs('currency_format') == Status::CUR_BOTH) {
            return gs('cur_sym') . $printAmount . ' ' . __(gs('cur_text'));
        } elseif (gs('currency_format') == Status::CUR_TEXT) {
            return $printAmount . ' ' . __(gs('cur_text'));
        } else {
            return gs('cur_sym') . $printAmount;
        }
    }
    return $printAmount;
}


function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://api.qrserver.com/v1/create-qr-code/?data=$wallet&size=300x300&ecc=m";
}

function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}


function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}


function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}





function getPageSections($arr = false)
{
    $jsonUrl  = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}

if (!function_exists('gourmet_cola_products')) {
    /**
     * Get Gourmet Cola product names
     *
     * @param string|null $filter Optional filter (e.g., "Zero", "Vanilla")
     * @return array
     */
    function gourmet_cola_products($filter = null)
    {
        $products = [
            'Zeera Cola',
            'Cola',
            'Lemon',
            'Malta',
            'Twister',
            'Red Anar',
            'Soda IceCream',
            'Diet Cola',
            'Diet Lemon Up',
            'Mouj',
            'Phal Rus',
            'Phalpy',
            'Spark',
            'Water',
            'Sparkling Water',
        ];

        // Apply filter if provided
        if ($filter) {
            $products = array_values(array_filter($products, function ($product) use ($filter) {
                return stripos($product, $filter) !== false;
            }));
        }

        // Add sequential SKUs: P1, P2, P3, ...
        $productsWithSku = [];
        foreach ($products as $index => $product) {
            $skuNumber = $index + 1;
            $productsWithSku[] = [
                'name' => $product,
                'sku' => 'P' . $skuNumber, // Example: P1, P2, P3
            ];
        }

        return $productsWithSku;
    }
}
if (!function_exists('getProductTitle')) {
    /**
     * Get product title by product ID
     * Format: BrandName - ProductName (CategoryName)
     *
     * @param int $productId
     * @return string
     */
    function getProductTitle($productId)
    {
        $product = Product::with(['brand', 'category'])->find($productId);

        if (!$product) {
            return 'Unknown Product';
        }

        $brand    = $product->brand->name ?? 'No Brand';
        $category = $product->category->name ?? 'No Category';

        return "{$product->name} ({$category})";
    }
}
if (!function_exists('isWeightOpen')) {

    function isWeightOff()
    {

        return true;
    }
}

function getImage($image, $size = null)
{
    $clean = '';
    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }
    if ($size) {
        return route('placeholder.image', $size);
    }
    return asset('assets/images/default.png');
}


function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true, $pushImage = null)
{
    $globalShortCodes = [
        'site_name'       => gs('site_name'),
        'site_currency'   => gs('cur_text'),
        'currency_symbol' => gs('cur_sym'),
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify               = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes   = $shortCodes;
    $notify->user         = $user;
    $notify->createLog    = $createLog;
    $notify->pushImage    = $pushImage;
    $notify->userColumn   = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}

function getPaginate($paginate = null)
{
    if (!$paginate) {
        $paginate = gs('paginate_number');
    }
    return $paginate;
}

function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}


function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3) $class = 'side-menu--open';
    elseif ($type == 2) $class = 'sidebar-submenu__open';
    else   $class              = 'active';

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) return $class;
        }
    } elseif (request()->routeIs($routeName)) {
        if ($param) {
            $routeParam = array_values(@request()->route()->parameters ?? []);
            if (strtolower(@$routeParam[0]) == strtolower($param)) return $class;
            else return;
        }
        return $class;
    }
}


function fileUploader($file, $location, $size = null, $old = null, $thumb = null, $filename = null)
{
    $fileManager           = new FileManager($file);
    $fileManager->path     = $location;
    $fileManager->size     = $size;
    $fileManager->old      = $old;
    $fileManager->thumb    = $thumb;
    $fileManager->filename = $filename;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    Carbon::setlocale('en');
    return Carbon::parse($date)->diffForHumans();
}


function showDateTime($date, $format = 'Y-m-d h:i A')
{
    if (!$date) {
        return '-';
    }
    Carbon::setlocale('en');
    return Carbon::parse($date)->translatedFormat($format);
}





function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path     = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value)
{
    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null)
{
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    if ($key) return @$general->$key;
    return $general;
}
function isImage($string)
{
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension     = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string)
{
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}


function convertToReadableSize($size)
{
    preg_match('/^(\d+)([KMG])$/', $size, $matches);
    $size = (int)$matches[1];
    $unit = $matches[2];

    if ($unit == 'G') {
        return $size . 'GB';
    }

    if ($unit == 'M') {
        return $size . 'MB';
    }

    if ($unit == 'K') {
        return $size . 'KB';
    }

    return $size . $unit;
}

function permit($code)
{
    return Role::hasPermission($code);
}

function downloadPDF($viewName, $data)
{
    $pdfManager = new PDFManager($viewName, $data);
    return $pdfManager->generate();
}

function generateInvoiceNumber($lastNumber)
{
    $prefix         = 'S-';
    $number         = str_replace($prefix, '', $lastNumber ?? 0) + 1;
    $lengthOfNumber = strlen($number);
    $numberOfZeros  = 6 - $lengthOfNumber;
    $totalLength    = $numberOfZeros + $lengthOfNumber;
    $invoiceNumber  = str_pad($number, $totalLength, '0', STR_PAD_LEFT);
    return 'S-' . $invoiceNumber;
}
function generateInvoiceNumberP($lastNumber)
{
    $prefix         = 'P-';
    $number         = str_replace($prefix, '', $lastNumber ?? 0) + 1;
    $lengthOfNumber = strlen($number);
    $numberOfZeros  = 6 - $lengthOfNumber;
    $totalLength    = $numberOfZeros + $lengthOfNumber;
    $invoiceNumber  = str_pad($number, $totalLength, '0', STR_PAD_LEFT);
    return 'P-' . $invoiceNumber;
}
function importFileValidation($request)
{
    $request->validate([
        'file' => ['required', 'file', 'max:3072', new FileTypeValidate(['csv'])],
    ]);
}


function importCSV($request, $model, $reqHeader, $unique)
{
    $request->validate([
        'file' => ['required', 'file', 'max:3072', new FileTypeValidate(['csv'])],
    ]);
    $file    = $request->file('file');
    $csvData = file_get_contents($file->getRealPath());
    $rows    = array_map('str_getcsv', explode("\n", $csvData));


    array_pop($rows);
    $header         = array_shift($rows);
    $missingHeaders = array_diff($reqHeader, $header);


    if (!empty($missingHeaders)) {
        return back()->withErrors("Missing required headers: " . implode(', ', $missingHeaders));
    }

    $importData = [];

    foreach ($rows as $row) {
        if (count($header) === count($row)) {
            $data = [];
            foreach ($header as $index => $headerName) {
                $data[$headerName] = $row[$index];
            }
            // if (in_array(null, $data) || $data === null) {
            //     continue;
            // }
            if (!array_key_exists($unique, $data)) {
                continue;
            }
            $existing = $model::where($unique, $data[$unique])->first();
            if (!$existing) {
                $importData[] = $data;
            }
        }
    }

    $notify = '';
    try {
        $existing   = $model::whereIn($unique, array_keys($importData))->pluck($unique)->toArray();
        $importData = array_filter($importData, function ($item) use ($existing, $unique) {
            return !in_array($item[$unique], $existing);
        });

        if (count($importData) > 0) {
            $model::insert($importData);
            $notify = 'CSV imported successfully';
        }
    } catch (\Exception $e) {
        $notify = 'Error occurred during CSV import: ' . $e->getMessage();
    }
    $importResult = [
        'data'   => $importData,
        'notify' => $notify,
    ];
    return $importResult;
}
