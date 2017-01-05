<?php

namespace App\Http\Controllers;

use App\Model\HongFuHelpLog;
use App\Model\HongFuPrize;
use App\Model\WechatMemberDetail;
use App\Repositories\HongFuHelpLogRepositoryEloquent;
use App\Repositories\HongFuRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\HongFuCreateRequest;
use App\Http\Requests\HongFuUpdateRequest;
use App\Repositories\HongFuRepository;
use App\Validators\HongFuValidator;


class HongFusController extends Controller
{

    /**
     * @var HongFuRepository
     */
    protected $repository;
    protected $help_log_repository;

    /**
     * @var HongFuValidator
     */
    protected $validator;


    public function __construct(HongFuRepositoryEloquent $repository, HongFuValidator $validator, HongFuHelpLogRepositoryEloquent $help_log_repository)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->help_log_repository = $help_log_repository;
        $this->middleware('wechat');
    }


    public function getGame(Request $request)
    {
        $key = $request->key;

        $member = getOpenId($key);


        return redirect(url('hongfu?key=' . $key . '#my'))->with('authed', $member['openid']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $key = $request->key;
        $member = getOpenId($key);
        $title = '尊茂鸿福18年店庆感恩礼物大派送';

        if (request()->wantsJson()) {

            $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
            $hongFus = $this->repository->with(['help_logs' => function ($q) {
                $q->with('wechatMember');
            }])->findWhere(
                [
                    'openid' => $member['openid'], 'status' => 0
                ],
                ['status', 'help_counts', 'id']
            )->first();
            $mygift  =  $this->repository->orderBy('created_at','DESC')->findWhere(['status'=>1,'openid'=>$member['openid']]);


            $detail = Cache::get('detail_'.$member['openid'],function() use($member){
                $detail = WechatMemberDetail::where('openid',$member['openid'])->first();
                Cache::put('detail_'.$member['openid'],$detail,Carbon::now()->addDay(10)->diffInMinutes());
                return $detail;
            });
            $is_detail=1;
            if(!$detail){
                $is_detail=0;
            }

            return response()->json([
                'data' => $hongFus,
                'mygift'=>$mygift,
                'is_detail'=>$is_detail
            ]);
        }

        if (!session('authed')) {
//            return view('activity.hongfu.noauth', compact('title'));
        }


        $openid = $member['openid'];
        $crypt_openid = Crypt::encrypt($openid);

        return view('activity.hongfu.index', compact('key', 'crypt_openid', 'title', 'member'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('hongFus.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  HongFuCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(HongFuCreateRequest $request)
    {

        try {



            $diff = Carbon::now()->diffInDays(Carbon::create(2016, 8, 8), false);
            return response()->json(['error' => 1, 'msg' => '活动已经结束了']);
            if ($diff > 0) {
//                return response()->json(['error' => 1, 'msg' => '还差' . $diff . '天才开始活动哦']);
            }

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $data = $request->all();
            $key = $request->key;
            $member = getOpenId($key);


            $hongFus = $this->repository->findWhere(
                [
                    'openid' => $member['openid'], 'status' => 0
                ],
                ['status', 'help_counts']
            )->count();

            if ($hongFus) {
                return response()->json(['error' => 1, 'msg' => '您有一个礼盒还未拆开']);
            }
            $has_prize = $this->repository->findWhere(
                [
                    'openid' => $member['openid'],

                    ['prize_id', '!=', '1']
                ]
            )->count();


            $prize = HongFuPrize::where('count', '>', '0')->where('v', '>', '0')->get();


            foreach ($prize as $key => $val) {
                $arr[$val['id']] = $val['v'];
                $prize_d[$val['id']] = $val;
            }
            $rid = getRand($arr);

            if($has_prize){
                $rid=1;//种过奖
            }

            $data['prize_id'] = $prize_d[$rid]->id;
            $data['prize'] = $prize_d[$rid]->name;
            $data['openid'] = $member['openid'];
            $data['help_counts'] = 0;
            $prize_d[$rid]->decrement('count',1);
            $hongFu = $this->repository->create($data);
            $return['help_counts'] = $hongFu->help_counts;
            $return['id'] = $hongFu->id;
            $response = [
                'message' => '创建成功',
                'data' => $return,
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


    public function getShare(Request $request)
    {
        Carbon::setLocale('zh');
        $to = $request->o;
        $to_openid = Crypt::decrypt($to); // 需要帮助的人
        $key = $request->key;
        $member = getOpenId($key);


        if ($member['openid'] == $to_openid) {
            //如果是自己打开 跳转回去
            return redirect(url('hongfu?key=' . $key . '#my'))->with('authed', $member['openid']);
        }

        $gift = $this->repository->with('wechatMember')->findWhere(['openid' => $to_openid, 'status' => 0])->first();
        $title = '帮助' . $gift['wechatMember']['nickname'] . '拆礼盒';
        $has_help = HongFuHelpLog::where('openid', $member['openid'])->where('to_openid', $to_openid)->count();
        $help_log = $this->help_log_repository->orderBy('id','desc')->with('wechatMember')->findWhere(['to_openid' => $to_openid]);
        return view('activity.hongfu.share', compact('key', 'member', 'help_log', 'has_help', 'to', 'title', 'gift'));
    }


    public function postHelp(Request $request)
    {
        $to = $request->o;
        $to_openid = Crypt::decrypt($to); // 需要帮助的人

        $key = $request->key;
        $member = getOpenId($key);
        return response()->json(['error' => 2, 'msg' => '活动已经结束了']);

        $gift_id = $request->gift_id;
        if (!$gift_id) {
            $gift = $this->repository->findWhere(['openid' => $to_openid, 'status' => 0])->first();

            if (!$gift) {
                return response()->json(['error' => 2, 'msg' => '他的礼盒已经拆开啦']);
            }
        }
        $cache_key = $to_openid . $member['openid'] . $gift_id;
        $has_helped = Cache::get($cache_key, function () use ($cache_key, $to_openid, $member, $gift_id) {
            $count = $this->help_log_repository->findWhere([
                'to_openid' => $to_openid,
                'openid' => $member['openid'],
//                'gift_id'=>$gift_id
            ])->count();
            Cache::put($cache_key, 1);
            return $count;
        });

        if ($has_helped > 0) {
            return response()->json(['error' => 1, 'msg' => '您已经帮助过了']);
        }
        $this->repository->find($gift_id)->increment('help_counts', 1);
        $this->help_log_repository->create(['openid' => $member['openid'], 'to_openid' => $to_openid, 'gift_id' => $gift_id]);
        return response()->json(['msg' => '帮助成功']);

    }

    public function postChai(Request $request)
    {
        return response()->json(['error' => 1, 'msg' => '活动已经结束']);
        $key = $request->key;
        $member = getOpenId($key);

        $id = $request->data['id'];
        $gift = $this->repository->find($id);
        if ($gift->help_counts < 5) {
            return response()->json(['error' => 1, 'msg' => '还不能拆开哦', 'data' => $gift]);
        }
        if ($gift->status == 1) {
            return response()->json(['error' => 1, 'msg' => '已经拆开了', 'data' => $gift]);
        }
        $gift->status = 1;
        $gift->save();

        return response()->json(['msg' => '拆开成功', 'data' => $gift]);

    }

    public function postDetails(Request $request){
        $key = $request->key;
        $member = getOpenId($key);
        $openid = $member['openid'];
        if(!$request->data){
            return '';
        }
        $data=$request->data;
        $data['openid']=$openid;
        $detail = WechatMemberDetail::firstOrcreate($data);
        Cache::put('detail_'.$member['openid'],$detail,Carbon::now()->addDay(10)->diffInMinutes());
        return response()->json(['msg'=>'ok']);

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
        $hongFu = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $hongFu,
            ]);
        }

        return view('hongFus.show', compact('hongFu'));
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

        $hongFu = $this->repository->find($id);

        return view('hongFus.edit', compact('hongFu'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  HongFuUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(HongFuUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $hongFu = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'HongFu updated.',
                'data' => $hongFu->toArray(),
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
                'message' => 'HongFu deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'HongFu deleted.');
    }
}
