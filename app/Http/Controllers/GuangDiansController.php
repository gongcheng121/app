<?php

namespace App\Http\Controllers;

use App\Model\GuangDian;
use App\Model\GuangdianCode;
use App\Model\GuangDianHelpLog;
use App\Model\GuangDianPrize;
use App\Model\WechatMemberDetail;
use App\Repositories\GuangDianHelpLogRepositoryEloquent;
use App\Repositories\GuangDianRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\GuangDianCreateRequest;
use App\Http\Requests\GuangDianUpdateRequest;
use App\Validators\GuangDianValidator;


class GuangDiansController extends Controller
{

    /**
     * @var GuangDianRepositoryEloquent
     */
    protected $repository;
    protected $help_log_repository;

    /**
     * @var GuangDianValidator
     */
    protected $validator;


    public function __construct(GuangDianRepositoryEloquent $repository, GuangDianValidator $validator, GuangDianHelpLogRepositoryEloquent $help_log_repository)
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


        return redirect(url('guangdian?key=' . $key . '#my'))->with('authed', $member['openid']);
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
        $title = '“鸡”动时刻 拆盒有礼 新疆广电网络';
        if (request()->wantsJson()) {

            $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
            $guangdians = $this->repository->with(['help_logs' => function ($q) {
                $q->with('wechatMember');
            }])->findWhere(
                [
                    'openid' => $member['openid'], 'status' => 0
                ],
                ['status', 'help_counts', 'id']
            )->first();

            $mygift = $this->repository->orderBy('created_at', 'DESC')->findWhere(['status' => 1, 'openid' => $member['openid']]);

            $detail = Cache::get('detail_' . $member['openid'], function () use ($member) {
                $detail = WechatMemberDetail::where('openid', $member['openid'])->first();
                Cache::put('detail_' . $member['openid'], $detail, Carbon::now()->addDay(10)->diffInMinutes());
                return $detail;
            });
            $is_detail = 1;
            if (!$detail) {
                $is_detail = 0;
            }

            return response()->json([
                'data' => $guangdians,
                'mygift' => $mygift,
                'is_detail' => $is_detail
            ]);
        }

        if (!session('authed')) {
            return view('activity.guangdian.noauth', compact('title'));
        }


        $openid = $member['openid'];
        $crypt_openid = Crypt::encrypt($openid);

        return view('activity.guangdian.index', compact('key', 'crypt_openid', 'title', 'member'));
    }


    public function postDetails(Request $request)
    {
        $key = $request->key;
        $member = getOpenId($key);
        $openid = $member['openid'];
        if (!$request->data) {
            return '';
        }
        $data = $request->data;
        $data['openid'] = $openid;
        $detail = WechatMemberDetail::firstOrcreate($data);
//        dd($detail);
        Cache::put('detail_' . $member['openid'], $detail, Carbon::now()->addDay(10)->diffInMinutes());
        return response()->json(['msg' => 'ok']);

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('guangDians.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  GuangDianCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(GuangDianCreateRequest $request, GuangdianCode $guangdianCode)
    {

        try {


            $diff = Carbon::now()->diffInDays(Carbon::create(2016, 8, 8), false);
//            return response()->json(['error' => 1, 'msg' => '活动已经结束了']);

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
                ], ['prize_id']
            )->all();


            $has_pid=false;
            $count = sizeof($has_prize);
            if ($count > 0 && $count < 3) {
                foreach ($has_prize as $v) {
                    $has_pid[] = $v['prize_id'];
                }
            }

            $guangdianPrizeM = GuangDianPrize::where('count', '>', '0')->where('v', '>', '0');

            if ($has_pid) {
                $guangdianPrizeM->whereNotIn('id', $has_pid);
            }
            $prize = $guangdianPrizeM->get();


            $prize_d = [];

            foreach ($prize as $key => $val) {
                $arr[$val['id']] = $val['v'];
                $prize_d[$val['id']] = $val;
            }
            $rid = getRand($arr);

            if ($count >= 3) {
                $rid = 1;//种过奖
            }

            $data['prize_id'] = $prize_d[$rid]->id;
            $data['prize'] = $prize_d[$rid]->name;
            $data['openid'] = $member['openid'];
            $data['help_counts'] = 0;
            $prize_d[$rid]->decrement('count', 1);
            $hongFu = $this->repository->create($data);

            //根据奖品 关联 code

            if ($hongFu->prize_id == 9) { //八等奖
                $code = GuangdianCode::where('type', 8)->where('status', 0)->first();
                $code->gid = $hongFu->id;
                $code->status = 1;
                $code->save();
            } else if ($hongFu->prize_id == 8) { //七等奖
                $code = GuangdianCode::where('type', 7)->where('status', 0)->first();
                $code->gid = $hongFu->id;
                $code->status = 1;
                $code->save();
            } else if ($hongFu->prize_id == 7) { //六等奖
                $code = GuangdianCode::where('type', 6)->where('status', 0)->first();
                $code->gid = $hongFu->id;
                $code->status = 1;
                $code->save();
            } else if ($hongFu->prize_id == 5) { //四等奖 三合一
                $c['6'] = $guangdianCode->where('status', 0)->where('type', 6)->first();
                $c['7'] = $guangdianCode->where('status', 0)->where('type', 7)->first();
                $c['8'] = $guangdianCode->where('status', 0)->where('type', 8)->first();
                foreach ($c as $code) {
                    $code->gid = $hongFu->id;
                    $code->status = 1;
                    $code->save();
                }
            }

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

    public function postChai(Request $request)
    {
//        return response()->json(['error' => 1, 'msg' => '活动已经结束']);
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


    public function getShare(Request $request)
    {
        Carbon::setLocale('zh');
        $to = $request->o;
        try {
            $to_openid = Crypt::decrypt($to); // 需要帮助的人
        } catch (DecryptException $e) {
            return response('错误的链接', 404);
        }
        $key = $request->key;
        $member = getOpenId($key);
        if ($member['openid'] == $to_openid) {
            //如果是自己打开 跳转回去
            return redirect(url('guangdian?key=' . $key . '#my'))->with('authed', $member['openid']);
        }

        $gift = $this->repository->with('wechatMember')->findWhere(['openid' => $to_openid, 'status' => 0])->first();
        $title = '帮助' . $gift['wechatMember']['nickname'] . '拆礼盒';
        $has_help = GuangDianHelpLog::where('openid', $member['openid'])->where('to_openid', $to_openid)->count();
        $help_log = $this->help_log_repository->orderBy('id', 'desc')->with('wechatMember')->findWhere(['to_openid' => $to_openid,'gift_id'=>$gift['id']]);

        return view('activity.guangdian.share', compact('key', 'member', 'help_log', 'has_help', 'to', 'title', 'gift'));
    }

    public function postHelp(Request $request)
    {
        $to = $request->o;
        $to_openid = Crypt::decrypt($to); // 需要帮助的人

        $key = $request->key;
        $member = getOpenId($key);
//        return response()->json(['error' => 2, 'msg' => '活动已经结束了']);

        $gift_id = $request->gift_id;
        if (!$gift_id) {
            $gift = $this->repository->findWhere(['openid' => $to_openid, 'status' => 0])->first();

            if (!$gift || $gift->help_counts >= 5) {
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

    public function postGift(Request $request)
    {

        $key = $request->key;
        $member = getOpenId($key);

        $data['code'] = 200;
        $data['list'] = $this->repository->with(['code' => function ($q) {
            $q->select('gid', 'code', 'type');
        }])->findWhere(['status' => 1, 'openid' => $member['openid']], ['status', 'help_counts', 'id', 'prize', 'prize_id', 'updated_at'])->all();
        return response()->json($data);
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
        $guangDian = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $guangDian,
            ]);
        }

        return view('guangDians.show', compact('guangDian'));
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

        $guangDian = $this->repository->find($id);

        return view('guangDians.edit', compact('guangDian'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  GuangDianUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(GuangDianUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $guangDian = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'GuangDian updated.',
                'data' => $guangDian->toArray(),
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
                'message' => 'GuangDian deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'GuangDian deleted.');
    }
}
