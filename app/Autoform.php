<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Autoform extends Model {

	//
    protected $table = 'autoforms';

    protected $fillable = ['name', 'mobile', 'chexin','jinxiaoshang','log'];

}
