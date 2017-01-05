<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\LiveHostdRepositoryEloquent;
use App\Repositories\LiveRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Ixudra\Curl\Facades\Curl;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\LiveHostdCreateRequest;
use App\Http\Requests\LiveHostdUpdateRequest;
use App\Repositories\LiveHostdRepository;
use App\Validators\LiveHostdValidator;


class LiveHostdsController extends AdminBaseController
{


    /**
     * @var LiveHostdRepository
     */
    protected $repository;
    protected $live_repository;

    /**
     * @var LiveHostdValidator
     */
    protected $validator;


    public function __construct(LiveHostdRepositoryEloquent $repository, LiveRepositoryEloquent $liveRepositoryEloquent, LiveHostdValidator $validator)
    {
        $this->repository = $repository;
        $this->live_repository = $liveRepositoryEloquent;
        $this->validator = $validator;
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
        $liveHostds = $this->repository->paginate();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $liveHostds,
            ]);
        }

        return view('liveHostds.index', compact('liveHostds'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('liveHostds.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  LiveHostdCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LiveHostdCreateRequest $request)
    {


        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $liveHostd = $this->repository->create($request->all());
            $response = Curl::to('http://220.171.90.234:9033/send')
                ->withData(['room' => 'live_' . $liveHostd->lid, 'data' => ['action' => 'create', 'data' => $liveHostd->toJson()]])
                ->post();
//            dd($response);
            $response = [
                'message' => 'LiveHostd created.',
                'data' => $liveHostd->toArray(),
            ];


            return response()->json($response);

//            return redirect()->back()->with('message', $response['message']);
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
        $live = $this->live_repository->with('live_hostds')->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $live,
            ]);
        }
        if(is_mobile()){
            return view('admin.liveHostds.show_mobile', compact('live'));
        }
        return view('admin.liveHostds.show', compact('live'));
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

        $liveHostd = $this->repository->find($id);

        return view('liveHostds.edit', compact('liveHostd'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  LiveHostdUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(LiveHostdUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $liveHostd = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'LiveHostd updated.',
                'data' => $liveHostd->toArray(),
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
                'message' => 'LiveHostd deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'LiveHostd deleted.');
    }
}
