<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 下午 5:42
 */

namespace App\Http\Controllers;


use App\Http\Controllers\Api\QiyeController;
use Illuminate\Http\Request;

class QrwzController extends Controller{

    public function qrwz($q){
        if($q =='jymx2015'){
//            return response('This page is not found','404');
            return redirect('http://zt.iyaxin.com/2015/node_118784.htm');
        }
    }
} 