<?php namespace App\Http\Controllers\Admin;

use App\Reservation;
use App\User;
use Illuminate\Routing\Controller;

class HomeController extends AdminBaseController
{


    public function index(){
//后台首页
        return view('admin.index');
    }
}