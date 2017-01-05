<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BabyVotePollRepository;
use App\Model\BabyVotePoll;
use App\Validators\BabyVotePollValidator;

/**
 * Class BabyVotePollRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BabyVotePollRepositoryEloquent extends BaseRepository implements BabyVotePollRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BabyVotePoll::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return BabyVotePollValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
