<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\LiveRepository;
use App\Model\Live;
use App\Validators\LiveValidator;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class LiveRepositoryEloquent
 * @package namespace App\Repositories;
 */
class LiveRepositoryEloquent extends BaseRepository implements LiveRepository
{
    use CacheableRepository;
    protected $cacheMinutes = 90;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Live::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return LiveValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
