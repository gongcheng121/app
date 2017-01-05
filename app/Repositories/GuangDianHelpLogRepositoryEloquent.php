<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\GuangDianHelpLogRepository;
use App\Model\GuangDianHelpLog;
use App\Validators\GuangDianHelpLogValidator;

/**
 * Class GuangDianHelpLogRepositoryEloquent
 * @package namespace App\Repositories;
 */
class GuangDianHelpLogRepositoryEloquent extends BaseRepository implements GuangDianHelpLogRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GuangDianHelpLog::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
