<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Ixudra\Curl\Facades\Curl;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koala:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $count;
    public $array=[];
    public $bar;
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


        $id = '69493';
//        $id = '72534';
//        $id = '72466';
//        $id = '72367';
//        $id = '2022';
        $bar = $this->output->createProgressBar(3090);
        $this->count =0;
        $this->scan($id,$bar);

    }
    function scan(&$id,$bar){

        $url  = 'http://www.wolaigo.com/shop/index.php?act=snapshot&op=index&rec_id='.$id;
//        $url  = 'http://www.hamij.com/shop/index.php?act=snapshot&op=index&rec_id='.$id;

        $try=1;
        $response = Curl::to($url)->get();
        $crawer  = new Crawler($response);


        try{
            $s=  $crawer->filter('div.snapshot-goods-name > h1')->first()->text();
            $t = $crawer->filter(' div.ncs-detail > div.ncs-goods-summary > div > p:nth-child(2)')->text();
            $m = $crawer->filter('div.ncs-detail > div.ncs-goods-summary > dl.ncs-price > dd > em')->text();
            $try=0;
            $time=  '   '.substr($t,30);
            $m = str_pad(number_format((float) $m,2),8,' ',STR_PAD_RIGHT);
            $count =$this->count +=$m;
//            $this->info($id.$time.'  '.$m.'  '.number_format($count,2));
            $data = ['id'=>$id,'time'=>$time,'count'=>number_format((float)$m,2)];
            array_push($this->array,$data);
            $id++;
            $bar->advance();
        }catch (InvalidArgumentException $e){
            $this->info('end');
            $try++;
            if($try>=60){
                $try=1;
            }
            $data = $this->array;
            Excel::create('koalaTest', function($excel) use($data) {
                $excel->sheet('Sheetname', function($sheet) use ($data) {

                    $sheet->fromArray($data);

                });
            })->store('xls', storage_path('excel/exports'));
            dd();
            sleep($try);
            return $this->scan($id,$bar);
        }
//        $this->info('start next');
        $this->scan($id,$bar);
    }
}
