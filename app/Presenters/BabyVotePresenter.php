<?php

namespace App\Presenters;

use App\Transformers\BabyVoteTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class BabyVotePresenter
 *
 * @package namespace App\Presenters;
 */
class BabyVotePresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new BabyVoteTransformer();
    }
}
