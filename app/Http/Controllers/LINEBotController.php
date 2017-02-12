<?php
#Laravel init
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

#Library
//LINEBot
use App\Libraries\LINE\LINEBot;
use App\Libraries\LINE\SignatureValidator;
use App\Libraries\LINE\LINEBot\HTTPClient\CurlHTTPClient;

#DB

class LINEBotController extends Controller
{

	public function bot(Request $request)
    {

    	// get request body and line signature header

    	//$body = $request->all();

    	$body = file_get_contents('php://input');

    	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

    	// is LINE_SIGNATURE exists in request header?

		if (empty($signature)){

			abort(400, 'Signature not set');

		}

		// is this request comes from LINE?
		if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){

		    abort(400, 'Invalid signature');

		}

    	$httpClient = new CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);

		$bot = new LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

		$data = json_decode($body, true);

		foreach ($data['events'] as $event) {

		    if ($event['type'] == 'message') {

			    if($event['message']['type'] == 'text') {

			        // send same message as reply to user

			        $result = $bot->replyText($event['replyToken'], $event['message']['text']);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			    }

		    }

		}

    	return $post;

    }

}