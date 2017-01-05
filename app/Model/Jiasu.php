<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15-9-8
 * Time: 下午9:05
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Jiasu extends Model{
    protected $table = 'game_jiasu';
    protected $fillable =['id','name','mobile','content'];
}