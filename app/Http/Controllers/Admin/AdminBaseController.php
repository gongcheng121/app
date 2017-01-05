<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/5
 * Time: 16:35
 */

namespace App\Http\Controllers\Admin;


use Illuminate\Routing\Controller;

class AdminBaseController extends Controller{
    function __construct(){
        $this->middleware('admin.auth');
        $this->files = new \Illuminate\Filesystem\Filesystem;
        foreach ($this->files->files(storage_path().'/framework/views') as $file)
        {
            $this->files->delete($file);
        }
    }
} 