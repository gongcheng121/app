<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\HongFuHelpLogRepository;
use App\Model\HongFuHelpLog;
use App\Validators\HongFuHelpLogValidator;

/**
 * Class HongFuHelpLogRepositoryEloquent
 * @package namespace App\Repositories;
 */
class HongFuHelpLogRepositoryEloquent extends BaseRepository implements HongFuHelpLogRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return HongFuHelpLog::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
