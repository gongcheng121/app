<?php namespace App\Http\Controllers\Admin\Activity;
use App\Commands\ImageResize;
use App\Http\Controllers\Admin\AdminBaseController;
use App\Model\VideoPoll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;

/**
 * Created by PhpStorm.
 * User: koala
 * Date: 02/02/16
 * Time: 下午 04:05
 */
class ActivityVideoController extends AdminBaseController
{
    public function __construct(){
        parent::__construct();
    }

    public function getIndex(VideoPoll $videoPoll){
        $videoList = $videoPoll->orderBy('id','DESC')->orderBy('listorder','DESC')->paginate(155);


        return view('admin.activity.video.index',compact('videoList'));
    }
    public function getAdd(){
        return view('admin.activity.video.add');
    }

    public function postAdd(Request $request,VideoPoll $videoPoll){
        $video = $videoPoll->create($request->all());
        $path = 'upload/videopoll/'.date('ymd').'/original/';
        $thumb_path = 'upload/videopoll/'.date('ymd').'/thumb/';
        $type = $request->file('img')->guessExtension();

        $name = md5($video->id).".".$type;
        $request->file('img')->move($path,$name);
        $video->image =$path.$name;
        $video->save();

        if(!file_exists($thumb_path)){
            mkdir($thumb_path);
        };
        Queue::push(new ImageResize(['obj'=>$video,'original_path'=>asset($path.$name),'thumb_path'=>public_path($thumb_path.$name),'thumb'=>$thumb_path.$name]));
        return redirect('admin/activity/video/index');
    }

}