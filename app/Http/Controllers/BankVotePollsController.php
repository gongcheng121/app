<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\BankVotePollCreateRequest;
use App\Http\Requests\BankVotePollUpdateRequest;
use App\Repositories\BankVotePollRepository;
use App\Validators\BankVotePollValidator;


class BankVotePollsController extends Controller
{

    /**
     * @var BankVotePollRepository
     */
    protected $repository;

    /**
     * @var BankVotePollValidator
     */
    protected $validator;


    public function __construct(BankVotePollRepository $repository, BankVotePollValidator $validator)
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
        $bankVotePolls = $this->repository->all();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $bankVotePolls,
            ]);
        }

        return view('bankVotePolls.index', compact('bankVotePolls'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('bankVotePolls.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  BankVotePollCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BankVotePollCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $bankVotePoll = $this->repository->create($request->all());

            $response = [
                'message' => 'BankVotePoll created.',
                'data'    => $bankVotePoll->toArray(),
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
        $bankVotePoll = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $bankVotePoll,
            ]);
        }

        return view('bankVotePolls.show', compact('bankVotePoll'));
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

        $bankVotePoll = $this->repository->find($id);

        return view('bankVotePolls.edit', compact('bankVotePoll'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  BankVotePollUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(BankVotePollUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $bankVotePoll = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'BankVotePoll updated.',
                'data'    => $bankVotePoll->toArray(),
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
                'message' => 'BankVotePoll deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'BankVotePoll deleted.');
    }
}
