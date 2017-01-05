<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\BabyVoteRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\BabyVoteCreateRequest;
use App\Http\Requests\BabyVoteUpdateRequest;
use App\Validators\BabyVoteValidator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class BabyVotesController extends AdminBaseController
{

    /**
     * @var BabyVoteRepository
     */
    protected $repository;

    /**
     * @var BabyVoteValidator
     */
    protected $validator;


    public function __construct(BabyVoteRepositoryEloquent $repository, BabyVoteValidator $validator)
    {
        $this->repository = $repository->skipPresenter();
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
        $babyVotes = $this->repository->paginate();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $babyVotes,
            ]);
        }
        return view('admin.babyVotes.index', compact('babyVotes'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.babyVotes.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  BabyVoteCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BabyVoteCreateRequest $request)
    {
        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $babyVote = $this->repository->create($request->all());

            $response = [
                'message' => 'BabyVote created.',
                'data'    => $babyVote->toArray(),
            ];

            if ($request->wantsJson()) {

                return response()->json($response);
            }

            return redirect('admin/baby')->with('message', $response['message']);
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
        $babyVote = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $babyVote,
            ]);
        }

        return view('babyVotes.show', compact('babyVote'));
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

        $babyVote = $this->repository->find($id);

        return view('babyVotes.edit', compact('babyVote'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  BabyVoteUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(BabyVoteUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $babyVote = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'BabyVote updated.',
                'data'    => $babyVote->toArray(),
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
                'message' => 'BabyVote deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'BabyVote deleted.');
    }
}
