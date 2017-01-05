<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/25 0025
 * Time: 下午 4:52
 */


namespace App\Http\Controllers\Admin;

use App\Model\PackOrder;
use App\Model\WechatKeyVerify;
use Illuminate\Http\Request;

class KeyController extends AdminBaseController{

    function __construct()
    {
        parent::__construct();
    }

    public function getIndex(Request $request , WechatKeyVerify $wechatKeyVerify){
        $wechatKeyVerifys = $wechatKeyVerify->orderBy('id','DESC')->with('Member')->paginate(25);
        return view('admin.key_verify',compact('wechatKeyVerifys'));
    }

    public function getAdd(){
        $data  = [
            'key'=>str_random(5),
            'password'=>substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 10), 1))), 0, 6),
            'status'=>0
        ];
        WechatKeyVerify::create($data);
        return redirect()->back();
    }
}