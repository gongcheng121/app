<?php

namespace App\Http\Controllers;

use App\Model\Game\GamePrizeInfo;
use Illuminate\Http\Request;

use App\Http\Requests;

class LvYouJuController extends BaseController
{
    protected $prizeInfo;

    //
    public function __construct(GamePrizeInfo $prizeInfo)
    {
        parent::__construct();
        $this->prizeInfo = $prizeInfo;
    }

    public function index(){

        dd(time());
    }

    public function ajax(){

    }

    public function create(){}
    public function show($id){


        $assets = url('lyj/resource/'.$id);

        try{

            return view('game/fanke_game/'.$id,compact('id','assets'));
        }catch (\InvalidArgumentException $e){
            return 404;
        }
    }

    public function anyAchieve(){
      $return =  '{"success":true,"rt":0,"isSuc":true,"rank":1,"beat":90,"score":295.00,"playerId":10,"msg":"设置成绩成功"}';

        return $return;
    }


    public function anyResult(){
       $return = ' {"success":1,"msg":"没有奖品了 ","rt":"13"}';
        return $return;
    }
}
