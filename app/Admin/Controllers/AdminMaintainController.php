<?php
#app/Http/Admin/Controllers/AdminMaintainController.php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminStore;
use App\Models\ShopLanguage;
use Illuminate\Http\Request;
use Validator;

class AdminMaintainController extends Controller
{
    public $languages;

    public function __construct()
    {
        $this->languages = ShopLanguage::getList();

    }
    
    public function index()
    {
        $languages = ShopLanguage::getCodeActive();
        $data = [
            'title' => trans('store_info.maintain_manager'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',
        ];

        $obj = (new AdminStore)->with('descriptions')->first();
        $data['obj'] = $obj;
        $data['languages'] = $languages;
        return view('admin.screen.maintain')
            ->with($data);
    }

/**
 * Form edit
 */
    public function edit()
    {
        $maintain = AdminStore::find(1);
        if ($maintain === null) {
            return 'no data';
        }
        $data = [
            'title' => trans('store_info.maintain_manager'),
            'subTitle' => '',
            'title_description' => '',
            'icon' => 'fa fa-pencil-square-o',
            'languages' => $this->languages,
            'maintain' => $maintain,
            'url_action' => route('admin_maintain.edit'),
        ];
        return view('admin.screen.maintain_edit')
            ->with($data);
    }

/**
 * update status
 */
    public function postEdit()
    {
        $data = request()->all();
        $dataOrigin = request()->all();
        $validator = Validator::make($dataOrigin, [
            'descriptions.*.maintain' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
//Edit

        $obj = AdminStore::find(1);
        $obj->descriptions()->delete();
        $dataDes = [];
        foreach ($data['descriptions'] as $code => $row) {
            $dataDes[] = [
                'shop_news_id' => $id,
                'lang' => $code,
                'title' => $row['title'],
                'maintain' => $row['maintain'],
                'description' => $row['description'],
                'content' => $row['content'],
            ];
        }
        ShopNewsDescription::insert($dataDes);

//
        return redirect()->route('admin_banner.index')->with('success', trans('banner.admin.edit_success'));

    }
}
