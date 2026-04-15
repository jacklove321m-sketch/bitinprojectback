<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Bank;
use App\Menu;
use App\FalseData;
use App\Market;
use App\Setting;
use App\HistoricalData;
use App\Users;
use App\Utils\RPC;
use App\DAO\UploaderDAO;

class DefaultController extends Controller
{
    const MAX_UPLOAD_SIZE = 10485760;

    private function allowedImageMimes()
    {
        $mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
            $mimes['image/webp'] = 'webp';
        }

        return $mimes;
    }

    private function containsSuspiciousImagePayload($content)
    {
        $patterns = [
            '/<\?(php|=)?/i',
            '/<script\b/i',
            '/eval\s*\(/i',
            '/assert\s*\(/i',
            '/shell_exec\s*\(/i',
            '/base64_decode\s*\(/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    private function buildUploadDirectory($relativeDir)
    {
        $relativeDir = trim($relativeDir, '/');
        $absoluteDir = public_path($relativeDir);
        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        return [$absoluteDir, '/' . $relativeDir];
    }

    private function generateSafeImageName($extension)
    {
        try {
            $random = bin2hex(random_bytes(8));
        } catch (\Exception $e) {
            $random = mt_rand(100000, 999999);
        }

        return date('YmdHis') . $random . '.' . $extension;
    }

    private function createImageResource($mime, $source)
    {
        switch ($mime) {
            case 'image/jpeg':
                return imagecreatefromjpeg($source);
            case 'image/png':
                return imagecreatefrompng($source);
            case 'image/gif':
                return imagecreatefromgif($source);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($source) : false;
            default:
                return false;
        }
    }

    private function saveImageResource($image, $mime, $destination)
    {
        switch ($mime) {
            case 'image/jpeg':
                return imagejpeg($image, $destination, 90);
            case 'image/png':
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return imagepng($image, $destination, 6);
            case 'image/gif':
                return imagegif($image, $destination);
            case 'image/webp':
                return function_exists('imagewebp') ? imagewebp($image, $destination, 90) : false;
            default:
                return false;
        }
    }

    private function storeUploadedImage($tmpFile, $targetRelativeDir)
    {
        if (! is_uploaded_file($tmpFile)) {
            return [false, '非法上传'];
        }

        $binary = @file_get_contents($tmpFile);
        if ($binary === false || $binary === '') {
            return [false, '文件读取失败'];
        }

        if ($this->containsSuspiciousImagePayload($binary)) {
            return [false, '文件内容非法'];
        }

        $imageInfo = @getimagesize($tmpFile);
        if (empty($imageInfo['mime'])) {
            return [false, '文件类型不对'];
        }

        $allowedMimes = $this->allowedImageMimes();
        $mime = strtolower($imageInfo['mime']);
        if (! isset($allowedMimes[$mime])) {
            return [false, '文件类型不对'];
        }

        $image = @$this->createImageResource($mime, $tmpFile);
        if (! $image) {
            return [false, '图片解析失败'];
        }

        list($absoluteDir, $publicDir) = $this->buildUploadDirectory($targetRelativeDir);
        $fileName = $this->generateSafeImageName($allowedMimes[$mime]);
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        $saved = $this->saveImageResource($image, $mime, $absolutePath);
        imagedestroy($image);

        if (! $saved) {
            return [false, '图片保存失败'];
        }

        return [true, $publicDir . '/' . $fileName];
    }

    private function handleImageUpload(Request $request, $targetRelativeDir)
    {
        $file = $request->file('file');
        if (! $file || ! isset($_FILES['file'])) {
            return $this->error('请选择文件');
        }

        if (! empty($_FILES['file']['error'])) {
            return $this->error($_FILES['file']['error']);
        }

        if ($_FILES['file']['size'] > self::MAX_UPLOAD_SIZE) {
            return $this->error('文件大小超出');
        }

        list($success, $result) = $this->storeUploadedImage($_FILES['file']['tmp_name'], $targetRelativeDir);
        if (! $success) {
            return $this->error($result);
        }

        return $this->success($result);
    }

    private function storeBase64Image($base64ImageContent, $targetRelativeDir)
    {
        if (! preg_match('/^data:\s*image\/(\w+);base64,/', $base64ImageContent)) {
            return false;
        }

        $binary = base64_decode(preg_replace('/^data:\s*image\/(\w+);base64,/', '', $base64ImageContent), true);
        if ($binary === false || $binary === '') {
            return false;
        }

        if (strlen($binary) > self::MAX_UPLOAD_SIZE) {
            return false;
        }

        if ($this->containsSuspiciousImagePayload($binary)) {
            return false;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'img_');
        file_put_contents($tmpFile, $binary);
        list($success, $result) = $this->storeUploadedImageFromPath($tmpFile, $targetRelativeDir);
        @unlink($tmpFile);

        return $success ? url('') . $result : false;
    }

    private function storeUploadedImageFromPath($sourcePath, $targetRelativeDir)
    {
        $binary = @file_get_contents($sourcePath);
        if ($binary === false || $binary === '') {
            return [false, '文件读取失败'];
        }

        if ($this->containsSuspiciousImagePayload($binary)) {
            return [false, '文件内容非法'];
        }

        $imageInfo = @getimagesize($sourcePath);
        if (empty($imageInfo['mime'])) {
            return [false, '文件类型不对'];
        }

        $allowedMimes = $this->allowedImageMimes();
        $mime = strtolower($imageInfo['mime']);
        if (! isset($allowedMimes[$mime])) {
            return [false, '文件类型不对'];
        }

        $image = @$this->createImageResource($mime, $sourcePath);
        if (! $image) {
            return [false, '图片解析失败'];
        }

        list($absoluteDir, $publicDir) = $this->buildUploadDirectory($targetRelativeDir);
        $fileName = $this->generateSafeImageName($allowedMimes[$mime]);
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        $saved = $this->saveImageResource($image, $mime, $absolutePath);
        imagedestroy($image);

        if (! $saved) {
            return [false, '图片保存失败'];
        }

        return [true, $publicDir . '/' . $fileName];
    }

    public function falseData()
    {
        $limit = Input::get('limit', '12');
        $page = Input::get('page', '1');
        
        $old = date("Y-m-d", strtotime("-1 day"));
        $old_time = strtotime($old);
        $time = strtotime(date("Y-m-d"));
        
        $yesterday = FalseData::where('time', ">", $old_time)->where("time", "<", $time)->sum('price');
        $today = FalseData::where('time', ">", $time)->sum('price');
        
        $data = FalseData::orderBy('id', 'DESC')->paginate($limit);
        
        return $this->success(array(
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
            "yesterday" => $yesterday,
            "today" => $today
        ));
    }

    public function quotation()
    {
        $result = Market::limit(20)->get();
        return $this->success(array(
            "coin_list" => $result
        ));
    }

    public function historicalData()
    {
        $day = HistoricalData::where("type", "day")->orderBy('id', 'asc')->get();
        $week = HistoricalData::where("type", "week")->orderBy('id', 'asc')->get();
        $month = HistoricalData::where("type", "month")->orderBy('id', 'asc')->get();
        
        return $this->success(array(
            "day" => $day,
            "week" => $week,
            "month" => $month
        ));
    }

    public function quotationInfo()
    {
        $id = Input::get("id");
        if (empty($id))
            return $this->error("参数错误");
        
        // $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker/".$id."/");
        $coin_list = Market::find($id);
        
        // $coin_list = @json_decode($coin_list,true);
        
        return $this->success($coin_list);
    }

    public function dataGraph()
    {
        $data = Setting::getValueByKey("chart_data");
        if (empty($data))
            return $this->error("暂无数据");
        
        $data = json_decode($data, true);
        return $this->success(array(
            "data" => array(
                $data["time_one"],
                $data["time_two"],
                $data["time_three"],
                $data["time_four"],
                $data["time_five"],
                $data["time_six"],
                $data["time_seven"]
            ),
            "value" => array(
                $data["price_one"],
                $data["price_two"],
                $data["price_three"],
                $data["price_four"],
                $data["price_five"],
                $data["price_six"],
                $data["price_seven"]
            ),
            "all_data" => $data
        ));
    }

    public function index()
    {
        $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker?limit=10");
        $coin_list = @json_decode($coin_list, true);
        
        if (! empty($coin_list["data"])) {
            foreach ($coin_list["data"] as &$d) {
                if ($d["total_supply"] > 10000) {
                    $d["total_supply"] = substr($d["total_supply"], 0, - 4) . "万";
                }
            }
        }
        return $this->success(array(
            "coin_list" => $coin_list["data"]
        ));
    }
    
    //上传NFT文件
    public function uploadNFT(Request $request)
    {
        return $this->handleImageUpload($request, 'upload_nft');
    }
    
    public function upload(Request $request)
    {
        return $this->handleImageUpload($request, 'upload');
    }
    
    public function upload_new(Request $request)
    {
        return $this->handleImageUpload($request, 'upload');
    }

    // ios 文件上传
    public function upload2(Request $request)
    {
        $base64_image_content = $request->input('base64_file', '');
        $res = self::base64_image_content($base64_image_content);
        if (! $res) {
            return $this->error('上传失败');
        }
        
        return $this->success($res);
    }

    /* base64格式编码转换为图片并保存对应文件夹 */
    public function base64_image_content($base64_image_content)
    {
        return $this->storeBase64Image($base64_image_content, 'upload/' . date('Ymd'));
    }

    public function getNode(\Illuminate\Http\Request $request)
    {
        $user_id = $request->get('user_id', 0);
        $show_message["real_teamnumber"] = Users::find($user_id)->real_teamnumber;
        $show_message["top_upnumber"] = Users::find($user_id)->top_upnumber;
        $show_message["today_real_teamnumber"] = Users::find($user_id)->today_real_teamnumber;
        $account_number = $request->get('account_number', null);
        if (! empty($account_number)) {
            $user_id_search = Users::where('account_number', $account_number)->first();
            if (! empty($user_id_search)) {
                $user_id = $user_id_search->id;
            } else {
                $user_id = 0;
            }
        }
        // if (empty($user_id)){
        $users = Users::where('parent_id', $user_id)->get();
        $results = array();
        foreach ($users as $key => $user) {
            $results[$key]['name'] = $user->account_number;
            $results[$key]['id'] = $user->id;
            $results[$key]['parent_id'] = $user->parent_id;
        }
        $data["show_message"] = $show_message;
        $data["results"] = $results;
        return $this->success($data);
    }

    public function getVersion()
    {
        $version = Setting::getValueByKey('version', '1.0');
        return $this->success($version);
    }

    public function getBanks()
    {
        $result = Bank::all();
        return $this->success($result);
    }

    public function language(Request $request)
    {
        $lang = $request->get('lang', 'zh');
        session()->put('lang', $lang);
        return $this->success($lang);
    }
    
     public function getMenu()
    {
        $menu = Menu::where('show', 1)->orderBy('sort','asc')->get();
        return $this->success($menu);
    }

    public function getSiteConfig(Request $request) {
        // $user_id   = Users::getUserId(); 
        $user_id = $request->get('user_id', 1);
        $model = Setting::whereIn('key', ['site_name', 'site_logo','site_pc_logo', 'down_logo','open_url','sharar_radio','reverse_radio','email_radio'
            ,'zxkf_radio','zxkf_url','telegram_url','telegram_radio','skype_radio','skype_url','whatsApp_radio','DAPP_DEMO','H5_DEMO','example_radio'
            ,'whatsApp_url','line_radio','line_url','jie_radio','jie_url','hk_radio','hk_url','bank_flag','image_server_url','tk_radio','yzm_radio','ios_apk_download_url','apk_download_url','mobile_register','web_mail','facebook','twitter','facebook_url','twitter_url','password_radio'
        ])->get();
        $settings = [];
        foreach ($model as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        
        $settings['code'] = Users::find($user_id)['extension_code'];
        return $this->success($settings);
    }
    public function US(){
        return $this->success('success'); 
        //$this->success(json_decode(file_get_contents("https://www.mycurrency.net/US.json"),true));
    }
    // public function getlanguage(\Request $request)
    // {
    // $lang=session()->get('lang');
    // return $this->success($lang);
    // }
}
?>
