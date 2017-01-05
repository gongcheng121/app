<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\DaheVoteRepository;
use App\Model\DaheVote;
use App\Validators\DaheVoteValidator;

/**
 * Class DaheVoteRepositoryEloquent
 * @package namespace App\Repositories;
 */
class DaheVoteRepositoryEloquent extends BaseRepository implements DaheVoteRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DaheVote::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return DaheVoteValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
