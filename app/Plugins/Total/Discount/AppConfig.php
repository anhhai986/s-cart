<?php
#App\Plugins\Total\Discount\AppConfig.php
namespace App\Plugins\Total\Discount;

use App\Plugins\Total\Discount\Models\PluginModel;
use App\Plugins\Total\Discount\Controllers\FrontController;
use App\Models\AdminConfig;
use App\Plugins\ConfigDefault;
class AppConfig extends ConfigDefault
{
    public $configGroup = 'Plugins';
    public $configCode = 'Total';
    public $configKey = 'Discount';
    public $pathPlugin;
    
    public function __construct()
    {
        $this->pathPlugin = $this->configGroup . '/' . $this->configCode . '/' . $this->configKey;
        $this->title = trans($this->pathPlugin.'::lang.title');
        $this->image = '';
        $this->separator = false;
        $this->suffix = false;
        $this->prefix = false;
        $this->length = 8;
        $this->mask = '****-****';
        $this->version = '4.0';
        $this->auth = 'Naruto';
        $this->link = 'https://s-cart.org';

    }

    public function install()
    {
        $return = ['error' => 0, 'msg' => ''];
        $check = AdminConfig::where('key', $this->configKey)->first();
        if ($check) {
            $return = ['error' => 1, 'msg' => 'Module exist'];
        } else {
            $process = AdminConfig::insert(
                [
                    'code' => $this->configCode,
                    'key' => $this->configKey,
                    'group' => $this->configGroup,
                    'sort' => 0,
                    'value' => self::ON, //Enable extension
                    'detail' => $this->pathPlugin.'::lang.title',
                ]
            );
            if (!$process) {
                $return = ['error' => 1, 'msg' => 'Error when install'];
            } else {
                $return = (new PluginModel)->installExtension();
            }
        }

        return $return;
    }

    public function uninstall()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)->where('key', $this->configKey)->delete();
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error when uninstall'];
        }
        (new PluginModel)->uninstallExtension();
        return $return;
    }
    public function enable()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)->where('key', $this->configKey)->update(['value' => self::ON]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error enable'];
        }
        return $return;
    }
    public function disable()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)
            ->where('key', $this->configKey)
            ->update(['value' => self::OFF]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error disable'];
        }
        return $return;
    }

    public function config()
    {
        return redirect()->route('admin_discount.index');
    }

    public function getData()
    {
        $uID = auth()->user()->id ?? 0;
        $arrData = [
            'title' => $this->title,
            'code' => $this->configCode,
            'key' => $this->configKey,
            'image' => $this->image,
            'permission' => self::ALLOW,
            'value' => 0,
            'version' => $this->version,
            'auth' => $this->auth,
            'link' => $this->link,
            'pathPlugin' => $this->pathPlugin
        ];

        $totalMethod = session('totalMethod',[]);
        $discount = $totalMethod['Discount']??'';

        $check = json_decode((new FrontController)->check($discount, $uID), true);
        if (!empty($discount) && !$check['error']) {
            $arrType = [
                'point' => 'Point',
                'percent' => '%',
            ];
            $subtotal = \Cart::subtotal();
            $value = ($check['content']['type'] == 'percent') ? floor($subtotal * $check['content']['reward'] / 100) : $check['content']['reward'];
            $arrData = array(
                'title' => '<b>' . $this->title . ':</b> ' . $discount . '',
                'code' => $this->configCode,
                'key' => $this->configKey,
                'image' => $this->image,
                'permission' => self::ALLOW,
                'value' => ($value > $subtotal) ? -$subtotal : -$value,
                'version' => $this->version,
                'auth' => $this->auth,
                'link' => $this->link,
                'pathPlugin' => $this->pathPlugin,
            );
        }
        return $arrData;
    }

    /**
     * Process after order success
     *
     * @param   [array]  $data  
     *
     */
    public function endApp($data = []) {
        $orderID = $data['orderID'] ?? '';
        $code = $data['code'] ?? '';
        $uID = auth()->user()->id ?? 0;
        $msg = 'Order #'.$orderID;
        return (new FrontController)->apply($code, $uID, $msg);
    }
}
