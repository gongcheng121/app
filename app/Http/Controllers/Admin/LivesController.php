<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\LiveRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\LiveCreateRequest;
use App\Http\Requests\LiveUpdateRequest;
use App\Repositories\LiveRepository;
use App\Validators\LiveValidator;
use zgldh\UploadManager\UploadManager;


class LivesController extends AdminBaseController
{

    /**
     * @var LiveRepository
     */
    protected $repository;

    /**
     * @var LiveValidator
     */
    protected $validator;


    public function __construct(LiveRepositoryEloquent $repository, LiveValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $lives = $this->repository->orderBy('id','desc')->paginate();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $lives,
            ]);
        }

        return view('admin.lives.index', compact('lives'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.lives.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  LiveCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LiveCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);
            $data = $request->all();
            $file = $request->file('images');
            $manager = UploadManager::getInstance();
            $data['images']=$manager->upload($file)->path;
            $live = $this->repository->create($data);

            $response = [
                'message' => 'Live created.',
                'data'    => $live->toArray(),
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
        $live = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $live,
            ]);
        }
        return view('admin.lives.show', compact('live'));
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

        $live = $this->repository->find($id);

        return view('lives.edit', compact('live'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  LiveUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(LiveUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $live = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'Live updated.',
                'data'    => $live->toArray(),
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
                'message' => 'Live deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'Live deleted.');
    }
}
