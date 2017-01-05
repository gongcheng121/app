<?php
namespace App\Http\Routes;

use Illuminate\Routing\Router;

/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/8/9
 * Time: 11:33
 */
class ClientRoutes
{
    public function map(Router $router)
    {


        $router->get('/jiangyaomengxiang/{id?}.html', 'Api\QiyeController@show');
        $router->any('/t', 'HomeController@index');
        $router->get('/ip', 'HomeController@ip');
        $router->any('/counts', 'HomeController@counts');
        $router->get('/showcounts', 'HomeController@showCounts');
        $router->get('/qrwz/{q}', 'QrwzController@qrwz');
        $router->get('/connect/oauth2/authorize/','WechatOauthApiController@oauth');
        $router->get('/sns/oauth2/access_token', 'WechatOauthApiController@token');
        $router->any('/wechat', 'WechatController@serve');
        $router->any('/WechatApi/{key}', 'WechatApiController@api');
        $router->any('/wechat/jsdk/{key}', 'WechatController@jsdk');
        $router->any('/wechat/card/{key}', 'WechatController@card');
        $router->any('/wechat/auth/{key}', 'WechatController@auth');
        $router->any('/wechat/authcheck/{key}', 'WechatController@authcheck');

        $router->any('/connect/oauth2/authorize', 'OpenWeixinController@oauth2');

    }
}