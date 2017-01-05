<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MakeQRcode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:qrcode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Qrcode ';

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
        $count = $this->ask('please enter count number ?');

        $bar = $this->output->createProgressBar($count);
        for ($i=1;$i<=$count;$i++){
            $r = str_random('10');
            $code =  url('dbc/redpack/'.$r);
            $contents  = QrCode::format('png')->size(100)->margin(0)->generate($code);
            Storage::disk('public-uploads')->put("dbc/{$r}.png",$contents);
            $bar->advance();
        }
        $bar->finish();
        //

    }
}
