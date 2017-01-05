<?php

//后台
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::get('/', ['as' => 'admin.home', 'uses' => 'HomeController@index']);
    Route::group(['prefix' => 'api'], function () {
        Route::get('/dashboard', 'DashboardController@index');
    });
    Route::resource('baby', 'BabyVotesController');
    Route::resource('live', 'LivesController');
    Route::resource('live-hostds', 'LiveHostdsController');
    Route::resource('activity/bank', 'Activity\BankVotesController');//银行投票
    Route::resource('activity/guozhiyuan', 'Activity\GuozhiyuanVotesController');//银行投票
    Route::resource('activity/driftbottle', 'Activity\DriftBottleController');//
    Route::controllers([
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

    Route::get('/views/{name}', function ($name) {
        return View($name);
    });
    Route::any('{path?}', function () {
        return View('admin.layouts.master');
    })->where("path", ".+");
});