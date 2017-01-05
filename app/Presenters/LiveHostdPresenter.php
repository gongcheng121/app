<?php

namespace App\Presenters;

use App\Transformers\LiveHostdTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class LiveHostdPresenter
 *
 * @package namespace App\Presenters;
 */
class LiveHostdPresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new LiveHostdTransformer();
    }
}
