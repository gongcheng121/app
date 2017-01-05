<?php

namespace App\Http\Controllers;

use App\Model\GuozhiyuanVotePoll;
use App\Model\GuozhiyuanVotePollLog;
use App\Repositories\GuozhiyuanVoteRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\GuozhiyuanVoteCreateRequest;
use App\Http\Requests\GuozhiyuanVoteUpdateRequest;
use App\Repositories\GuozhiyuanVoteRepository;
use App\Validators\GuozhiyuanVoteValidator;
use zgldh\UploadManager\UploadManager;


class GuozhiyuanVotesController extends BaseController
{

    /**
     * @var GuozhiyuanVoteRepository
     */
    protected $repository;

    /**
     * @var GuozhiyuanVoteValidator
     */
    protected $validator;


    public function __construct(GuozhiyuanVoteRepositoryEloquent $repository, GuozhiyuanVoteValidator $validator)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->validator = $validator;
        $this->middleware('wechat');
        Carbon::setLocale('zh');

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));

        if (request()->wantsJson()) {
            $guozhiyuanVotes = $this->repository->with('count')->all();
            $guozhiyuanVotes = $guozhiyuanVotes['data'];
            return response()->json([
                'data' => $guozhiyuanVotes,
            ]);
        }

        $key = $request->key;
        $member = getOpenId($key);

        $openid = $member["openid"];

        $result = $this->repository->skipPresenter()->findByField('openid', $openid)->first();

        $title = '吐鲁番果之源LOGO征集';
        return view('activity.guozhiyuan.index', compact( 'result', 'key', 'title'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.activity.guozhiyuan.add');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  GuozhiyuanVoteCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(GuozhiyuanVoteCreateRequest $request)
    {

        try {


            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);
            $data = $request->all();
            $member = getOpenId($request->key);
            $openid = $member["openid"];
            $data['openid'] = $openid;
            $guozhiyuanVote = $this->repository->create($data);


            $response = [
                'message' => '添加成功',
                'data' => $guozhiyuanVote->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }
            return redirect(url('guozhiyuan?key=' . $request->key . '&ss=' . time()));
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
        $guozhiyuanVote = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $guozhiyuanVote,
            ]);
        }

        return view('guozhiyuanVotes.show', compact('guozhiyuanVote'));
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

        $guozhiyuanVote = $this->repository->find($id);

        return view('guozhiyuanVotes.edit', compact('guozhiyuanVote'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  GuozhiyuanVoteUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(GuozhiyuanVoteUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $guozhiyuanVote = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'GuozhiyuanVote updated.',
                'data' => $guozhiyuanVote->toArray(),
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
                'message' => 'GuozhiyuanVote deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'GuozhiyuanVote deleted.');
    }

    public function anyUpload(Request $request)
    {
        $file = $request->file;
        $manager = UploadManager::getInstance();
        $upload = $manager->upload($file);
        $upload->save();

        $img = Image::make($upload->path);
        $img->resize(320, 320);
        $img->save($img->dirname . '/thumb_' . $img->basename);
        return $upload;
    }

    public function postVote(Request $request)
    {
        $message = ['code'=>0,'msg'=>'投票已截止'];
        return response()->json($message);
        $member = getOpenId($request->key);
        $openid = $member["openid"];
        $cache_key = 'guozhiyuanvote_'.'-' . $openid;
        $cache = Cache::get($cache_key, function () use ($cache_key,$request,$openid) {
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            $log = GuozhiyuanVotePollLog::create(['vote_id' => $request->id, 'openid' => $openid, 'ip' => $request->ip()]);
            Cache::put($cache_key,$log,$expiresAt);
            return false;
        });
        $message = ['code'=>1,'msg'=>'投票成功'];
        if($cache){
            $message = ['code'=>0,'msg'=>'请勿重复投票，请在'.Carbon::now()->diffForHumans($cache->created_at->addDay(),true).'后继续投票'];
        }else{
            $count = GuozhiyuanVotePoll::firstOrCreate(['vote_id' => $request->id]);
            $count->increment('count', 1);
        }

        return response()->json($message);

    }

   
}
