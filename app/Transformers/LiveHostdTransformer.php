<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\LiveHostd;

/**
 * Class LiveHostdTransformer
 * @package namespace App\Transformers;
 */
class LiveHostdTransformer extends TransformerAbstract
{

    /**
     * Transform the \LiveHostd entity
     * @param \LiveHostd $model
     *
     * @return array
     */
    public function transform(LiveHostd $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
