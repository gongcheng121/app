<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\LiveHostdCreateRequest;
use App\Http\Requests\LiveHostdUpdateRequest;
use App\Repositories\LiveHostdRepository;
use App\Validators\LiveHostdValidator;


class LiveHostdsController extends Controller
{

    /**
     * @var LiveHostdRepository
     */
    protected $repository;

    /**
     * @var LiveHostdValidator
     */
    protected $validator;


    public function __construct(LiveHostdRepository $repository, LiveHostdValidator $validator)
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
        $liveHostds = $this->repository->all();

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

            $response = [
                'message' => 'LiveHostd created.',
                'data'    => $liveHostd->toArray(),
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
        $liveHostd = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $liveHostd,
            ]);
        }

        return view('liveHostds.show', compact('liveHostd'));
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
     * @param  string            $id
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
                'data'    => $liveHostd->toArray(),
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
                'message' => 'LiveHostd deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'LiveHostd deleted.');
    }
}
