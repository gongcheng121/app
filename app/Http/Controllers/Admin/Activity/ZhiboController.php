<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/6/12
 * Time: 11:38
 */

namespace App\Http\Controllers\Admin\Activity;


use App\Http\Controllers\Admin\AdminBaseController;
use App\Model\ZhiboInfo;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Ixudra\Curl\Facades\Curl;
use zgldh\UploadManager\UploadManager;

class ZhiboController extends AdminBaseController
{


    private $model;
    /**
     * ZhiboController constructor.
     */
    public function __construct(ZhiboInfo $zhiboInfo)
    {
        parent::__construct();
        $this->model=$zhiboInfo;
    }


    public function getIndex(){
        return view('admin.activity.zhibo.index');
    }

    public function anyUpload(Request $request){

        $file = $request->file('wangEditorH5File');
        $manager = UploadManager::getInstance();
        $upload = $manager->upload($file);
        $upload->save();

        $img = Image::make($upload->path);

        $name =$img->basename ;
        $width = $img->width() * .2;
        $height = $img->height() * .2;
        $img->resize($width, $height);
        $img->save($img->dirname . '/thumb_2_' . $name);

        $width = $img->width() * .5;
        $height = $img->height() * .5;
        $img->resize($width, $height);
        $img->save($img->dirname . '/thumb_5_' . $name);

        return "http://app.iyaxin.com/".$upload->path;
    }

    public function postStore(Request $request){

        $result = $this->model->create($request->all());
        $response = Curl::to('http://220.171.90.234:9033/send')
            ->withData(['room'=>'zhibo'.$request->zid,'text'=>$result->toJson()])
            ->post();
        return ['code'=>1,'msg'=>'添加成功','data'=>$result];
    }
}