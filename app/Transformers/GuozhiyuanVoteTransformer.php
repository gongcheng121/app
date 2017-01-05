<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\GuozhiyuanVote;

/**
 * Class GuozhiyuanVoteTransformer
 * @package namespace App\Transformers;
 */
class GuozhiyuanVoteTransformer extends TransformerAbstract
{

    /**
     * Transform the \GuozhiyuanVote entity
     * @param \GuozhiyuanVote $model
     *
     * @return array
     */
    public function transform(GuozhiyuanVote $model)
    {
        return [
            'id' => (int)$model->id,

            'openid'=>$model->openid,
            'name'=>$model->name,
            'mobile'=>$model->mobile,
            'image'=>$model->image,
            'description'=>$model->description,
            'list_order'=>$model->list_order,
            'count'=>$model->count,
            'thumb_image'=>str_insert($model->image,19,'thumb_'),
            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
