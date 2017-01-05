<?php

namespace App\Presenters;

use App\Transformers\DriftBottleTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class DriftBottlePresenter
 *
 * @package namespace App\Presenters;
 */
class DriftBottlePresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new DriftBottleTransformer();
    }
}
