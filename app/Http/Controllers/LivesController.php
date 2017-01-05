<?php

namespace App\Http\Controllers;

use App\Repositories\LiveRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use League\Fractal\Scope;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\LiveCreateRequest;
use App\Http\Requests\LiveUpdateRequest;
use App\Repositories\LiveRepository;
use App\Validators\LiveValidator;


class LivesController extends Controller
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
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $lives = $this->repository->all();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $lives,
            ]);
        }

        return view('lives.index', compact('lives'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('lives.create');
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

            $live = $this->repository->create($request->all());

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
        $live = $this->repository->skipCache()->with(['live_hostds'=>function( $q){
                return $q->orderBy('id','asc');
        }])->find($id);
        $live->getModel()->increment('views');
        if (request()->wantsJson()) {

            return response()->json([
                'data' => $live,
            ]);
        }

        return view('lives.show', compact('live'));
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
