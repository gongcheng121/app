<?php

namespace App\Presenters;

use App\Transformers\GuozhiyuanVoteTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class GuozhiyuanVotePresenter
 *
 * @package namespace App\Presenters;
 */
class GuozhiyuanVotePresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new GuozhiyuanVoteTransformer();
    }
}
