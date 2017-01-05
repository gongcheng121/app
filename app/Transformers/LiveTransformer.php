<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\Live;

/**
 * Class LiveTransformer
 * @package namespace App\Transformers;
 */
class LiveTransformer extends TransformerAbstract
{

    /**
     * Transform the \Live entity
     * @param \Live $model
     *
     * @return array
     */
    public function transform(Live $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
