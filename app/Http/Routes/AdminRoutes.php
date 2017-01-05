<?php
namespace App\Http\Routes;

use Illuminate\Routing\Router;

/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/8/9
 * Time: 11:28
 */
class AdminRoutes
{

    public function map(Router $router)
    {
        $router->group(['prefix' => 'admin', 'namespace' => 'Admin'], function ($router) {
            $router->get('/', ['as' => 'admin.home', 'uses' => 'HomeController@index']);
            $router->group(['prefix' => 'api'], function ($router) {
                $router->get('/dashboard', 'DashboardController@index');
            });
            $router->resource('baby', 'BabyVotesController');
            $router->resource('live', 'LivesController');
            $router->resource('live-hostds', 'LiveHostdsController');
            $router->resource('activity/bank', 'Activity\BankVotesController');//银行投票
            $router->resource('activity/guozhiyuan', 'Activity\GuozhiyuanVotesController');//银行投票
            $router->resource('activity/driftbottle', 'Activity\DriftBottleController');//
            $router->controllers([
                'auth' => 'Auth\AuthController',
//        'password' => 'Auth\PasswordController',
                'subway' => 'SubwayController',
                'lottery' => 'LotteryController',
                'video' => 'VideoController',
                'paraser' => 'ParaserController',
                'qiye' => 'QiyeController',
                'key' => 'KeyController',
                'command' => 'CommandController',
                'activity/video' => 'Activity\ActivityVideoController',
                'activity/zhibo' => 'Activity\ZhiboController',
                'activity/bank' => 'Activity\BankVotesController',
                'activity/guozhiyuan' => 'Activity\GuozhiyuanVotesController',
            ]);

//            $router->get('/views/{name}', function ($name) {
//                return View($name);
//            });
//            $router->any('{path?}', function () {
//                return View('admin.layouts.master');
//            })->where("path", ".+");
        });
    }
}