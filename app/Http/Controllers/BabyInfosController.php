<?php

namespace App\Http\Controllers;

use App\Model\BabyVideos;
use App\Repositories\BabyInfoRepositoryEloquent;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\BabyInfoCreateRequest;
use App\Http\Requests\BabyInfoUpdateRequest;
use App\Repositories\BabyInfoRepository;
use App\Validators\BabyInfoValidator;
use zgldh\UploadManager\UploadManager;


class BabyInfosController extends BaseController
{

    /**
     * @var BabyInfoRepository
     */
    protected $repository;

    /**
     * @var BabyInfoValidator
     */
    protected $validator;


    public function __construct(BabyInfoRepositoryEloquent $repository, BabyInfoValidator $validator)
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
        $babyInfos = $this->repository->all();

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $babyInfos,
            ]);
        }

        $field =  ['baby_name', 'baby_words', 'birthday', 'father_name', 'father_mobile', 'father_qq', 'father_wechat', 'mother_name', 'mother_mobile', 'mother_qq', 'mother_wechat'];

        return view('babyInfos.index', compact('babyInfos','field'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('babyInfos.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  BabyInfoCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BabyInfoCreateRequest $request)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $babyInfo = $this->repository->create($request->all());

            if($request->video){
                $video  = new BabyVideos($request->video);
                $babyInfo->video()->save($video);
            }
            $response = [
                'message' => '添加成功,请等待管理人员确认您的信息',
                'data'    => $babyInfo->toArray(),
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
        $babyInfo = $this->repository->find($id);

        if (request()->wantsJson()) {

            return response()->json([
                'data' => $babyInfo,
            ]);
        }

        return view('babyInfos.show', compact('babyInfo'));
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

        $babyInfo = $this->repository->find($id);

        return view('babyInfos.edit', compact('babyInfo'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  BabyInfoUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(BabyInfoUpdateRequest $request, $id)
    {

        try {

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $babyInfo = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'BabyInfo updated.',
                'data'    => $babyInfo->toArray(),
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
                'message' => 'BabyInfo deleted.',
                'deleted' => $deleted,
            ]);
        }

        return redirect()->back()->with('message', 'BabyInfo deleted.');
    }

    public function upload(Request $request){
        $file = $request->file('file');
        $manager = UploadManager::getInstance();
        $upload = $manager->upload($file);
        $upload->save();
        return $upload;
    }
}
