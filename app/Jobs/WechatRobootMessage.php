<?php

namespace App\Jobs;

use App\Extensions\Wechat\MessageType;
use App\Extensions\Wechat\WebApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Ixudra\Curl\Facades\Curl;

class WechatRobootMessage extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    protected $type;
    protected $from = [];
    protected $to = [];
    protected $value;
    protected $info = [];

    protected $api;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $from, $to, $value, $info = [])
    {
        //

        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
        $this->value = $value;
        $this->info = $info;


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WebApi $api)
    {
        $api = $api->restoreState();
        if ($this->type == MessageType::Init) {
            return null;
        }

        // pre process & format
        switch ($this->type) {
            case MessageType::Text:

                $this->value = $this->info['Content'];

                // It's a Location
                if (!empty($this->info['Url'])) {
                    $this->type = MessageType::Location;
                    $this->value = [
                        'address' => array_first(explode(':', $this->value)),
                        'url' => $this->info['Url'],
                    ];
                }
                break;

            case MessageType::LinkShare:

                if (array_get($this->from, 'Type') == 'public') {
                    $this->type = MessageType::PublicLinkShare;
                }

                $xml = str_replace('<br/>', '', htmlspecialchars_decode($this->info['Content']));
                $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
                $data = json_decode(json_encode($xml), true);
                $items = array_get($data, 'appmsg.mmreader.category.item');
                if (!empty($items) && is_array(array_first($items))) {
                    $news = array_map(function ($item) {
                        return array_only($item, ['title', 'digest', 'url']);
                    }, $items);
                } else {
                    $news = json_encode(array_only($data['appmsg'], ['title', 'des', 'url']), JSON_UNESCAPED_UNICODE);
                }
                $this->value = $news;
                break;

            case MessageType::Card:
                $this->value = array_only(array_get($this->info, 'RecommendInfo', []), ['NickName', 'Province', 'City']);
                break;

            case MessageType::System:
                $this->value = array_get($this->info, 'Content');
                break;
        }
        echo $this->from['NickName'].' to '.$this->to['NickName'].':'.dump($this->value).'';
        $can_arr = [
            '@df0dba46f89ca671a19eabec060771898f75aab0198cdea78337becf4929f77a',
            '@@a9cf9a45fb3430b8a9f8fc4e4bfafac7842e17767cfedbae2ec74653fdf1e7c0',
            '@@80e3d3056da04320cba6ddc58cc2e78e6347346af3638defba4e3f8512f9f434',
            '@@8cfa419581521de0dc96e15261f159e305a4c2cd6ffd42a0edd31824aee78015',
            '@3b4a787accd7abbaaf5448416dd076e0f85fd794f7428e55024c66d81e65c0d2',//邓平
            '@58668b1e0795870004fcf7f39d72e719',//微软小兵
            ''
        ];

            if (in_array($this->from['UserName'], $can_arr)) {

//            $this->from['Type']=='group'; //内容发送到群


                $tulin_url = 'http://www.tuling123.com/openapi/api';
                $response = Curl::to($tulin_url)->withData([
                    'key' => '6e502081a6da41f4b66e0f0cce3a3797',
                    'info' => $this->value,
                    'userid' => '@ebff77f5b468f36fcba3e31fe7c71642'
                ])->asJson(true)
                    ->post();
                $nick = '';
                if ($this->from['Type'] == 'group') {
                    $nick = $this->to['NickName'];
                }
                if ($response['code'] == 100000) {
                    $api->sendMessage($this->from['UserName'], '@' . $nick . "  " . $response['text']);
                }

            }
            


//        $this->type, $this->from, $this->to, $this->value, $this->info
//        dump($this->info);
//        dump($this->value);
//        dump($this->from);
//        dump($this->to);
//        dump($this->value);

    }
}
