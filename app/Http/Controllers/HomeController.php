<?php namespace App\Http\Controllers;


use App\Model\Car;
use App\Model\Crawer;
use Carbon\Carbon;
use Collective\Remote\Facades\Remote as SSH;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Ixudra\Curl\Facades\Curl;
use Psr\Http\Message\ResponseInterface;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use TesseractOCR;


class HomeController extends Controller
{


    protected $client;
    protected $i = 1;

    protected $result;

    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    public function index()
    {
        $stepOneUrl = 'https://login.wx.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https%3A%2F%2Fwx.qq.com%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN&_=1483607230848';

        $stepOneResponse = Curl::to($stepOneUrl)->get();
        preg_match('/"(.*?)"/',$stepOneResponse,$match);
        $uuid = $match[1];
        session('uuid',$uuid);

        return view('wechatBoot.index',compact('uuid'));

    }


    public function index5()
    {


        $url = 'http://www.faisco.cn/validateCode.jsp?232';
        $im = imagecreatefromjpeg($url);
        imagegif($im, '1.gif');
        $rgbArray = array();
        $res = $im;
        $size = getimagesize($url);


        $wid = $size['0'];
        $hid = $size['1'];
        for ($i = 0; $i < $hid; ++$i) {
            for ($j = 0; $j < $wid; ++$j) {
                $rgb = imagecolorat($res, $j, $i);
                $rgbArray[$i][$j] = imagecolorsforindex($res, $rgb);
            }
        }

        for ($i = 0; $i < $hid; $i++) {
            for ($j = 0; $j < $wid; $j++) {

                if ($rgbArray[$i][$j]['red'] >= 90) {
                    echo '□';
                } else {
                    echo '■';
                }
            }
            echo "<br>";
        }

    }

    public function index4()
    {


        $curl = Curl::to('http://webchat.b.qq.com/cgi/d?t=49046234670303734');
        $curl->withData('kfcookie=kfskey:F81594F6775E258CA059D9A11BE7FC11E44A7DA9D79785606362CDBE1432470D$kfguin:1153865567$ext:1001&rt_status=200&rt_block=244&rt_wait=0&rt_recv=1&kfguin=1153865567&ext=1001&cid=17211944&uin=800017713&ty=1&msg=' . str_random('10') . '&idx=1480063165&');
        $response = $curl->post();

        dd($response);

    }

    public function index3()
    {


        $this->client = new Client();

        $this->result = [];
        $r = $this->getBody();
        return ($r);


//        return


    }

    function getBody($time = null)
    {
        $this->i++;
        $uri = 'https://shequ.yunzhijia.com/thirdapp/forum/getMsgList?forward=false&networkId=57a4a514e4b0074d5e546b1f' . '&t=' . Carbon::now()->timestamp * 1000;
        if ($time) {
            $uri .= '&separator=' . $time;
        }

        $u[] = $uri;
        $c = $this->client->request('GET', $uri);
        $body = $c->getBody();


        $body = (\Qiniu\json_decode($body)->message);
        if (sizeof($body) == 0) {
            return $this->result;
        }
        $this->result = array_merge($this->result, $body);
        $s_time = collect($body)->last()->updateTime;

        if ($this->i <= 20) {
            $this->getBody($s_time, $this->result);
        }

        return $this->result;
    }

    public function Rss()
    {


        $config = [
            'name' => '新闻中心',
            'scan_urls' => ['http://news.iyaxin.com/node_55909.htm'],
            'scan_target' => [
                [
                    'name' => '要闻',
                    'selector' => '#list > ul  a'
                ]
            ],
            'content_rule' => [
                [
                    'fields_name' => 'article_title',
                    'selector' => '#page >h1',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'source',
                    'selector' => '#source_baidu',
                    'attr_type' => '4',
                    'custom_rule' => '$("#source_baidu").text()'
                ],
                [
                    'fields_name' => 'article_content',
                    'selector' => '#page > div.article-detail',
                    'attr_type' => 2
                ],
                [
                    'fields_name' => 'author',
                    'selector' => '#author_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'pub_time',
                    'selector' => '#pubtime_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'editor',
                    'selector' => '#editor_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'description',
                    'selector' => 'meta[name="description"]',
                    'attr_type' => 3,
                    'attr' => "content"
                ],
            ]
        ];
        $config2 = [
            'name' => '新闻中心',
            'scan_urls' => ['http://money.iyaxin.com/'],
            'scan_target' => [
                [
                    'name' => '要闻',
                    'selector' => 'body > div.layout.hotnews > div.center > div:nth-child(1) a'
                ]
            ],
            'content_rule' => [
                [
                    'fields_name' => 'article_title',
                    'selector' => '#page >h1',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'source',
                    'selector' => '#source_baidu',
                    'attr_type' => '4',
                    'custom_rule' => '$("#source_baidu").text()'
                ],
                [
                    'fields_name' => 'article_content',
                    'selector' => '#Article > div.content',
                    'attr_type' => 2
                ],
                [
                    'fields_name' => 'author',
                    'selector' => '#author_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'pub_time',
                    'selector' => '#pubtime_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'editor',
                    'selector' => '#editor_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'description',
                    'selector' => 'meta[name="description"]',
                    'attr_type' => 3,
                    'attr' => "content"
                ],
            ]
        ];
        $config3 = [
            'name' => '健康',
            'scan_urls' => ['http://health.iyaxin.com/index.php?m=content&c=index&a=lists&catid=10'],
            'scan_target' => [
                [
                    'name' => '要闻',
                    'selector' => 'div.col_left.lbyleft > div.listcont > ul a'
                ]
            ],
            'content_rule' => [
                [
                    'fields_name' => 'article_title',
                    'selector' => '#page > h1',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'source',
                    'selector' => '#source_baidu',
                    'attr_type' => '4',
                    'custom_rule' => '$("#source_baidu").text()'
                ],
                [
                    'fields_name' => 'article_content',
                    'selector' => '#Article > div.content',
                    'attr_type' => 2
                ],
                [
                    'fields_name' => 'author',
                    'selector' => '#author_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'pub_time',
                    'selector' => '#pubtime_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'editor',
                    'selector' => '#editor_baidu',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'description',
                    'selector' => 'meta[name="description"]',
                    'attr_type' => 3,
                    'attr' => "content"
                ],
            ]
        ];
        $config4 = [
            'name' => '网易',
            'scan_urls' => ['http://xj.news.163.com/'],
            'scan_target' => [
                [
                    'name' => '要闻',
                    'selector' => '#local_site_wrap > div.local_site_content > div.idx-area-top > div.idx-topline.clearfix > div.idx-left a'
                ]
            ],
            'content_rule' => [
                [
                    'fields_name' => 'article_title',
                    'selector' => '#h1title',
                    'attr_type' => 1
                ],
                [
                    'fields_name' => 'article_content',
                    'selector' => '#endText',
                    'attr_type' => 2
                ],
            ]
        ];

//        $response = Curl::to('http://220.171.90.234:9033/spider/api') ->withData( ['config'=>$config] )->asJson()->post();
//        $response2 = Curl::to('http://220.171.90.234:9033/spider/api') ->withData( ['config'=>$config2] )->asJson()->post();
//        $response3 = Curl::to('http://220.171.90.234:9033/spider/api') ->withData( ['config'=>$config3] )->asJson()->post();
        $response4 = Curl::to('http://220.171.90.234:9033/spider/api')->withData(['config' => $config4])->asJson()->post();

//        $response = array_merge($response,$response2,$response3);
//        $response = \GuzzleHttp\json_decode($response);

        return $response4;

    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        /* $this->files = new \Illuminate\Filesystem\Filesystem;
         foreach ($this->files->files(storage_path().'/framework/views') as $file)
         {
             $this->files->delete($file);
         }*/
//		$this->middleware('auth');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index2(Request $request)
    {

        if ($request->s) {
            $s = $request->s;
        } else {
            $list = Crawer::where(['ip' => '222.82.227.198'])->select('sid')->get();
            $s = collect($list->toArray())->flatten()->toArray();
        }

        $result = Curl::to('http://220.171.90.234:9033/crawer/test')->withData([
            'sid' => $s,
            'e' => $request->e,
            'action' => 'action'
        ])->post();
        return $result;
//		return view('home');
    }


    public function ip(Request $request)
    {
        return response()->json(['ip' => $request->ip()])->setCallback($request->input('callback'));

    }

    public function counts(Request $request)
    {
        $count = Cache::get('counts', function () {
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::add('counts', 1, $expiresAt);
            return 1;
        });
        Cache::increment('counts', 1);
        return 'ok';
    }

    public function showCounts()
    {
//        dd(env('APP_DEBUG'));
//        dd(Redis::set('test','testtest'));
        $count = Cache::get('counts', function () {
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::add('counts', 1, $expiresAt);
            return 1;
        });
        dd($count);
    }


}
