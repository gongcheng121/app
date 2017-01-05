<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\HongFuRepository;
use App\Model\HongFu;
use App\Validators\HongFuValidator;

/**
 * Class HongFuRepositoryEloquent
 * @package namespace App\Repositories;
 */
class HongFuRepositoryEloquent extends BaseRepository implements HongFuRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return HongFu::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return HongFuValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
