<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/2 0002
 * Time: 下午 1:26
 */

namespace App\Http\Controllers;


use Overtrue\Wechat\Menu;
use Overtrue\Wechat\MenuItem;

class WeixinController extends BaseController {

    public function __construct(){
        parent::__construct();
    }
    public function getIndex(){
        $appId  = 'wxddc67139cdfeec38';
        $secret = 'fa591617e6470ece1db6dfa878299369';

        $menu =new Menu($appId, $secret);



         $json = '
{"button":[{"name":"产品推荐","sub_button":[{"type":"view","name":"生命如意宝壹号","url":"http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=210693498&idx=1&sn=53fcbd73e35ddfe9ee45b4f3561ec3d4&scene=18#wechat_redirect"},{"type":"view","name":"生命福相随","url":"http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=210693747&idx=1&sn=cf9671f875a91e56ca4be60cf066bc28&scene=18#wechat_redirect"},{"type":"view","name":"生命E理财C款","url":"http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=214300932&idx=1&sn=46341f49283aea6fab2c53f39809e8b0&scene=18#wechat_redirect"},{"type":"view","name":"富德生命富赢壹号","url":"http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=214300384&idx=1&sn=869d8a3cc21893c22c18caefabbb9f86&scene=18#wechat_redirect"}]},{"name":"公司动态","sub_button":[{"type":"view","name":"靓丽新分形象大使","url":"http://www.wxhand.com/addon/Vote/WapVote/detail/token/a3b20f2e9fb75017e5287bf5890141e6/id/15028.html"},{"type":"view","name":"公司官网","url":" https://www.sino-life.com/"},{"type":"view","name":"保险商城","url":" http://shop.sino-life.com/"},{"type":"view","name":"公司介绍 ","url":"http://eqxiu.com/s/5PiTqx5Y?eqrcode=1&from=singlemessage&isappinstalled=0/"},{"type":"view","name":"精英俱乐部英雄榜","url":"http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=214720309&idx=1&sn=0fdbd2d4307ed3303df6fd967c66169a&scene=18#wechat_redirect"}]},{"name":"微服务","sub_button":[{"type":"view","name":"保全服务","url":" https://www.sino-life.com/SL_ESSO/Login.sso?loginId=20140000000000512412&loginSign=c83874cb451b977101b23b41732f631b&toURL=https://m.sino-life.com/SL_LES/mwebPos/index.do&type=2&wxUserId=20140000000000186474"},{"type":"view","name":"会员中心","url":" https://www.sino-life.com/SL_ESSO/LOGIN/member/login.html"},{"type":"view","name":"E动生命","url":" http://m.sino-life.com/app/"},{"type":"view","name":"理赔服务","url":" https://m.sino-life.com/SL_LES/weixinclaim/claimSelectPrepare.do"}]}]}';
        $button = (json_decode($json));
        foreach($button->button as $k=>$v){
            $item = new MenuItem($v->name);
            $buttons = [];
            foreach($v->sub_button as $key=>$val){
                $buttons[] = new MenuItem($val->name, $val->type, $val->url);
            }
            $item->buttons($buttons);
            $target[] = $item;
        }
    unset($target[1]);
    unset($target[2]);
//dd($target);
//        $button = new MenuItem("产品推荐");
//
//
//        $menus = array(
//            $button->buttons(array(
//                new MenuItem('生命如意宝壹号', 'view', 'http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=210693498&idx=1&sn=53fcbd73e35ddfe9ee45b4f3561ec3d4&scene=18#wechat_redirect'),
//                new MenuItem('生命福相随', 'view', 'http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=210693747&idx=1&sn=cf9671f875a91e56ca4be60cf066bc28&scene=18#wechat_redirect'),
//                new MenuItem('生命E理财C款', 'view', 'http://mp.weixin.qq.com/s?__biz=MzA5NTM1MzgzNg==&mid=214300932&idx=1&sn=46341f49283aea6fab2c53f39809e8b0&scene=18#wechat_redirect'),
//            )),
//        );

        try {
            $menu->set($target);// 请求微信服务器
            echo '设置成功！';
        } catch (\Exception $e) {
            echo '设置失败：' . $e->getMessage();
        }
    }
} 