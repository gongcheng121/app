<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\DriftBottle;

/**
 * Class DriftBottleTransformer
 * @package namespace App\Transformers;
 */
class DriftBottleTransformer extends TransformerAbstract
{

    /**
     * Transform the \DriftBottle entity
     * @param \DriftBottle $model
     *
     * @return array
     */
    public function transform(DriftBottle $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
