<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:message';

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
        //
        $url = 'https://jianliao.com/v2/services/webhook/e5a024eb9f913e43e8a8eedceb862fb4f22df1c9';
        if(isset($this->data['type']) && $this->data['type']=='wx'){
            $url = 'https://jianliao.com/v2/services/webhook/0c38499e081bb939a3d1e004eb553ab0b757686e';
        }
        $http = new Http();
        $data = [
            "authorName"=> isset($this->data['authorName']) ? $this->data['authorName'] :"Iyaxin",                          // 消息发送者的姓名，如果留空将显示为配置中的聚合标题
            "title"=> $this->data['title'],                    // 聚合消息标题
            "text"=> is_array($this->data['data'])?json_encode($this->data['data']):$this->data['data'],                                     // 聚合消息正文
        ];
        $result = $http->post($url,$data);
    }
}
