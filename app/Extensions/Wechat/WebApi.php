<?php

namespace App\Extensions\Wechat;

use App\Jobs\WechatRobootMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\File;
use Illuminate\Cache\RateLimiter;
use Psr\Http\Message\ResponseInterface;

class WebApi
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * client options
     * @var array
     */
    protected $clientOptions = [];

    /**
     * @var array
     */
    protected $loginInfo = []; // ['skey', 'wxsid', 'wxuin', 'pass_ticket']

    /**
     * @var SyncKey
     */
    protected $syncKey;

    /**
     * login user
     * @var array
     */
    protected $user = [];

    /**
     * user contact
     * @var Contact
     */
    protected $contact;

    /**
     * request limit
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * max request attempts in 0.5 min
     * @var int
     */
    protected $maxAttempts = 10;

    /**
     * increment file index
     * @var int
     */
    protected $fileIndex = 0;

    /**
     * WebApi constructor.
     * @param array $clientOptions client options
     * @param array $options other options
     */
    public function __construct($clientOptions = [], $options = [])
    {
        // important, don't allow auto redirect
        $this->clientOptions = [
                'allow_redirects' => false,
                'http_errors' => false,
                'timeout' => config('wechat.web_api.connect_timeout', 30),
                'debug' => config('wechat.debug', false),
            ] + array_replace_recursive([
                'cookies' => new CookieJar(),
            ], $clientOptions);

        $this->maxAttempts = config('wechat.web_api.max_attempts', 10);

        // in fact, if set login cookies, the loginInfo can be got from it,
        // and user can be restored using webwxinit api
        $this->client = new Client($this->clientOptions);
        // for quick start
        $this->loginInfo = array_get($options, 'loginInfo', []);
        $this->user = array_get($options, 'user', []);
        $this->fileIndex = array_get($options, 'fileIndex', 0);

        $this->limiter = app(RateLimiter::class);
        $this->limiter->clear('synccheck');
        $this->limiter->clear('wechat_login');
    }

    public function run()
    {
        $has_login = ! empty($this->loginInfo);

        while (true) {
            try {
                if (! $has_login) {
                    // wait until user login
                    do {
                        if ($this->tooManyAttempts('wechat_login')) {
                            Cache::forget('wechat_login_uuid');
                            sleep(5);
                            break;
                        }

                        // regenerate uuid when time exceed 5 min
                        if (($uuid = Cache::get('wechat_login_uuid')) == null) {
                            $uuid = $this->getUUID();
                            Storage::put('wechat/qrcode.png', file_get_contents($this->getQRCode($uuid)));
                            Cache::put('wechat_login_uuid', $uuid, 5);
                        }

                    } while (! $this->loginListen($uuid));
                }

                while ($this->reload());

            } catch (Exception $e) {
                // need relogin
                $has_login = false;
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * reload the whole page (first init when login or when too many requests at one time)
     * @return bool $need_reload
     * @throws Exception
     */
    protected function reload()
    {
        $need_reload = false;

        $this->loginInit();
        $this->statusNotify();

        // save state to file
        $this->saveState();

        // init contact
        static $init_contact = false;
        if (! $init_contact) {
            try {
                $this->initContact();
                $this->initBatchGroupMembers();
                $init_contact = true;
            } catch (Exception $e) {
                Log::error('get contact error' . $e->getMessage());
                return true;
            }
        }

        // message listen
        while (true) {

            if ($this->tooManyAttempts('synccheck')) {
                sleep(5);
                $need_reload = true;
                break;
            }

            try {
                $check_status = $this->syncCheck();
                Log::info('synccheck status ' . SyncCheckStatus::getStatus($check_status));
            } catch (Exception $e) {
                // when synccheck error, reload page
                $need_reload = true;
                break;
            }

            switch ($check_status) {
                case SyncCheckStatus::NewMessage:
                    Log::info('new message come');
                    try {
                        $detail = $this->syncDetail();
                        $has_new = false;
                        if ($detail['AddMsgCount'] > 0) {
                            $has_new = true;
                            $this->receiveMessage($detail['AddMsgList']);
                        }

                        if ($detail['DelContactCount'] > 0) {
                            Log::info('contact delete', $detail['DelContactList']);
                        }

                        if ($detail['ModContactCount'] > 0) {
                            Log::info('contact changed');
                            foreach ($detail['ModContactList'] as $item) {
                                if ($this->contact->isGroup($item['UserName'])) {
                                    $this->contact->setGroupMembers($item['UserName'], $item['MemberList'], array_except($item, 'MemberList'));
                                } else {
                                    $this->contact->addContact([$item]);
                                }
                            }
                        }

                        if (! $has_new) {
                            $this->loginInit();
                        }
                    } catch (Exception $e) {
                        Log::error('get new message error ' . $e->getMessage());
                    }
                    break;

                case SyncCheckStatus::Normal:
                    Log::info('no message');
                    break;

                case SyncCheckStatus::Fail:
                    throw new Exception('lost user, please relogin');
                    break;

                default:
                    // unknown status
                    Log::info('unknown status ' . SyncCheckStatus::getStatus($check_status));
                    $this->loginInit();
                    break;
            }
        }

        return $need_reload;
    }

    /**
     * check if has too many request in 0.5 min
     * @param $key
     * @return bool
     */
    protected function tooManyAttempts($key)
    {
        if ($this->limiter->hit($key) && $this->limiter->tooManyAttempts($key, $this->maxAttempts, 0.5)) {
            Log::warning('too many request in 0.5 min, sleep some seconds and reload page');
            $this->limiter->clear($key);
            return true;
        }

        return false;
    }

    /**
     * base request
     * @param $method
     * @param string $uri request url
     * @param array $options request options
     * @param int $retry retry_times
     * @return string plain/text
     * @throws Exception
     */
    protected function request($method, $uri, array $options = [], $retry = 10)
    {
        $default = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
            ],
        ];

        $options = array_replace_recursive($default, $options);

        // enable retry
        while ($retry--) {
            try {
                $response = $this->client->request($method, $uri, $options);

            } catch (Exception $e) {
                Log::warning('network error ' . $e->getMessage());
                sleep(5);
                continue;
            }

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
                if ($retry > 0) {
                    sleep(5);
                    continue;
                }
                Log::error('request error after tries',  [
                    'url' => $uri,
                    'code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                ]);
                throw new Exception('request error after tries');
            } else {
                return $response->getBody()->getContents();
            }
        }
    }

    /**
     * get current timestamp with milliseconds
     * @return int
     */
    protected function getTimeStamp()
    {
        return intval(microtime(true) * 1000);
    }

    /**
     * simulate js ~new Date
     * 当前时间取反 (获取getTimeStamp低32位数据,然后去反操作)
     * @return int
     */
    protected function getReverseTimeStamp()
    {
        $timestamp = $this->getTimeStamp();
        return 0xFFFFFFFF + (($timestamp >> 32 << 32) - $timestamp);
    }

    /**
     * get msgId when sending msg
     * @return string
     */
    protected function getClientMessageID()
    {
        return $this->getTimeStamp() . '' . random_int(1000, 9999);
    }

    /**
     * get upload file id
     * @return string
     */
    protected function getFileID()
    {
        return 'WU_FILE_' . $this->fileIndex++;
    }

    /**
     * random device id
     * @return string
     */
    protected function getDeviceId()
    {
        return 'e' . rand(10000000, 99999999) . rand(1000000, 9999999);
    }

    /**
     * get cookies
     * @param null $name
     * @return mixed
     */
    protected function getCookies($name = null)
    {
        $cookiejar = $this->client->getConfig('cookies');
        $cookies = $cookiejar->toArray();
        if ($name === null) {
            return $cookies;
        }

        $cookie = array_first($cookies, function ($cookie) use ($name) {
            return $cookie['Name'] == $name;
        }, []);

        return array_get($cookie, 'Value');
    }

    protected function getBaseRequest()
    {
        return [
            'DeviceID' => $this->getDeviceId(),
            'Sid' => $this->loginInfo['wxsid'],
            'Skey' => $this->loginInfo['skey'],
            'Uin' => $this->loginInfo['wxuin'],
        ];
    }

    /**
     * return login uuid
     * @return string uuid
     * @throws Exception
     */
    public function getUUID()
    {
        $url = 'https://login.weixin.qq.com/jslogin';
        $response = $this->request('GET', $url, [
            'query' => [
                'appid' => 'wx782c26e4c19acffb',
                'fun' => 'new',
                'lang' => 'zh_CN',
                '_' => $this->getTimeStamp(),
            ]
        ]);

        preg_match('|window.QRLogin.code = (\d+); window.QRLogin.uuid = "(\S+?)";|', $response, $matches);

        if (empty($matches) || count($matches) != 3 || intval($matches[1]) != 200) {
            throw new Exception('get uuid parse error');
        }

        $uuid = $matches[2];
        Log::info('get new login uuid ' . $uuid);

        return $uuid;
    }

    /**
     * get login user
     * @param null $attributes
     * @return array|mixed
     * @throws Exception
     */
    public function getLoginUser($attributes = null)
    {
        if (empty($this->user)) {
            $this->loginInit();
        }

        if ($attributes) {
            if (is_array($attributes)) {
                return array_only($this->user, $attributes);
            } else {
                return array_get($this->user, $attributes);
            }
        }

        return $this->user;
    }

    /**
     * get contact
     * @param string|null $userName
     * @param string|array|null $attributes
     * @return Contact|array|mixed
     * @throws Exception
     */
    public function getContact($userName = null, $attributes = null)
    {
        if (empty($this->contact)) {
            $this->initContact();
            $this->initBatchGroupMembers();
        }

        if ($userName) {
            $info = $this->contact->getUser($userName, $attributes);

            if (empty($info)) {
                if ($this->contact->isGroup($userName)) {
                    $this->initBatchGroupMembers([$userName]);
                } else {
                    $this->initContact();
                }
                $info = $this->contact->getUser($userName, $attributes);
            }

            return $info;
        }

        return $this->contact;
    }

    /**
     * get login qrcode link
     * @param $uuid
     * @return string
     */
    public function getQRCode($uuid)
    {
        Log::info('get qrcode link');
        return 'https://login.weixin.qq.com/qrcode/' . $uuid;
    }

    /**
     * listening user to login
     * @param $uuid
     * @return boolean is_success
     * @throws Exception
     */
    public function loginListen($uuid)
    {
        Log::info('listening user scan qrcode to login');
        $url = 'https://login.wx8.qq.com/cgi-bin/mmwebwx-bin/login';
        $response = $this->request('GET', $url, [
            'query' => [
                'uuid' => $uuid,
                'tip' => 0,
                '_' => $this->getTimeStamp(),
            ]
        ]);

        preg_match('|window.code=(\d+);|', $response, $matches);

        if (empty($matches) || count($matches) != 2) {
            return false;
        }

        $code = intval($matches[1]);

        if ($code != 200) {
            return false;
        }

        preg_match('|window.redirect_uri="(\S+?)";|', $response, $matches);
        if (empty($matches) || count($matches) != 2) {
            throw new Exception('login success parse error');
        }

        return $this->loginConfirm($matches[1]);
    }

    public function loginConfirm($redirect_uri)
    {
        Log::info('login confirm when user confirm login');
        $response = $this->request('GET', $redirect_uri);

        $info = simplexml_load_string($response);
        if ($info && ($info = (array)$info) && $info['ret'] == 0) {
            $this->loginInfo = array_only($info, ['skey', 'wxsid', 'wxuin', 'pass_ticket']);
            Log::info('user login success', $this->loginInfo);
            return true;
        }
        return false;
    }

    public function loginInit()
    {
        Log::info('login init');
        $fail_times = 0;

        while (true) {
            $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxinit';

            $response = $this->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'query' => [
                    'r' => $this->getReverseTimeStamp(),
                    'pass_ticket' => $this->loginInfo['pass_ticket'],
                ],
                'body' => json_encode([
                    'BaseRequest' => $this->getBaseRequest(),
                ])
            ]);

            $content = json_decode($response, true);
            if (! $content && array_get($content, 'BaseResponse.Ret') !== 0) {
                throw new Exception('webwxinit fail');
            }

            $this->loginInfo['skey'] = empty($content['Skey']) ? $this->loginInfo['skey'] : $content['Skey'];
            $this->syncKey = new SyncKey(array_get($content, 'SyncKey', []));

            if (array_get($content, 'User.Uin', -1) == 0) {
                $fail_times++;
                if ($fail_times > $this->maxAttempts) {
                    Cache::forget('wechat_login_uuid');
                    Log::error('cant not get uesr info, please relogin');
                    throw new Exception('cant not get uesr info, please relogin');
                }
                Log::warning('get user info error, retry');
                sleep(5);
                continue;
            }

            $this->user = array_get($content, 'User', []);
            Log::info('success get user info', $this->user);

            return $content;
        }
    }

    public function statusNotify()
    {
        Log::info('status notify');
        $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxstatusnotify';
        $response = $this->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
            'body' => json_encode(
                [
                    'BaseRequest' => $this->getBaseRequest(),
                    'ClientMsgId' => $this->getTimeStamp(),
                    'Code' => 3,
                    'FromUserName' => array_get($this->user, 'UserName'),
                    'ToUserName' => array_get($this->user, 'UserName'),
                ]
            )
        ]);

        $content = json_decode($response, true);

        if (! $content && array_get($content, 'BaseResponse.Ret') !== 0) {
            throw new Exception('statusnotify fail');
        }

        return $content;
    }

    /**
     * syncCheck
     * @return int check status
     * @throws Exception
     */
    public function syncCheck()
    {
        Log::info('sync check');
        $url = 'https://webpush.wx8.qq.com/cgi-bin/mmwebwx-bin/synccheck';
        $response = $this->request('GET', $url, [
            'query' => [
                '_' => $this->getTimeStamp(),
                'r' => $this->getTimeStamp(),
                'skey' => $this->loginInfo['skey'],
                'sid' => $this->loginInfo['wxsid'],
                'uin' => $this->loginInfo['wxuin'],
                'deviceid' => $this->getDeviceId(),
                'synckey' => $this->syncKey->toString(),
            ]
        ]);
        preg_match('|window.synccheck={retcode:"(\d+)",selector:"(\d+)"}|', $response, $matches);
        if (empty($matches) || count($matches) != 3) {
            throw new Exception('synccheck parse error');
        }

        $retcode = intval($matches[1]);
        $selector = intval($matches[2]);

        if ($retcode !== 0) {
            return SyncCheckStatus::Fail;
        }

        if ($selector == 0) {
            return SyncCheckStatus::Normal;
        } else if ($selector == 2) {
            return SyncCheckStatus::NewMessage;
        } else if ($selector == 7) {
            return SyncCheckStatus::NewJoin;
        } else {
            Log::warning('unrecognized synccheck selector', compact('retcode', 'selector'));
            return SyncCheckStatus::Unknown;
        }
    }

    /**
     * get detail when the method syncCheck got new message
     * @return mixed
     * @throws Exception
     */
    public function syncDetail()
    {
        Log::info('get detail');
        $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxsync';

        $response = $this->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
           'query' => [
               'skey' => $this->loginInfo['skey'],
               'sid' => $this->loginInfo['wxsid'],
               'lang' => 'zh_CN',
           ],
           'body' => json_encode(
               [
                   'BaseRequest' => $this->getBaseRequest(),
                   'SyncKey' => $this->syncKey->getData(),
                   'rr' => $this->getReverseTimeStamp(),
               ]
           )
        ]);

        $content = json_decode($response, true);

        if (array_get($content, 'BaseResponse.Ret') !== 0) {
            throw new Exception('webwxsync error');
        }

        $this->syncKey->refresh(array_get($content, 'SyncKey'));

        return $content;
    }

    /**
     * init user all contact
     * @throws Exception
     */
    public function initContact()
    {
        Log::info('init contact');
        $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxgetcontact';
        $response = $this->request('GET', $url, [
            'query' => [
                'lang' => 'zh_CN',
                'r' => $this->getTimeStamp(),
                'seq' => 0,
                'skey' => $this->loginInfo['skey'],
            ],
        ]);

        $content = json_decode($response, true);

        if (array_get($content, 'BaseResponse.Ret') !== 0) {
            throw new Exception('getcontact error');
        }

        $members = array_get($content, 'MemberList', []);
        $this->contact = $this->contact ?: new Contact();
        $this->contact->addContact($members);
    }

    /**
     * init group members by chunk
     * @param array $groupNames
     * @throws Exception
     */
    public function initBatchGroupMembers($groupNames = [])
    {
        Log::info('init group members');
        $chunk_size = 50;
        $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxbatchgetcontact';

        if (! $groupNames) {
            $groupNames = array_keys($this->contact->getGroups());
        }

        foreach(array_chunk($groupNames, $chunk_size) as $group_names) {
            $response = $this->request('POST', $url, [
                'query' => [
                    'lang' => 'zh_CN',
                    'r' => $this->getTimeStamp(),
                    'type' => 'ex',
                ],
                'body' => json_encode(
                    [
                        'BaseRequest' => $this->getBaseRequest(),
                        'Count' => count($group_names),
                        'List' => array_map(function ($group_name) {
                            return ['UserName' => $group_name, 'EncryChatRoomId' => 0];
                        }, $group_names),
                    ]
                )
            ]);

            $content = json_decode($response, true);

            if (array_get($content, 'BaseResponse.Ret') !== 0) {
                throw new Exception('getcontact error');
            }
            foreach(array_get($content, 'ContactList', []) as $group_list) {
                $this->contact->setGroupMembers($group_list['UserName'], $group_list['MemberList'],
                    array_except($group_list, 'MemberList'));
            }
        }
    }

    /**
     * receive message and fire event
     * @param $messages
     */
    public function receiveMessage($messages)
    {
        foreach ($messages as $message) {
            $value = '';
            switch ($message['MsgType']) {

                case MessageType::Image:
                case MessageType::Voice:
                case MessageType::Video:
                    $value = $this->downloadMedia($message);
                    break;

                case MessageType::Init:
                    // discard
                    return;
                    break;

                case MessageType::Text:
                case MessageType::LinkShare:

                    // attachment
                    if (array_get($message, 'FileSize', 0) > 0) {
                        $message['MsgType'] = MessageType::Attachment;
                        $value = $this->downloadAttachment($message);
                    }
                    // do nothing
                    break;

                case MessageType::Emotion:

                    if (! empty($message['Content'])) {
                        $xml = htmlspecialchars_decode($message['Content']);
                        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
                        $data = json_decode(json_encode($xml), true);
                        if (array_get($data, 'emoji.@attributes.cdnurl')) {
                            $value = $this->downloadEmotion($data);
                        }
                    }
                    break;

                default:
                    Log::info('other message type', $message);
                    break;
            }

            Log::info('success get new message', [
                'from' => $this->getContact($message['FromUserName'], 'NickName'),
                'type' => MessageType::getType($message['MsgType']),
                'value' => $value,
                'raw_content' => $message,
            ]);

            // process message job
            $from = $this->getContact($message['FromUserName']);
            $to = $this->getContact($message['ToUserName']);
            if ($this->contact->isGroup($message['FromUserName'])) {
                preg_match('|^(@[\w]+):<br/>|', $message['Content'], $matches);
                if ($matches && count($matches) == 2) {
                    $to = $this->getContact($matches[1]);
                    $message['Content'] = substr($message['Content'], strlen($matches[0]));
                }
            }
            try {
                $job = (new WechatRobootMessage($message['MsgType'], $from, $to, $value, $message))
                    ->onConnection('redis')
                    ->onQueue('wechat');
                dispatch($job);
            } catch (Exception $e) {
                Log::error('can not push message in job ' . $e->getMessage());
            }
        }
    }

    /**
     * download media message, eg, image,voice,video
     * @param $message
     * @return bool|string
     * @throws Exception
     */
    public function downloadMedia($message)
    {
        Log::info('downloadMedia', $message);
        switch ($message['MsgType']) {
            case MessageType::Image:
                $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxgetmsgimg';
                $suffix = 'jpg';
                $path = 'wechat/image/' . $message['MsgId'];
                break;

            case MessageType::Voice:
                $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxgetvoice';
                $suffix = 'mp3';
                $path = 'wechat/voice/' . $message['MsgId'];
                break;

            default:
                return false;
                break;
        }

        $data = $this->request('GET', $url, [
            'query' => [
                'msgid' => $message['MsgId'],
                'skey' => $this->loginInfo['skey'],
            ],
            'on_headers' => function (ResponseInterface $response) use (& $suffix) {
                $suffix = last(explode('/', $response->getHeaderLine('Content-Type')));
            }
        ]);

        $path .= '.' . $suffix;
        Storage::put($path, $data);

        return $path;
    }

    /**
     * download attachment message
     * @param $message
     * @return bool|string
     * @throws Exception
     */
    public function downloadAttachment($message)
    {
        Log::info('downloadAttachment', $message);
        switch ($message['MsgType']) {
            case MessageType::Attachment:
                $url = 'https://file.wx8.qq.com/cgi-bin/mmwebwx-bin/webwxgetmedia';
//                $suffix = (new File($message['FileName'], false))->getExtension() ?: 'undefined';
                break;

            default:
                return false;
                break;
        }

        $data = $this->request('GET', $url, [
            'query' => [
                'sender' => $message['FromUserName'],
                'mediaid' => $message['MediaId'],
                'filename' => $message['FileName'],
                'fromuser' => $this->getContact('Uin'),
                'pass_ticket' => $this->getCookies('pass_ticket') ?: 'undefined',
                'webwx_data_ticket' => $this->getCookies('webwx_data_ticket') ?: 'undefined',
            ],
            'on_headers' => function (ResponseInterface $response) use (& $suffix) {
                $suffix = last(explode('/', $response->getHeaderLine('Content-Type')));
            }
        ]);

        $path = 'wechat/attachment/' . $message['MsgId'] . '.' . $suffix;

        Storage::put($path, $data);

        return $path;
    }

    /**
     * download emotion
     * @param $data
     * @return string
     * @throws Exception
     */
    public function downloadEmotion($data)
    {
        Log::info('downloadEmotion', $data);
        $suffix = 'gif';
        $data = $this->request('GET', array_get($data, 'emoji.@attributes.cdnurl'), [
            'on_headers' => function (ResponseInterface $response) use (& $suffix) {
                $suffix = last(explode('/', $response->getHeaderLine('Content-Type')));
            }
        ]);

        $path = 'wechat/emotion/' . array_get($data, 'emoji.@attributes.md5', str_random(32)) . '.' . $suffix;
        Storage::put($path, $data);

        return $path;
    }


    /**
     * send normal message
     * @param $to
     * @param $content
     * @return bool
     */
    public function sendMessage($to, $content)
    {
        try {
            $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxsendmsg';
            $msg_id = $this->getClientMessageID();

            $response = $this->request('POST', $url, [
                'body' => json_encode([
                    'BaseRequest' => $this->getBaseRequest(),
                    'Msg' => [
                        'ClientMsgId' => $msg_id,
                        'FromUserName' => $this->getLoginUser('UserName'),
                        'ToUserName' => $to,
                        'LocalID' => $msg_id,
                        'Type' => 1,
                        'Content' => $content,
                    ]
                ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            ]);

            $content = json_decode($response, true);

            if (array_get($content, 'BaseResponse.Ret') !== 0) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('send text error ' . $e->getMessage());
            return false;
        }
    }

    /**
     * send emotion
     * @param $to
     * @param $gif_path
     * @return bool
     */
    public function sendEmotion($to, $gif_path)
    {
        $file = new File($gif_path);
        $ext = $file->getExtension();
        if ($ext != 'gif') {
            return false;
        }
        return $this->sendImage($to, $gif_path);
    }


    /**
     * send image && gif emotion
     * @param $to
     * @param $img_path
     * @return bool
     */
    public function sendImage($to, $img_path)
    {
        try {
            $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxsendmsgimg?fun=async&f=json';
            $msg_id = $this->getClientMessageID();
            $file = new File($img_path);
            $ext = $file->getExtension();

            if ($ext == 'gif') {
                $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxsendemoticon?fun=sys';
                $info = [
                    'Type' => 47,
                    'EmojiFlag' => 2,
                ];
            } else {
                $info = [
                    'Type' => 3,
                ];
            }

            $response = $this->request('POST', $url, [
                'body' => json_encode([
                    'BaseRequest' => $this->getBaseRequest(),
                    'Msg' => [
                        'ClientMsgId' => $msg_id,
                        'FromUserName' => $this->getLoginUser('UserName'),
                        'ToUserName' => $to,
                        'LocalID' => $msg_id,
                        'MediaId' => $this->uploadMedia($to, $img_path),
                    ] + $info
                ]),
            ]);

            $content = json_decode($response, true);

            if (array_get($content, 'BaseResponse.Ret') !== 0) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('send image error ' . $e->getMessage());
            return false;
        }
    }

    /**
     * send file
     * @param $to
     * @param $file_path
     * @return bool
     */
    public function sendFile($to, $file_path)
    {
        try {
            $url = 'https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxsendappmsg?fun=async&f=json';
            $msg_id = $this->getClientMessageID();
            $file = new File($file_path);

            $response = $this->request('POST', $url, [
                'body' => json_encode([
                    'BaseRequest' => $this->getBaseRequest(),
                    'Msg' => [
                        'ClientMsgId' => $msg_id,
                        'FromUserName' => $this->getLoginUser('UserName'),
                        'ToUserName' => $to,
                        'LocalID' => $msg_id,
                        'Type' => 6,
                        'Content' => sprintf("<appmsg appid='wxeb7ec651dd0aefa9' sdkver=''><title>%s</title><des></des><action></action><type>%d</type><content></content><url></url><lowurl></lowurl><appattach><totallen>%d</totallen><attachid>%s</attachid><fileext>%s</fileext></appattach><extinfo></extinfo></appmsg>",
                            $file->getFilename(), 6, $file->getSize(), $this->uploadMedia($to, $file_path), $file->getExtension()),
                    ],
                ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            ]);

            $content = json_decode($response, true);
            if (array_get($content, 'BaseResponse.Ret') !== 0) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('send file error ' . $e->getMessage());
            return false;
        }
    }

    public function uploadMedia($to, $file_path)
    {
        $url = 'https://file.wx8.qq.com/cgi-bin/mmwebwx-bin/webwxuploadmedia?f=json';
        $file = new File($file_path, true);

        $response = $this->request('POST', $url, [
            'multipart' => [
                [
                    'name' => 'id',
                    'contents' => $this->getFileID(),
                ],
                [
                    'name' => 'name',
                    'contents' => $file->getFilename(),
                ],
                [
                    'name' => 'type',
                    'contents' => $file->getMimeType(),
                ],
                [
                    'name' => 'lastModifiedDate',
                    'contents' => date('D M d Y H:i:s GMT+0800 (CST)', $file->getMTime()),
                ],
                [
                    'name' => 'size',
                    'contents' => $file->getSize(),
                ],
                [
                    'name' => 'mediatype',
                    'contents' => str_contains($file->getMimeType(), 'image') ? 'pic' : 'doc',
                ],
                [
                    'name' => 'filename',
                    'contents' => fopen($file_path, 'r'),
                ],
                [
                    'name' => 'uploadmediarequest',
                    'contents' => json_encode([
                        'UploadType' => 2,
                        'BaseRequest' => $this->getBaseRequest(),
                        'ClientMediaId' => $this->getTimeStamp(),
                        'TotalLen' => $file->getSize(),
                        'StartPos' => 0,
                        'DataLen' => $file->getSize(),
                        'MediaType' => 4,
                        'FromUserName' => $this->getLoginUser('UserName'),
                        'ToUserName' => $to,
                        'FileMd5' => md5_file($file_path),
                    ]),
                ],
                [
                    'name' => 'webwx_data_ticket',
                    'contents' => $this->getCookies('webwx_data_ticket') ?: 'undefined',
                ],
                [
                    'name' => 'pass_ticket',
                    'contents' => $this->getCookies('pass_ticket') ?: 'undefined',
                ]
            ]
        ]);

        $content = json_decode($response, true);

        if (array_get($content, 'BaseResponse.Ret') !== 0) {
            throw new Exception('upload media fail');
        }

        return array_get($content, 'MediaId');
    }

    /**
     * save current state
     */
    public function saveState()
    {
        $core_state = [
            'user' => $this->user,
            'fileIndex' => $this->fileIndex,
            'loginInfo' => $this->loginInfo,
            'clientOptions' => array_except($this->clientOptions, 'debug'),
        ];

        Storage::put('wechat/core_state.txt', serialize($core_state));
    }

    /**
     * clear state
     */
    public static function clearState()
    {
        Cache::forget('wechat_login_uuid');
        Storage::delete('wechat/core_state.txt');
    }

    /**
     * get new instance from stored state
     * @param bool $debug
     * @return WebApi
     */
    public static function restoreState($debug = false)
    {
        if (Storage::exists('wechat/core_state.txt')) {
            $core_state = unserialize(Storage::get('wechat/core_state.txt'));
            return new WebApi(['debug' => false] + $core_state['clientOptions'], array_except($core_state, 'clientOptions'));
        } else {
            return new WebApi();
        }
    }
}
