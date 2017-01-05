<?php

namespace App\Model\Game;

use Illuminate\Database\Eloquent\Model;

class GamePrizeInfo extends Model
{

    protected $table = 'game_prizes_info';

    protected $fillable = [
        'game_id', 'name', 'count', 'v', 'created_at', 'updated_at',
    ];
}
