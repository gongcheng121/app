<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/8
 * Time: 15:06
 */

namespace App\Http\Controllers\Admin;


use App\Http\Requests\StationEditRequest;
use App\Http\Requests\StationRequest;
use App\Http\Requests\StoreEditRequest;
use App\Http\Requests\StoreRequest;
use App\Model\SubwayStation;
use App\Model\SubwayStore;
use Illuminate\Http\Request;

class SubwayController extends AdminBaseController{

//地铁站管理首页
    public function getStation(){
        $stations  = SubwayStation::orderBy('listorder','desc')->paginate(15);
        return view('admin.subway.station',compact('stations'));
    }
    public function postStation(Request $request){
        $listorder = $request->listorder;
        $id = [];
        foreach($listorder as $k=>$v){
            array_push($id,$k);
        }

        foreach(SubwayStation::find($id) as $k=>$station){
            $station->listorder = $listorder[$station->id];
            $station->update();
        }
        return redirect()->back();
//        dd($data);
    }
    public function getStationadd(){
        return view('admin.subway.stationadd');
    }
    public function getStationedit($id){

        $station = SubwayStation::find($id);
        return view('admin.subway.stationedit',compact('station'));
    }
    public function getStationdelete($id){
        $station  = SubwayStation::find($id);
        if($station){
            return redirect()->back();
        };
    }

    public function postStationadd(StationRequest $request){
          $station_model = new SubwayStation();
          $request->icon=  '';
          $station =  $station_model->create($request->all());
          $path = 'upload/subway/station/'.date('ymd').'/';
          $type = $request->file('icon')->guessExtension();
          $name = $station->id.".".$type;
          $request->file('icon')->move($path,$name);
          $station->icon =$path.$name;
          if($station->save()){
              return redirect('admin/subway/station');
          }
    }

    public function postStationedit(StationEditRequest $request){
        $station_model = new SubwayStation();
        $station = $station_model->find($request->id);
        $icon = $request->icon;
        if($request->hasFile('icon')){
            $path = 'upload/subway/station/'.date('ymd').'/';
            $type = $request->file('icon')->guessExtension();
            $name = $request->id.".".$type;
            $request->file('icon')->move($path,$name);
            $station->icon =$path.$name;
            $icon = $path.$name;
        }

        if($station->update($request->all())){
            $station->icon = $icon;
            $station->save();
            return redirect()->back();
        }
    }

//商家管理首页
    public function getStore(){
        $stores = SubwayStore::with('station')->orderBy('id','desc')->paginate(15);
        return view('admin.subway.store',compact('stores'));
    }
    public function postStore(Request $request){
        $listorder = $request->listorder;
        $id = [];
        foreach($listorder as $k=>$v){
            array_push($id,$k);
        }

        foreach(SubwayStore::find($id) as $k=>$station){
            $station->listorder = $listorder[$station->id];
            $station->update();
        }
        return redirect()->back();
//        dd($data);
    }
    public function getStoreadd(){
        $stations = SubwayStation::all();
        return view('admin.subway.storeadd',compact('stations'));
    }
    public function getStoreedit($id){
        $store = SubwayStore::find($id);
        $stations = SubwayStation::all();
        return view('admin.subway.storeedit',compact('store','stations'));
    }
    public function getStoredelete($id){
        if(SubwayStore::find($id)->delete()){
            return redirect()->back();
        };
    }

    public function postStoreadd(StoreRequest $request){
        $store_model = new SubwayStore();
        $request->icon=  '';
        $store =  $store_model->create($request->all());
        $path = 'upload/subway/store/'.date('ymd').'/';
        $type = $request->file('img')->guessExtension();
        $name = $store->id.".".$type;
        $request->file('img')->move($path,$name);
        $store->img =$path.$name;
        if($store->save()){
            return redirect('admin/subway/store');
        }

    }
    public function postStoreedit(StoreEditRequest $request){

        $store_model = new SubwayStore();
        $store = $store_model->find($request->id);
        $img = $request->img;
        if($request->hasFile('img')){
            $path = 'upload/subway/store/'.date('ymd').'/';
            $type = $request->file('img')->guessExtension();
            $name = $request->id.".".$type;
            $request->file('img')->move($path,$name);
            $store->img =$path.$name;
            $img = $path.$name;
        }

        if($store->update($request->all())){
            $store->img = $img;
            $store->save();
            return redirect()->back();
        }
    }

    public function getStorelocation($id){
        $station = SubwayStation::with('store')->find($id);
        return view('admin.subway.location',compact('station'));
    }

    public function postStorelocation(Request $request){
        $store = SubwayStore::find($request->id);
        $return['error']= 1;
        $return['msg']='some thing wrong';
        if($store->update($request->all())){
            $return['error']= 0;
            $return['msg']='ok';
        }
        return json_encode($return);
    }




} 