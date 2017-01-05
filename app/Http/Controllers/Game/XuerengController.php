<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/9 0009
 * Time: 下午 6:29
 */

namespace App\Http\Controllers\Game;


use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class XuerengController extends BaseController{


    const default_disc = 3;
    const default_score = 10;
    public $wxopenid;

    public function __construct(Request $request)
    {
        $this->wxopenid = $request->wxopenid;
    }

    public function getGetuserinfo(Request $request){

        $score = Cache::get('discScore_'.$this->wxopenid,function(){
            $expiresAt = Carbon::now()->addMonth(1);
            Cache::add('discScore_'.$this->wxopenid,self::default_disc,$expiresAt);
            return self::default_disc;
        });
        if($request->getscore==1){
            $data = ['Score'=>$score,'piect'=>0,"Act"=>"loadscore"];
            return response()->json($data);
        }
        $disc=  Cache::get('disc_'.$this->wxopenid,function(){
            $expiresAt = Carbon::now()->addHour(1);
            Cache::add('disc_'.$this->wxopenid,self::default_disc,$expiresAt);
            return self::default_disc;
        });
        if($disc<0)$disc=0;
        $data = json_decode('{"mapNo":4,"mapEvent":[0,0,0,0,0,3,3,2,0,0,1,0,0,0,1,1,0,0,2,0,4,2,2,2,0,0,0,1,0,0,0,0,1,0,3,1,0,1,4,3,1,0,0,0,3,2,2,0,0,3,4,2,4,0,0,0,0,0,0,4,0,0,0,0,2,0,4,2,1,3,1,4,0,1,0,1,4,2,1,4,0,2,1,3,0,0,3,0,1,3,0,2,1,1,0,0,0,0,0,0,0,0,1,4,0,0,2,0,2,0,4,4,3,1,0,1,0,0,3,0,0,0,4],"startStep":3,"dice":'.$disc.',"Score":200,"Act":"load"}');
        return response()->json($data);
    }

    public function getGetrankinglist(Request $request){
        $list = [
            ['name'=>"name1","score"=>65010],
            ['name'=>"name2","score"=>61010],
        ];
        $data = [
            "hongbaolist"=>'',
            "list"=>$list,
            "totalscore"=>'',
            "usersort"=>'',
        ];
        return response()->json($data);
    }
    public function getSavelog(Request $request){

    }
    public function getDice(Request $request){
        $key = 'disc_'.$this->wxopenid;
        $keyScore = 'discScore_'.$this->wxopenid;
        $num = rand(1,6);
        $stepNo = ['stop','dice1','dice2','win1','win2','lost','0','lost','stop','0','0','0'];
        $disc=  Cache::get($key,function() use ($key){
            $expiresAt = Carbon::now()->addHour(1);
            Cache::add($key,self::default_disc,$expiresAt);
            return self::default_disc;
        });
        $disc--;
        Cache::decrement($key,1);
        Cache::increment($keyScore,10*$num);
        if($disc<0) $disc=0;
        $result = $stepNo[rand(0,11)];
        if($result=='dice2' || $result=='win2'){
            $disc+=2;
            Cache::increment($key, 2);
        }
        if($result=='dice1' || $result=='win1'){
            $disc+=1;
            Cache::increment($key, 1);
        }

        $data  =  [
            'stepResult'=>[$num,$result],
            'score'=>830,
            'dice'=>$disc,
            'Act'=>'updatesuccess'
        ];


        return response()->json($data);
    }

} 