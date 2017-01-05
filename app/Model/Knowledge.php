<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/3/17
 * Time: 14:05
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model {
    protected $table="knowledge_info";

    protected $fillable = array('openid', 'key', 'answer','fen','status');
} 