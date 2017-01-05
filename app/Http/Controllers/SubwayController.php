<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/8
 * Time: 20:29
 */

namespace App\Http\Controllers;


use App\Model\SubwayStation;

class SubwayController extends BaseController{

    public function getIndex(){


        return view('subway.index',compact('subways'));
    }
    public function getSubway(){
        $subways = SubwayStation::orderBy('listorder','desc')->with(['store'=>function($q){
        }])->get()->toJson();
        return $subways;

    }
} 