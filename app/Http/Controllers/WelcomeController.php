<?php namespace App\Http\Controllers;

use App\Commands\SendMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Http;


class WelcomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
//        $this->middleware('wechat');
        parent::__construct();

	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{


       return redirect('redpack')->with('code','1f3ac9a8e1f2f6cace6afeca352dda3c');
	}

}
