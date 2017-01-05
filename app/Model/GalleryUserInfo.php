<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28 0028
 * Time: 下午 12:52
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class GalleryUserInfo extends Model{
    protected $table="gallery_user_info";
    protected $fillable = ['user_name','mobile','openid','wish_message','list_order','images','status','count'];

    public function WechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 