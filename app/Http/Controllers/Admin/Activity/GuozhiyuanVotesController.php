<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Admin\AdminBaseController;
use App\Repositories\GuozhiyuanVoteRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Intervention\Image\Facades\Image;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\GuozhiyuanVoteCreateRequest;
use App\Http\Requests\GuozhiyuanVoteUpdateRequest;
use App\Repositories\GuozhiyuanVoteRepository;
use App\Validators\GuozhiyuanVoteValidator;
use zgldh\UploadManager\UploadManager;


class GuozhiyuanVotesController extends AdminBaseController
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

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $guozhiyuanVotes = $this->repository->skipPresenter()->paginate();
        return view('admin.activity.guozhiyuan.index', compact('guozhiyuanVotes'));
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
            $openid = 'admin';
            $data['openid'] = $openid;
            $guozhiyuanVote = $this->repository->create($data);


            $response = [
                'message' => '添加成功',
                'data' => $guozhiyuanVote,
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

    public function getDelete($id)
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

    public function anyUpload(Request $request){
        $file = $request->file;
        $manager = UploadManager::getInstance();
        $upload = $manager->upload($file);
        $upload->save();

        $img = Image::make($upload->path);
        $img->resize(320,320);
        $img->save($img->dirname.'/thumb_'.$img->basename);
        return $upload;
    }
}
