<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\BankVote;

/**
 * Class BankVoteTransformer
 * @package namespace App\Transformers;
 */
class BankVoteTransformer extends TransformerAbstract
{

    /**
     * Transform the \BankVote entity
     * @param \BankVote $model
     *
     * @return array
     */
    public function transform(BankVote $model)
    {

        return [
            'id'         => (int) $model->id,
            'name'=>$model->name,
            'mobile'=>$model->mobile,
            'image'=>$model->image,
            'title'=>$model->title,
            /* place your other model properties here */
            'count'=>isset($model->vote_count) ? $model->vote_count->count : 0,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
