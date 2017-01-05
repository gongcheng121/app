<?php


Route::get('/jiangyaomengxiang/{id?}.html','Api\QiyeController@show');
Route::any('/t','HomeController@index');
Route::get('/ip','HomeController@ip');
Route::any('/counts','HomeController@counts');
Route::get('/showcounts','HomeController@showCounts');
Route::get('/qrwz/{q}','QrwzController@qrwz');
//Route::get('/connect/oauth2/authorize/','WechatOauthApiController@oauth');
Route::get('/sns/oauth2/access_token','WechatOauthApiController@token');
Route::any('/wechat', 'WechatController@serve');
Route::any('/WechatApi/{key}', 'WechatApiController@api');
Route::any('/wechat/jsdk/{key}','WechatController@jsdk');
Route::any('/wechat/card/{key}','WechatController@card');
Route::any('/wechat/auth/{key}','WechatController@auth');
Route::any('/wechat/authcheck/{key}','WechatController@authcheck');

Route::any('/connect/oauth2/authorize','OpenWeixinController@oauth2');

