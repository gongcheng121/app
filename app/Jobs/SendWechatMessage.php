<?php

namespace App\Jobs;

use App\Extensions\Wechat\WebApi;
use App\Jobs\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWechatMessage extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable,SerializesModels;

    protected $to;
    protected $msg;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to,$msg)
    {
        //
        $this->to=$to;
        $this->msg=$msg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $this->api = WebApi::restoreState();
        $this->api->sendMessage($this->to, $this->msg);
    }
}
