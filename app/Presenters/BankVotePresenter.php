<?php

namespace App\Presenters;

use App\Transformers\BankVoteTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class BankVotePresenter
 *
 * @package namespace App\Presenters;
 */
class BankVotePresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new BankVoteTransformer();
    }
}
