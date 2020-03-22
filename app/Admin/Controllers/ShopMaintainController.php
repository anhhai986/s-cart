<?php
#app/Http/Admin/Controllers/ShopMaintainController.php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminStore;
use App\Models\ShopLanguage;
use Illuminate\Http\Request;
use Validator;

class ShopMaintainController extends Controller
{
    protected $arrTarget;
    protected $dataType;

    public function __construct()
    {
        $this->arrTarget = ['_blank' => '_blank', '_self' => '_self'];
        $this->dataType = ['0' => 'Banner', '1' => 'Background'];
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
 * Form create new order in admin
 * @return [type] [description]
 */
    public function create()
    {
        $data = [
            'title' => trans('banner.admin.add_new_title'),
            'subTitle' => '',
            'title_description' => trans('banner.admin.add_new_des'),
            'icon' => 'fa fa-plus',
            'banner' => [],
            'arrTarget' => $this->arrTarget,
            'dataType' => $this->dataType,
            'url_action' => route('admin_banner.create'),
        ];
        return view('admin.screen.banner')
            ->with($data);
    }

/**
 * Post create new order in admin
 * @return [type] [description]
 */
    public function postCreate()
    {
        $data = request()->all();
        $dataOrigin = request()->all();
        $validator = Validator::make($dataOrigin, [
            'sort' => 'numeric|min:0',
            'email' => 'email|nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataInsert = [
            'image' => $data['image'],
            'url' => $data['url'],
            'html' => $data['html'],
            'type' => $data['type'] ?? 0,
            'target' => $data['target'],
            'status' => empty($data['status']) ? 0 : 1,
            'sort' => (int) $data['sort'],
        ];
        ShopBanner::create($dataInsert);

        return redirect()->route('admin_banner.index')->with('success', trans('banner.admin.create_success'));

    }

/**
 * Form edit
 */
    public function edit($id)
    {
        $banner = ShopBanner::find($id);
        if ($banner === null) {
            return 'no data';
        }
        $data = [
            'title' => trans('banner.admin.edit'),
            'subTitle' => '',
            'title_description' => '',
            'icon' => 'fa fa-pencil-square-o',
            'arrTarget' => $this->arrTarget,
            'dataType' => $this->dataType,
            'banner' => $banner,
            'url_action' => route('admin_banner.edit', ['id' => $banner['id']]),
        ];
        return view('admin.screen.banner')
            ->with($data);
    }

/**
 * update status
 */
    public function postEdit($id)
    {
        $data = request()->all();
        $dataOrigin = request()->all();
        $validator = Validator::make($dataOrigin, [
            'sort' => 'numeric|min:0',
            'email' => 'email|nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
//Edit

        $dataUpdate = [
            'image' => $data['image'],
            'url' => $data['url'],
            'html' => $data['html'],
            'type' => $data['type'] ?? 0,
            'target' => $data['target'],
            'status' => empty($data['status']) ? 0 : 1,
            'sort' => (int) $data['sort'],

        ];
        $obj = ShopBanner::find($id);
        $obj->update($dataUpdate);

//
        return redirect()->route('admin_banner.index')->with('success', trans('banner.admin.edit_success'));

    }

/*
Delete list item
Need mothod destroy to boot deleting in model
 */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            ShopBanner::destroy($arrID);
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

}
