<?php namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WechatInfo extends Model {

    protected $table = 'wechat_info';
    protected $fillable = ['appid', 'secret', 'key','appname','mch_id','pay_key'];

    public function getByKey($key){
        $wechat_info  = Cache::get('wechat_info_'.$key,function() use($key){
            try{
                $expiresAt = Carbon::now()->addDay(10)->diffInMinutes();
                $wechatInfo =$this->where('key','=',$key)->firstOrFail();
                Cache::add('wechat_info_'.$key,$wechatInfo,$expiresAt);
                return  $wechatInfo;
            }catch (ModelNotFoundException $e){
                return response('Please contact the Administrator',403);
            }
        });
        return $wechat_info;
    }
}
