<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\GuangDianPrizeRepository;
use App\Model\GuangDianPrize;
use App\Validators\GuangDianPrizeValidator;

/**
 * Class GuangDianPrizeRepositoryEloquent
 * @package namespace App\Repositories;
 */
class GuangDianPrizeRepositoryEloquent extends BaseRepository implements GuangDianPrizeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GuangDianPrize::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
