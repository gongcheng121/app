<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 上午 11:29
 */

namespace App\Model;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class QiyeType extends Model{


    protected $table = 'qiye_type';
    protected $fillable =['id','type_name','listorder','description'];


    public function Category(){
        return $this->hasMany('App\Model\QiyeCategory','type_id','id');
    }
    public function cell(){
        return $this->hasMany('App\Model\QiyeCategory','type_id','id');
    }

    public function getTypes($id,$fresh=false){
        if($fresh){
            $this->cleanCache($id);
        }
        return  Cache::get('cache_type_'.$id,function() use ($id){
                $result = $this->find($id)->toArray();
                $expiresAt = Carbon::now()->addMinutes(10);
                Cache::add('cache_type_'.$id,$result,$expiresAt);
            return $result;
        });
    }

    public function cleanCache($id){
        Cache::forget('cache_type_'.$id);
    }
}