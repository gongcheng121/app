<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\GuangDianRepository;
use App\Model\GuangDian;
use App\Validators\GuangDianValidator;

/**
 * Class GuangDianRepositoryEloquent
 * @package namespace App\Repositories;
 */
class GuangDianRepositoryEloquent extends BaseRepository implements GuangDianRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GuangDian::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
