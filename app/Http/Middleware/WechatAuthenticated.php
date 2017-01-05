<?php namespace App\Http\Middleware;

//use App\Commands\SendMessage;
use App\Model\WechatInfo;
use App\Model\WechatMember;
use Carbon\Carbon;
use Closure;
use EasyWeChat\Core\Exceptions\InvalidArgumentException;
use EasyWeChat\Foundation\Application;
use Illuminate\Auth\Guard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Exception;

class WechatAuthenticated
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        $parameters = $request->route()->parametersWithoutNulls();
        $key = isset($parameters['one']) ? $parameters['one'] : (($request->key) ? $request->key : '');
        if (!$key) {
            return 'key is missing please connect administrator';
        }
//        $request->session()->forget('wechat_user_' . $key);

        if (env('APP_DEBUG') == true || $request->wantsJson()) {
            if (!Session::get('wechat_user_' . $key) || $request->fresh) {
                $wechat_member = WechatMember::where('openid', '=', 'oVDTUjrDGBL9b6thzI5fWxC-f__M')->where('key', '=', $key)->first();
                Session::set('wechat_user_' . $key, $wechat_member);
            }
        }


        if (Session::get('wechat_user_' . $key)) {
            return $next($request);
        } else {
            $request->merge(['key' => $key]);
            $wechat_info = Cache::get('wechat_info_' . $key, function () use ($key) {
                try {
                    $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
                    $wechatInfo = WechatInfo::where('key', '=', $key)->firstOrFail();
                    Cache::add('wechat_info_' . $key, $wechatInfo, $expiresAt);
                    return $wechatInfo;
                } catch (ModelNotFoundException $e) {
                    return null;
                }
            });
            if (!$wechat_info) return response('Please contact the Administrator', 403);
            $appId = $wechat_info['appid'];
            $secret = $wechat_info['secret'];
            $config = [
                'app_id' => $appId,
                'secret' => $secret,
                'oauth' => [
                    'scopes' => ['snsapi_userinfo'],
                    'callback' => URL::full()
                ],
            ];
            $app = new Application($config);
            $oauth = $app->oauth;
            if ($request->code && $request->state) {
                try {

                    $user = $oauth->user()->toArray()['original'];
                } catch (\InvalidArgumentException $e) {
                    return redirect($request->fullUrlWithQuery(['code'=>'']));//清除code 并重新获取授权
//                    return 'please closed and try again';
                }


                $wechat_member = WechatMember::where('openid', '=', $user['openid'])->where('key', '=', $key)->get();
                if (!$wechat_member->toArray()) {
                    $data = $user;
                    $data['key'] = $key;
                    unset($data['privilege']);
                    unset($data['language']);
                    WechatMember::create($data);
                }
                Session::set('wechat_user_' . $key, $user);
                return $next($request);
            }
            return $oauth->redirect();
        }

        return $next($request);
    }

}
