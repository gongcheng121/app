<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/12 0012
 * Time: 上午 11:56
 */

namespace App\Http\Controllers\Admin;

use App\Model\VideoInfo;
class VideoController extends AdminBaseController{
    public function getIndex(){
        $videos = VideoInfo::paginate(10);

        return view('admin.video.index',compact('videos'));
    }
} 