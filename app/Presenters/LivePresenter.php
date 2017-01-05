<?php

namespace App\Presenters;

use App\Transformers\LiveTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class LivePresenter
 *
 * @package namespace App\Presenters;
 */
class LivePresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new LiveTransformer();
    }
}
