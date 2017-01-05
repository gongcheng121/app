<?php

namespace App\Repositories;

use App\Transformers\LiveHostdTransformer;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\LiveHostdRepository;
use App\Model\LiveHostd;
use App\Validators\LiveHostdValidator;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class LiveHostdRepositoryEloquent
 * @package namespace App\Repositories;
 */
class LiveHostdRepositoryEloquent extends BaseRepository implements LiveHostdRepository
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
        return LiveHostd::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return LiveHostdValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function presenter(){
        return LiveHostdTransformer::class;
    }
}
