<?php

namespace App\Http\Controllers;

use App\Model\DaheVotePoll;
use App\Repositories\DaheVoteRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Intervention\Image\Facades\Image;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\DaheVoteCreateRequest;
use App\Http\Requests\DaheVoteUpdateRequest;
use App\Repositories\DaheVoteRepository;
use App\Validators\DaheVoteValidator;
use zgldh\UploadManager\UploadManager;


class DaheVotesController extends Controller
{

    /**
     * @var DaheVoteRepository
     */
    protected $repository;

    /**
     * @var DaheVoteValidator
     */
    protected $validator;


    public function __construct(DaheVoteRepositoryEloquent $repository, DaheVoteValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));



        if (request()->wantsJson()) {
            $daheVotes = $this->repository->with('poll')->findWhere(['status'=>'1'])->groupBy('type')->all();
            return response()->json([
                'data' => $daheVotes,
            ]);
        }

        $key = '7cb551a19e58ed5524f2be99f251c405';
        $title ='一万条鱼唤一万个梦想 绘画创意大赛';

        return view('activity.dahevote.index', compact('key','title'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title ='一万条鱼唤一万个梦想 绘画创意大赛';
        return view('activity.dahevote.create',compact('title'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  DaheVoteCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(DaheVoteCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $daheVote = $this->repository->create($request->all());

            $response = [
                'message' => '添加成功',
                'data'    => $daheVote->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error'   => true,
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
        $daheVote = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $daheVote,
            ]);
        }

        return view('daheVotes.show', compact('daheVote'));
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

        $daheVote = $this->repository->find($id);

        return view('daheVotes.edit', compact('daheVote'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  DaheVoteUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(DaheVoteUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $daheVote = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'DaheVote updated.',
                'data'    => $daheVote->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {

            if ($request->wantsJson()) {

                return response()->json([
                    'error'   => true,
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
                'message' => 'DaheVote deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'DaheVote deleted.');
    }
    public function anyUpload(Request $request){
        $file = $request->file('file');
        $manager = UploadManager::getInstance();
        $upload = $manager->upload($file);
        $paths = $manager->path($file);
        $new_name = 'thumb_'.$paths['name'];
        $new_path = $paths['path'].$new_name;
        $upload->save();
        $img = Image::make($upload->path);
        $img->resize(320,240);
        $img->save($new_path);
        $upload->thumb = $new_path;
        return $upload;
    }

    public function postPoll(Request $request){
        return false;
        $key = $request->key;

//        $member = getOpenId($key);
//        $openid = $member->openid;
//        $key = 'bank_poll'.$openid.$request->id;

//        $has = Cache::get($key,function()use($key){
//            return false;
//        });
        $has=  false;
        if($has){
            $return['error']='1';
            $return['data']='您已经投过票，请明天再来';
        }else{
            $data['vote_id']=$request->id;
            $result = DaheVotePoll::firstOrCreate($data);
            $result->increment('count');
//            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
//            Cache::put($key,true,$expiresAt);
//            BankVotePollLog::create(['vote_id'=>$data['vote_id'],'ip'=>$request->ip(),'open_id'=>$openid]);
            $return=$result;
        }

        return $return;
    }
}
