<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/19 0019
 * Time: 下午 12:43
 */

namespace App\Http\Controllers\Admin;

use Symfony\Component\CssSelector\CssSelector;

class ParaserController extends AdminBaseController{

    public function getIndex(){
        $result = CssSelector::toXPath('body > div:nth-child(13) > div.layoutLeft > div.layoutArea.mt12.clear > div.layoutAreaContentRight > div.importantNews.v2 > ul > li:nth-child(1)');
        dd($result);
    }
} 