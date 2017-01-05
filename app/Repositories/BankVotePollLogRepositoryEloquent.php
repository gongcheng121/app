<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BankVotePollLogRepository;
use App\Model\BankVotePollLog;
use App\Validators\BankVotePollLogValidator;

/**
 * Class BankVotePollLogRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BankVotePollLogRepositoryEloquent extends BaseRepository implements BankVotePollLogRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BankVotePollLog::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
