<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BabyInfoRepository;
use App\Model\BabyInfo;
use App\Validators\BabyInfoValidator;

/**
 * Class BabyInfoRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BabyInfoRepositoryEloquent extends BaseRepository implements BabyInfoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BabyInfo::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return BabyInfoValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
