<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/3 0003
 * Time: 下午 1:33
 */

namespace App\Http\Controllers\Admin;


use App\Model\LotteryResult;
use Maatwebsite\Excel\Facades\Excel;
class LotteryController extends AdminBaseController{
    public function __construct(){
        parent::__construct();
    }

    public function getResult(){
        $lottery_model = LotteryResult::with('wechatMember')->orderBy('add_time','DESC');
        $lottery_model->where('prize_id','!=','8');
        $lottery_result = $lottery_model->paginate(20);
        return view('admin.lottery.result',compact('lottery_result'));
    }

    public function getUpdate($k){
        $result = LotteryResult::find($k);
        $result->status = 1;
        $result->save();
        return redirect()->back();
    }

    public function getExport(){
        $lottery_model = LotteryResult::with('wechatMember')->orderBy('add_time','DESC');
        $lottery_model->where('prize_id','!=','8');
        $lottery_model->where('prize_id','!=','7');
        $lottery_result = $lottery_model->get()->toArray();
        foreach($lottery_result as $k=>$v){
            $lottery_result[$k]['nickname'] = $v['wechat_member']['nickname'];
            unset($lottery_result[$k]['wechat_member']);
            unset($lottery_result[$k]['add_time']);
        }


        $filename = '抽奖记录表'.date('YmdHis',time());
        $excel = Excel::create($filename, function($excel) {
            $excel->setTitle('Our new awesome title');
            $excel->setCreator('Maatwebsite')
                ->setCompany('Maatwebsite');
            $excel->setDescription('A demonstration to change the file properties');
        });
        $excel->sheet('抽奖记录表', function($sheet)  use($lottery_result) {
            $sheet->fromArray($lottery_result);
        });
        $excel->export();

    }

} 