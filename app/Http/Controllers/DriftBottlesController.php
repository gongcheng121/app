<?php

namespace App\Http\Controllers;

use App\Model\DriftBottle;
use App\Model\DriftBottleCard;
use App\Model\DriftBottleGift;
use App\Model\DriftBottleMyGift;
use App\Repositories\DriftBottleRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\DriftBottleCreateRequest;
use App\Http\Requests\DriftBottleUpdateRequest;
use App\Repositories\DriftBottleRepository;
use App\Validators\DriftBottleValidator;
use Symfony\Component\Console\Input\Input;


class DriftBottlesController extends BaseController
{

    /**
     * @var DriftBottleRepository
     */
    protected $repository;

    /**
     * @var DriftBottleValidator
     */
    protected $validator;

    protected $key;

    protected $cards;
    protected $cardlist;

    public function __construct(DriftBottleRepositoryEloquent $repository, DriftBottleValidator $validator, Request $request)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->validator = $validator;
        $this->middleware('wechat');
        $this->key = $request->key;
        $this->cards = [
            '1' => '红',
            '2' => '绿',
            '3' => '紫',
            '4' => '黄',
            '5' => '橙',
//            '6'=>'粉',
        ];
        $cardlist = [
            ['id' => '1', 'name' => '电梯小高层120平米商业住房一套 价值36万元', 'condition' => [ 2 => 10, 3 => 10, 4 => 10, 5 => 10, 6 => 10,7 => 10]],//没有此项，

            ['id' => '2', 'name' => '喀纳斯4天亲子游（2人名额）价值3000元', 'condition' => [ 2 => 9, 3 => 9, 4 => 9, 5 => 9,6 => 10]],

            ['id' => '3', 'name' => '大河宴三个小伙伴套餐（1.2kg斑鱼+锅底一份+娃娃菜一份+金针菇', 'condition' => [5 => 6]],

            ['id' => '4', 'name' => '大河宴两个好基友套餐（1kg斑鱼+锅底+生菜+2位餐位费）价值163元', 'condition' => [2 => 6]],

            ['id' => '5', 'name' => '大河宴胡吃嗨喝券  价值118元', 'condition' => [4 => 6]],

            ['id' => '6', 'name' => '大河宴价值98元斑鱼1kg免费券', 'condition' => [5 => 6]],

            ['id' => '7', 'name' => '大河宴68元代金券', 'condition' => [3 => 30]],

            ['id' => '8', 'name' => '川CS黑参原浆面膜/盒（7片装）', 'condition' => [3 => 10, 5 => 4]],

            ['id' => '9', 'name' => '川CS红参原浆面膜/盒（7片装）', 'condition' => [3 => 10, 5 => 4]],

            ['id' => '10', 'name' => '川CS白参原浆面膜/盒（7片装）', 'condition' => [3 => 10, 5 => 4]],

            ['id' => '11', 'name' => '净爽爽细肤甜心泥膜', 'condition' => [6 => 4, 3 => 10]],

            ['id' => '12', 'name' => '红润润雪肌甜心泥膜', 'condition' => [6 => 4, 3 => 10]],

            ['id' => '13', 'name' => '水嘟嘟补水甜心泥膜', 'condition' => [6 => 4, 3 => 10]],

            ['id' => '14', 'name' => '川CS蜂浆弹滑祛角质素（60g）', 'condition' => [6 => 4, 5 => 10]],

            ['id' => '15', 'name' => '魔力星级魅翘睫毛膏', 'condition' => [6 => 4, 4 => 10]],

        ];
        $this->cardlist = $cardlist;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $driftBottles = $this->repository->all();
        $member = Session::get('wechat_user_' . $this->key);
        $key = $this->key;
        if (request()->wantsJson()) {

            return response()->json([
                'data' => $driftBottles,
            ]);
        }
        $title='一万条鱼唤一万个梦想游戏奖品';
        return view('activity.driftBottles.index', compact('driftBottles', 'key','title'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('driftBottles.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  DriftBottleCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(DriftBottleCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);
            $data = $request->all();
            $key = $request->key;
            $member = getOpenId($key);
            $data['openid'] = $member['openid'];
            $driftBottle = $this->repository->create($data);

            Cache::put('can_' . $member['openid'], '1', Carbon::now()->addDay(10)->diffInMinutes());
            $response = [
                'message' => 'DriftBottle created.',
                'data' => $driftBottle->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessageBag()
                ]);
            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $driftBottle = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $driftBottle,
            ]);
        }

        return view('driftBottles.show', compact('driftBottle'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $driftBottle = $this->repository->find($id);

        return view('driftBottles.edit', compact('driftBottle'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  DriftBottleUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(DriftBottleUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $driftBottle = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'DriftBottle updated.',
                'data' => $driftBottle->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {

            if ($request->wantsJson()) {

                return response()->json([
                    'error' => true,
                    'message' => $e->getMessageBag()
                ]);
            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

        if (request()->wantsJson()) {

            return response()->json([
                'message' => 'DriftBottle deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'DriftBottle deleted.');
    }

    public function postLao(Request $request)
    {
        $key = $request->key;
        $member = getOpenId($key);

        $can = Cache::pull('can_' . $member['openid']);
        if (!$can) {
            return response()->json(['data' => 0, 'error' => 1, 'message' => '请先提交梦想']);
        }

        $can = $this->check_can($key);
        $prize_arr_new=[];
        $prize_arr = DriftBottleGift::where('count', '>', '0')->get()->toArray();
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
            $c[$val['id']] = $val['count'];
            $prize_arr_new[$val['id']]=$val;
        }
        $rid = $this->getRand($arr);
        if (!$can) {
            $rid = 1;
        };
        $can = DriftBottleMyGift::where('openid',$member['openid'])->count();
        if($can>0){
            $rid =1;
        }
        $res = $prize_arr_new[$rid]; //中奖项
        $gift = DriftBottleGift::where('id', $res['id']);
        $gift->decrement('count');
        if ($res['id'] == '1') {
            //获取其他心愿
            $whish = $this->get_whish();
            return response()->json(['data' => $res, 'whish' => $whish]);
        } else {
            $mycard = DriftBottleCard::firstOrCreate(['openid' => $member['openid'], 'type_id' => $res['id']]);
            $mycard->type = $res['name'];
            $mycard->save();
            $mycard->increment('count');
            return response()->json(['data' => $res, 'message' => '恭喜您，获得了一张' . $res['name'] . '卡,请到我的粮票中查看']);
        }

    }

    public function postMycard(Request $request)
    {
        $key = $request->key;
        $member = getOpenId($key);
        $openid = $member['openid'];


        $mycard = DriftBottleCard::where('openid', $openid)->get();
        $cardlist = $this->get_cardlist($mycard);
        $mygift = DriftBottleMyGift::where('openid',$openid)->where('status',0)->get();
        return response()->json(['data' => $mycard, 'cardlist' => $cardlist,'mygift'=>$mygift]);

    }

    public function postDui(Request $request)
    {
        $key = $request->key;
        $member = getOpenId($key);
        $openid = $member['openid'];

        $mycard = DriftBottleCard::where('openid', $openid)->get();
        $cardlist = $this->get_cardlist($mycard);

        $condition = false;
        foreach ($cardlist as $key => $val) {
            if ($val['id'] == $request->data['id']) {
                if ($val['can']) {
                    $condition = $val['condition'];
                }
            }
        }
        if ($condition) {
            foreach ($condition as $k => $v) {
                DriftBottleCard::where('openid', $openid)->where('type_id', $k)->decrement('count', $v);
            }
            $data = DriftBottleMyGift::create(['openid' => $openid, 'gift_id' => $request->data['id'], 'name' => $request->data['name']]);
            $code = $data->id.str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $data->code = $data['id']+$code;
            $data->save();
            return response()->json(['error' => 0, 'data' => $data, 'msg' => '兑换成功']);
        }
        return response()->json(['error' => 1, 'msg' => '兑换失败']);
    }

    private function get_cardlist($mycard)
    {
        $cardlist = $this->cardlist;
        foreach ($mycard as $key => $item) {
            $newmycard[$item['type_id']] = $item['count'];
        }
        foreach ($cardlist as $k => $v) {
            foreach ($v['condition'] as $key => $c) {
                if (!isset($newmycard[$key])) {
                    $cardlist[$k]['r'][$key] = false;
                } else {
                    $cardlist[$k]['r'][$key] = ($newmycard[$key] >= $c) ? true : false;
                }
            }
        }
        foreach ($cardlist as $k => $v) {
            if (!isset($v['r'])) continue;
            $cardlist[$k]['r'] = collect($v['r']);
            $cardlist[$k]['can'] = (!$cardlist[$k]['r']->contains(false)) ? 1 : 0;
            unset($cardlist[$k]['r']);
        }
        return $cardlist;
    }

    private function check_can($key)
    {
        $can = true;
        $member = getOpenId($key);

        $openid = $member['openid'];

        $cardlist = $this->cardlist;

        $mycard = DriftBottleCard::where('openid', $openid)->get();
        foreach ($mycard as $key => $item) {
            $newmycard[$item['type_id']] = $item['count'];
        }
        foreach ($cardlist as $k => $v) {
            foreach ($v['condition'] as $key => $c) {
                if (!isset($newmycard[$key])) {
                    $cardlist[$k]['r'][$key] = false;
                } else {
                    $cardlist[$k]['r'][$key] = ($newmycard[$key] >= $c+2) ? true : false;
                }
            }
        }
        foreach ($cardlist as $k => $v) {
            if (!isset($v['r'])) continue;
            $cardlist[$k]['r'] = collect($v['r']);
            $cardlist[$k]['can'] = (!$cardlist[$k]['r']->contains(false)) ? 1 : 0;
            if ($cardlist[$k]['can'] == 1) {
                $can = false;
            }
            unset($cardlist[$k]['r']);
        }

        return $can;
    }

    private function get_whish()
    {

        $whish = DriftBottle::where('status','1')->orderBy(DB::raw('RAND()'))->take(1)->first();
        return $whish;
    }

    private function getRand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }


}
