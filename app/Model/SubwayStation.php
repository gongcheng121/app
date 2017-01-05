<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SubwayStation extends Model {

	//
    protected $table = 'subway_station';
    protected $fillable = ['name', 'description', 'icon'];
    public function store(){
       return $this->hasMany('App\Model\SubwayStore','station_id','id');
    }
}
