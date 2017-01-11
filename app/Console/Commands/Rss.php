<?php

namespace App\Console\Commands;

use App\Model\Cron;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Ixudra\Curl\Facades\Curl;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;

class Rss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:rss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cron::create(['type'=>'RssMake_start']);
        //
        $feed_liebao = new Feed();
        $feed_bjh = new Feed();

        $channel = new Channel();
        $channel_bjh = new Channel();
        $channel
            ->title('亚心网新闻')
            ->description('亚心网热点新闻')
            ->url('http://www.iyaxin.com')
            ->language('zh-cn')
            ->pubDate(strtotime(date("Y-m-d H:i:s",time())))
            ->appendTo($feed_liebao);

        $channel_bjh
            ->title('亚心网新闻')
            ->description('亚心网热点新闻')
            ->url('http://www.iyaxin.com')
            ->image('http://app.iyaxin.com/images/iyaxin_logo.png')
            ->language('zh-cn')
            ->pubDate(strtotime(date("Y-m-d H:i:s",time())))
            ->appendTo($feed_liebao);

        $channel_bjh->appendTo($feed_bjh); // 同步到百家号

        $response = Curl::to('http://220.171.90.234:9033/spider/')->get();
//        TODO  Koala 增加动态规则调用
        $response = \GuzzleHttp\json_decode($response);

        foreach ($response as $k => $v) {

            if ($v->article_title) {
                $t = date('Y-m-d',time());
                $item = new Item();
                $content = trim($v->article_content);

                $content = str_replace(['../../../'],'http://news.iyaxin.com/',$content);
                $source = str_replace("来源：","",$v->source);

                $item
                    ->title($v->article_title)
                    ->url('http://app.iyaxin.com/content/view/' .$t. '/'.md5($v->article_title).'.html?s=cm')
                    ->description($v->description)
                    ->category($v->category)
                    ->author($v->author)
                    ->source($source)
                    ->contentEncoded($content)
                    ->pubDate(strtotime($v->pub_time))
                    ->appendTo($channel);

                $item->url('http://app.iyaxin.com/content/view/' .$t. '/'.md5($v->article_title).'.html?id=baijiahao')
                    ->appendTo($channel_bjh);

                $title = $v->article_title;

                $pubtime =$v->pub_time;
//                dd($content);
                $view = view('rss/rss',compact('title','content',"source","pubtime"));
                $path = '/content/view/'.$t.'/'.md5($v->article_title).'.html';
                Storage::disk('public-uploads')->put($path,$view);

            }

        }

        Storage::disk('public-uploads')->put("feed/rss.rss",$feed_liebao);
        Storage::disk('public-uploads')->put("feed/bjh/rss.rss",$feed_bjh);

        Cron::create(['type'=>'RssMake_end']);
//        return response($feed)
//            ->header('Content-type', 'text/xml');
    }
}
