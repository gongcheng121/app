<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BankVotePollRepository;
use App\Model\BankVotePoll;
use App\Validators\BankVotePollValidator;

/**
 * Class BankVotePollRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BankVotePollRepositoryEloquent extends BaseRepository implements BankVotePollRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BankVotePoll::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return BankVotePollValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
