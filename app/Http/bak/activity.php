<?php


Route::any('/redpack',function(){
    return redirect('http://app.iyaxin.com/nottime.html');
});
Route::get('/github/theme/backend/list',function(\Illuminate\Support\Facades\Storage $storage){
    $backendfiles = Storage::disk('public')->directories('/github/theme/backend/');
    foreach($backendfiles as $v){
        $v = url($v);
        echo "<a href='{$v}'>{$v}</a></br>";
    }
});
Route::get('key/card','KeyController@card');
Route::post('key/idcard','KeyController@idcard');
Route::resource('live','LivesController');
Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
    'gactivity/gallery'=>'Activity\GalleryController',
    'auto' =>'AutoController',
    'chengxu/web/' =>'BaomingController',
    'subway' =>'SubwayController',
    'Other' =>'OtherController',
    'weixin'=>'WeixinController',
    'game/xuereng/api'=>'Game\XuerengController',//游戏雪人
    'game/kanjia'=>"WechatKanjiaController",//微信砍价功能
//    'game/helpcard'=>'Game\HelpcardController',//旅游局卡券分享
//    'game/laohuji'=>'Game\LaohujiController',//旅游局卡券老虎机游戏
    'game'=>'WechatGameController',//摇一摇抽奖
//    'pingtu'=>'WechatGameController',//摇一摇抽奖
    'wechatPack'=>"WechatPackController",//发红包

    'cb'=>'WelcomeController',
    'api/news'=>'NewsController',
    'api/qiye'=>'Api\QiyeController',
    'api/lottery'=>'Api\WechatMemberVerify',
    'api'=>'ApiController',
    'key/{key}'=>'KeyController',
//    'pingtu'=>"PingtuController",
    'scene2'=>'Activity\SceneController',
    'knowledge'=>'Activity\KnowledgeController',
    'activity/video'=>'Activity\VideoPollController',//视频投票
    'emei'=>'Activity\EmeiController',//峨眉山抽奖活动
    'zhibo'=>'ZhiboController',//直播界面,
    'drift'=>'DriftBottlesController',
    'bank'=>'BankVotesController',//银行前台
    'guozhiyuan'=>'GuozhiyuanVotesController',//果汁源
    'hongfu'=>'HongFusController',//鸿福

]);
Route::any('baby/upload','BabyInfosController@upload');//视频上传
Route::resource('baby', 'BabyInfosController');
Route::resource('drift', 'DriftBottlesController');//漂流瓶
Route::resource('bank', 'BankVotesController');//银行投票
Route::resource('guozhiyuan', 'GuozhiyuanVotesController');//果汁源
Route::resource('hongfu', 'HongFusController');//鸿福
Route::get('/views/{name}', function ($name) {
    return View($name);
});