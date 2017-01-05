<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 下午 1:10
 */
namespace App\Http\Controllers\Api;
use App\Commands\ImageResize;
use App\Commands\QiyePollLog;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Model\LuckMoney;
use App\Model\QiyeCategory;
use App\Model\QiyeItem;
use App\Model\QiyeItemPoll;
use App\Model\QiyeItemPollLog;
use App\Model\QiyeType;
use App\Model\QiyeUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class QiyeController extends BaseController{
    public function getIndex(Request $request){
        dd(time());
        $h = Carbon::now()->hour;
        $m = (Carbon::now()->minute-30 <0 )? 0 : 1;//时段

        $k = $h."_".$m;
        $order_id = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 10), 1))), 0, 9);
dump($k);
//        dd($order_id);
        $cache_key = 'Count_'.$k;
        $count = Cache::get($cache_key);
        dump($count);
        $redPackAmount = LuckMoney::where('type',$k)->count();
//        $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
//        Cache::put('Count_'.$k,0,$expiresAt);
        dd($redPackAmount);

        $categorys = Cache::get('categorysAll',function(){
            $categorys = QiyeType::orderBy('listorder','DESC')->with(['Category.Item'=>function($q){
                $q->orderBy('listorder','DESC');
            }])->get()->toArray();
            $expiresAt = Carbon::now()->addMinutes(10)->diffInMinutes();
            Cache::add('categorysAll',$categorys,$expiresAt);
            return $categorys;
        });
        $result=['status'=>1,'data'=>$categorys,'msg'=>'成功'];
        Storage::disk('public')->put('/api/qiye.js',json_encode($result));
        return response()->jsonp($request->callback,$result);
    }

    public function anyApipoll(Request $request){

        $id = $request->id;
        $num = $request->num;
        $qiyepoll = QiyeItemPoll::find($id);
        if($qiyepoll) $qiyepoll->increment('count',$num);
        return $qiyepoll->count;
    }

    public function anyPoll($id,Request $request){
        if(!Session::has('user_info')){
            $data = ['status'=>2,'msg'=>'请填写您的信息','data'=>''];
            return response()->jsonp($request->call_back,$data);
        }


        $cach_key = md5($request->getClientIp())."poll".$id;
        $can = Cache::has($cach_key)? (Cache::get($cach_key)>=5 ? false : true) : true;
        if($can){
            $poll = QiyeItemPoll::firstOrCreate(['id'=>$id]);
            $num = rand(30,50);
            while($num%2==0){
                $num = rand(31,51);
            }
            $poll->increment('count',$num);
            $data['item_id']= $id;
            $data['ip'] = $request->getClientIp();
            Queue::push(new QiyePollLog($data));
            $expiresAt = Carbon::now()->addDay(1);
            if(!Cache::has($cach_key)){
                Cache::add($cach_key,0,$expiresAt);
            }
            Cache::increment($cach_key, 1);
            $data = ['status'=>1,'msg'=>'操作成功,已经投票'.Cache::get($cach_key),'data'=>['id'=>$id]];
        }else{
            $data = ['status'=>0,'msg'=>'不能进行操作,您已经投了'.Cache::get($cach_key).'次','data'=>['id'=>$id]];
        }

        return response()->jsonp($request->call_back,$data);

    }

    public function anyRegister(Request $request){
        $validator = Validator::make($request->all(),[
             'name'=>'required',
             'mobile'=>'required'
            ]);
        if($validator->fails()){
            $data = ['status'=>0,'msg'=>'请填写完整','data'=> $validator->messages()];
        }else{
            QiyeUser::firstOrCreate($request->all());
            $data = ['status'=>1,'msg'=>'提交成功，请继续投票吧','data'=>''];
            $user_info = serialize($request->all());
            Session::set('user_info',$user_info);
        }
        return response()->jsonp($request->call_back,$data);
    }
    public function show($id=1,QiyeType $qiyeType){
        $cache =false;
        if(!$cache){
            $categorys = QiyeCategory::orderBy('listorder','DESC')->where('type_id',$id)->with(['Item'=>function($q){
                $q->orderBy('listorder','DESC')->with(['count'=>function($q){
                    $q->select('id','count');
                }]);
            }])->get()->toArray();
        }else{
            $categorys = Cache::get('categorys_'.$id,function() use ($id){
                $categorys = QiyeCategory::orderBy('listorder','DESC')->where('type_id',$id)->with(['Item'=>function($q){
                    $q->orderBy('listorder','DESC')->with(['count'=>function($q){
                        $q->select('id','count');
                    }]);
                }])->get()->toArray();
                $expiresAt = Carbon::now()->addMinutes(10)->diffInMinutes();
                Cache::add('categorys_'.$id,$categorys,$expiresAt);
                return $categorys;
            });
        }
        $qiye  =  $qiyeType->getTypes($id);
        $title = '“疆耀•梦想”2015年度'.$qiye['type_name'].'品牌榜样评选';
        return view('jiangyaomengxiang',compact('categorys','title','qiye'));
    }

    public function anyType(){
        $result = QiyeType::OrderBy('listorder','desc')->get()->toArray();
        $data = ['msg'=>'ok','status'=>1,'list'=>$result];
        Storage::disk('public')->put('/api/qiye/type.js',"var data=".json_encode($data));
        return response()->json($data);
    }

    public function getTest(){
        $id= 1;
        $categorys = QiyeCategory::orderBy('listorder','DESC')->where('type_id',$id)->with(['Item'=>function($q){
            $q->orderBy('listorder','DESC')->with(['count'=>function($q){
                $q->select('id','count');
            }]);
        }])->get()->toArray();
        return response()->json($categorys);
    }

} 