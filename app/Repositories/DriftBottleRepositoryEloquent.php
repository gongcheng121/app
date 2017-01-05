<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\DriftBottleRepository;
use App\Model\DriftBottle;
use App\Validators\DriftBottleValidator;

/**
 * Class DriftBottleRepositoryEloquent
 * @package namespace App\Repositories;
 */
class DriftBottleRepositoryEloquent extends BaseRepository implements DriftBottleRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DriftBottle::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return DriftBottleValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
