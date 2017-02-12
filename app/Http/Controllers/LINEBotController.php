<?php
#Laravel init
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

#Library
//LINEBot
use App\Libraries\LINE\LINEBot;
use App\Libraries\LINE\LINEBot\HTTPClient\CurlHTTPClient;

#DB

class LINEBotController extends Controller
{

	public function bot(Request $request)
    {

    	$post = $request->all();

    	$httpClient = new CurlHTTPClient('<channel access token>');

		$bot = new LINEBot($httpClient, ['channelSecret' => 'c1e97f1d72e19a6d30302ada807611e1']);

    	return $post;

    }

}