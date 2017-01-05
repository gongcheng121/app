<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SubwayStore extends Model {

	//
    protected $table = 'subway_store';
    protected $fillable = ['name', 'description', 'station_id','img','location','link','style'];
    public function station(){
        return $this->hasOne('App\Model\SubwayStation','id','station_id');
    }
}
