<?php

namespace App\Http\Controllers;

use App\Model\BankVotePoll;
use App\Model\BankVotePollLog;
use App\Repositories\BankVoteRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\BankVoteCreateRequest;
use App\Http\Requests\BankVoteUpdateRequest;
use App\Repositories\BankVoteRepository;
use App\Validators\BankVoteValidator;


class BankVotesController extends BaseController
{

    /**
     * @var BankVoteRepository
     */
    protected $repository;

    /**
     * @var BankVoteValidator
     */
    protected $validator;


    public function __construct(BankVoteRepositoryEloquent $repository, BankVoteValidator $validator)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->validator = $validator;
        $this->middleware('wechat');

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $bankVotes = $this->repository->with('vote_count')->findByField('status', 1);
        $key = $request->key;
        if (request()->wantsJson()) {

            return response()->json([
                'data' => $bankVotes,
            ]);
        }

        $title = '华夏银行 发现美好 还原真实生活';
        return view('activity.bank.index', compact('bankVotes', 'key','title'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('bankVotes.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  BankVoteCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BankVoteCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $data = $request->all();
            $member = getOpenId($request->key);
            $openid = $member->openid;
            $data['openid'] = $openid;
            $bankVote = $this->repository->skipPresenter()->create($data);
            $response = [
                'message' => '作品添加成功，请等待管理员审核',
                'data' => $bankVote->toArray(),
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
        $bankVote = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $bankVote,
            ]);
        }

        return view('bankVotes.show', compact('bankVote'));
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

        $bankVote = $this->repository->find($id);

        return view('bankVotes.edit', compact('bankVote'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  BankVoteUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(BankVoteUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $bankVote = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'BankVote updated.',
                'data' => $bankVote->toArray(),
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
                'message' => 'BankVote deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'BankVote deleted.');
    }


    public function getJoin(Request $request)
    {
        $key = $request->key;
        $member = getOpenId($key);
        $openid = $member['openid'];

        $result = $this->repository->skipPresenter()->findByField('openid', $openid)->first();

        $title = '华夏银行 发现美好 还原真实生活';
        return view('activity.bank.join', compact('key', 'result','title'));
    }

    public function getIntroduction()
    {
        return view('activity.bank.introduction');
    }


    public function postPoll(Request $request){
        $key = $request->key;

        $member = getOpenId($key);
        $openid = $member->openid;
        $key = 'bank_poll'.$openid.$request->id;

        $has = Cache::get($key,function()use($key){
            return false;
        });
        if($has){
            $return['error']='1';
            $return['data']='您已经投过票，请明天再来';
        }else{
            $data['vote_id']=$request->id;
            $result = BankVotePoll::firstOrCreate($data);
            $result->increment('count');
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::put($key,true,$expiresAt);
            BankVotePollLog::create(['vote_id'=>$data['vote_id'],'ip'=>$request->ip(),'open_id'=>$openid]);
            $return=$result;
        }

        return $return;
    }
}
