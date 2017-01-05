<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/7
 * Time: 19:00
 */

namespace App\Http\Controllers;




class BaseController extends Controller {
    public function __construct(){
       /* $this->files = new \Illuminate\Filesystem\Filesystem;
        foreach ($this->files->files(storage_path().'/framework/views') as $file)
        {
            $this->files->delete($file);
        }*/
    }
} 