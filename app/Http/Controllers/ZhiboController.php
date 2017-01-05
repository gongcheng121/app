<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/6/12
 * Time: 10:39
 */

namespace App\Http\Controllers;


use App\Model\ZhiboInfo;
use Illuminate\Http\Request;

class ZhiboController extends BaseController
{
    private $model;
    public function __construct(ZhiboInfo $zhiboInfo)
    {
        parent::__construct();
        $this->model=$zhiboInfo;
    }

    public function anyIndex(Request $request){
        $zid = 2;
        if($request->ajax()){
            $list = $this->model->where('zid',$zid)->orderBy('id','desc')->paginate(50);
            return $list;
        }
        $list = $this->model->where('zid',$zid)->orderBy('id','desc')->paginate(100);
       return view('activity.zhibo.index',compact('list','zid'));
    }

}