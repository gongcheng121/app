<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/18 0018
 * Time: 上午 10:07
 */

namespace App\Http\Controllers;


class NewsController extends BaseController{


    function __construct()
    {
        parent::__construct();
    }
    public function getIndex(){

        $return =['event'=>0,'msg'=>"success",'objList'=>[['id'=>1,'title'=>'title']]];
        return response()->json($return);
    }
}