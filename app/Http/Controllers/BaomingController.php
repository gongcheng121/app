<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/6
 * Time: 17:24
 */

namespace App\Http\Controllers;



use App\Autoform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaomingController extends  Controller{

    public function __construct(){

        /* $this->files = new \Illuminate\Filesystem\Filesystem;
         foreach ($this->files->files(storage_path().'/framework/views') as $file)
         {
             $this->files->delete($file);
         }*/
    }
    public function getIndex(){

        return view('baoming.index');
    }

    public function postCreate(Request $request){
        $v = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'mobile' => 'required',
//            'chexin' => 'required',
            'jinxiaoshang' => 'required',
        ]);

//        if ($v->fails())
//        {
//            return redirect()->back()->withErrors($v->errors());
//        }
//        $auto = Autoform::create($request->all());
        $autoid=rand(111111,999999);
        $code_array = ['A2016-623-2873-31-69185','A2016-623-2873-31-69186'];
//        $code_array = ['A0000-000-000-00-00000'];
        $smart_code=array_rand($code_array,1);
        $smart_code='A2016-623-2873-31-69185';
//        $smart_code='A0000-000-000-00-00000';
        $dealercode =$request->jinxiaoshang;
        $mobile=$request->mobile;
        $model='';
        $province ='9';
        $city ='85';
        $name =$request->name;
//        $series =$request->chexin;
        $create_time =date ('Y-m-d H: i: s');
        $arr = array (
            'AuthenticatdKey' => $smart_code,
            'RequestObject'   =>
                array (
                    array (
                        'MEDIA_LEAD_ID'=>$autoid,
                        'FK_DEALER_ID'=>$dealercode,
                        'CUSTOMER_NAME'=>$name,
                        'MOBILE'=>$mobile,
                        'PROVINCE'=>$province,
                        'CITY'=>$city,
//                        'SERIES'=>$series,
                        'MODEL'=>$model ,
                        'ORDER_TIME'=>$create_time,
                        'COMMENTS'=>'',
                        'OPERATE_TYPE'=>'0',
                        'OPERATE_TIME'=>$create_time,
                        'STATUS'=>'0',
                        'SMART_CODE'=>$smart_code
                    )
                )
        );
        $Jsondata = json_encode($arr);
        $data = array('inputParam'=>$Jsondata);
        $soap = new \SoapClient('http://202.96.191.233:8080/MediaInterface/BaseInfoService.svc?wsdl');
        $result = $soap -> SyncSaleClues($data);
//        $auto->log = $result->SyncSaleCluesResult;
//        var_dump($result);
        echo "<script type='text/javascript'>alert('报名成功')</script>";
        exit;
//        $auto->update();
        return redirect('auto')->with('submit',1);
    }

} 