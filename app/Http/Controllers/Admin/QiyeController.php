<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 上午 11:20
 */

namespace App\Http\Controllers\Admin;


use App\Commands\ImageResize;
use App\Commands\QiyePollLog;
use App\Model\QiyeCategory;
use App\Model\QiyeItem;
use App\Model\QiyeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class QiyeController extends AdminBaseController{


    public function getType(Request $request,QiyeType $qiyeType){
        $types = $qiyeType->orderBy('listorder','DESC')->with('Category')->paginate(25);
        return view('admin.qiye.type_index',compact('types'));
    }

    public function getTypeadd(QiyeType $qiyeType,Request $request){
        return view('admin.qiye.type_add');
    }

    public function getTypeedit($id,Request $request,QiyeType $qiyeType){
        $type = $qiyeType->find($id);
        return view('admin.qiye.type_edit',compact('type'));
    }

    public function postType(Request $request){
        Cache::forget('categorysAll');
        $listorder = $request->listorder;
        $id = [];
        foreach($listorder as $k=>$v){
            array_push($id,$k);
        }

        foreach(QiyeType::find($id) as $k=>$station){
            $station->listorder = $listorder[$station->id];
            $station->update();
        }
        return redirect()->back();
    }
    public function postTypeadd(Request $request,QiyeType $qiyeType){
        $qiyeType->create($request->all());
        return redirect('admin/qiye/type');
    }

    public function postTypeedit($id,Request $request,QiyeType $qiyeType){
        $qiyeTypeModel  = $qiyeType->find($id);
        $qiyeTypeModel->update($request->all());
        return redirect('admin/qiye/type');
    }


    public function  getItem(Request $request,QiyeItem $qiyeItem){
        //企业列表
       $qiyeItemQ = $qiyeItem->orderBy('id','DESC')->orderBy('listorder','DESC')->with('Category')->with('Type')->with('count');
        if($request->type){
            $qiyeItemQ->where('type_id',$request->type);
        }
        if($request->cat_id){
            $qiyeItemQ->where('cat_id',$request->cat_id);
        }
        $qiyeItems = $qiyeItemQ->paginate(25);
        return view('admin.qiye.item_index',compact('qiyeItems','request'));
    }

    public function getItemadd(QiyeCategory $qiyeCategory,Request $request){
//        $categorys = $qiyeCategory->all();
        return view('admin.qiye.item_add',compact('categorys'));
    }

    public function postItemadd(Request $request,QiyeItem $qiyeItem){
        $qiye = $qiyeItem->create($request->all());
        $path = 'upload/qiye/'.date('ymd').'/original/';
        $thumb_path = 'upload/qiye/'.date('ymd').'/thumb/';
        $type = $request->file('img')->guessExtension();

        $name = md5($qiye->type_id."_".$qiye->id).".".$type;
        $request->file('img')->move($path,$name);
        $qiye->image =$path.$name;
        $qiye->save();
        if(!file_exists($thumb_path)){
            mkdir($thumb_path);
        };
        Queue::push(new ImageResize(['obj'=>$qiye,'original_path'=>asset($path.$name),'thumb_path'=>public_path($thumb_path.$name),'thumb'=>$thumb_path.$name]));
        return redirect('admin/qiye/item');
    }

    public function getApitype(){
        $result = QiyeType::with('Category')->get()->toArray();
        foreach($result as $k=>$v){
            $data[$v['id']]['name'] = $v['type_name'];
            foreach($v['category'] as $key=>$val){
                $data[$v['id']]['cell'][$val['id']]['name'] = $val['cat_name'];
            }
        }
        return response()->json($data);
    }

    public function postItemedit($id,Request $request,QiyeItem $qiyeItem){
        $qiye = $qiyeItem->find($id);
        $qiye->update($request->all());
        if($request->file('img')){
            $path = 'upload/qiye/'.date('ymd').'/original/';
            $thumb_path = 'upload/qiye/'.date('ymd').'/thumb/';
            $type = $request->file('img')->guessExtension();
            $name = md5($qiye->type_id."_".$qiye->id).".".$type;
            $request->file('img')->move($path,$name);
            $qiye->image =$path.$name;
            $qiye->save();
            if(!file_exists($thumb_path)){
                Storage::disk('public')->makeDirectory($thumb_path);
            };
            Queue::push(new ImageResize(['obj'=>$qiye,'original_path'=>asset($path.$name),'thumb_path'=>public_path($thumb_path.$name),'thumb'=>$thumb_path.$name]));
        }

        return redirect('admin/qiye/item');

    }

    public function postItem(Request $request){
        Cache::forget('categorys');
        $listorder = $request->listorder;
        $id = [];
        foreach($listorder as $k=>$v){
            array_push($id,$k);
        }

        foreach(QiyeItem::find($id) as $k=>$item){
            $item->listorder = $listorder[$item->id];
            $item->update();
        }
        return redirect()->back();
    }

    public function getItemdelete($id){
        $qiye = QiyeItem::find($id);
        if($qiye->image){
            Storage::disk('public')->delete($qiye->image);
            Storage::disk('public')->delete($qiye->thumb);
        }
        if($qiye->delete()){
            return redirect()->back();
        };
        return;
    }

    public function getItemedit($id,QiyeItem $qiyeItem,QiyeCategory $qiyeCategory){
            $qiyeItemInfo = $qiyeItem->with('Category')->find($id);
            $categorys = $qiyeCategory->all();
        return view('admin.qiye.item_edit',compact('qiyeItemInfo','categorys'));
    }

    /**
     * 企业分类类型
     */
    public function getCategory(Request $request){
            $qiyeCategory = QiyeCategory::orderBy('listorder','DESC')->with('Type');
            if($request->type){
                $qiyeCategory->where('type_id',$request->type);
            }
            $categorys = $qiyeCategory->paginate(15);
            return view('admin.qiye.category_index',
                compact('categorys','request'));
    }

    public function getCategorydelete($id,Request $request){
        if(QiyeCategory::find($id)->delete()){
            return redirect()->back();
        };
        return;
    }
    public function getCategoryadd(Request $request){
        return view('admin.qiye.category_add');
    }
    public function getCategoryedit($id,Request $request,QiyeCategory $qiyeCategory){
        $category = $qiyeCategory->find($id);
        return view('admin.qiye.category_edit',compact('category'));
    }

    public function postCategory(Request $request){
        Cache::forget('categorys');
        $listorder = $request->listorder;
        $id = [];
        foreach($listorder as $k=>$v){
            array_push($id,$k);
        }

        foreach(QiyeCategory::find($id) as $k=>$station){
            $station->listorder = $listorder[$station->id];
            $station->update();
        }
        return redirect()->back();
    }
    public function postCategoryadd(Request $request,QiyeCategory $qiyeCategory){
        $qiyeCategory->create($request->all());
        return redirect('admin/qiye/category');
    }

    public function postCategoryedit($id,Request $request,QiyeCategory $qiyeCategory){
        $category  = $qiyeCategory->find($id);
        $category->update($request->all());
        return redirect('admin/qiye/category');
    }

    public function getItemlist($id,Request $request,QiyeCategory $qiyeCategory){
        //企业列表

        $category   = $qiyeCategory->find($id)->with('Item')->first()->toArray();
        return view('admin.qiye/item_list',compact('category'));
    }

    public function postEditcount(Request $request,QiyeItem $qiyeItem){
        $count = $request->value;
        $id = $request->id;
        $qiyeItem->find($id)->count()->update(['count'=>$count]);
        return $count;
    }
}