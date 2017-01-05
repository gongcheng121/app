<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Admin\AdminBaseController;
use App\Model\DriftBottle;
use App\Repositories\DriftBottleRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DriftBottleController extends AdminBaseController
{


    public $repository;
    /**
     * DriftBottleController constructor.
     */
    public function __construct(DriftBottleRepositoryEloquent $repository)
    {

        parent::__construct();
        $this->repository=$repository;
    }


    
    public function index(){
        $list = $this->repository->orderBy('id','desc')->with('wechatMember')->paginate(100);
        if (request()->wantsJson()) {

            return response()->json([
                'data' => $list,
            ]);
        }

        $title='审核';
        return view('admin.activity.drift.index',compact('title'));
    }

    public function update(Request $request,$id){
        $data=  $request->data;
        $bottle = $this->repository->find($id);
        $bottle->status=($bottle->status) ? 0 :1;
        $bottle->save();
        return response()->json($bottle);
    }

    public function destroy($id){
         $deleted =$this->repository->delete($id);
        if (request()->wantsJson()) {

            return response()->json([
                'message' => 'DriftBottle deleted.',
                'deleted' => $deleted,
            ]);
        }
    }
}
