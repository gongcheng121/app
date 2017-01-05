<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Model\BabyVote;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Class BabyVoteTransformer
 * @package namespace App\Transformers;
 */
class BabyVoteTransformer extends TransformerAbstract
{

    /**
     * Transform the \BabyVote entity
     * @param \BabyVote $model
     *
     * @return array
     */
    public function transform(BabyVote $model)
    {
        return [
            'id'         => (int) $model->id,
            'type'=>$model->type,
            'title'=>$model->title,
            'video_url'=>$model->video_url,
            'images'=>$model->images,
            'description'=>$model->description,
            'qrcode'=>base64_encode(QrCode::format('png')->size(100)->errorCorrection('H')->generate(url('baby/detail/'.$model->id))),
            /* place your other model properties here */
            'poll'=>$model->poll=='' ?  ['count'=>0,'vote_id'=>$model->id] :$model->poll ,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
