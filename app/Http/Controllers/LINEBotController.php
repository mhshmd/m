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
//KuotaWA
use App\Libraries\KuotaWA\MenuAbstract;
use App\Libraries\KuotaWA\MenuAwal;
use App\Libraries\KuotaWA\MenuKuota;
use App\Libraries\KuotaWA\PreOrder;
use App\Libraries\KuotaWA\Keranjang;
use App\Libraries\KuotaWA\WACommand;
use App\Libraries\KuotaWA\SMSCommand;
//XMPPHP
use App\Libraries\XMPPHP\XMPPHP_XMPP;

#DB
use App\Operator;
use App\Transaksi;
use App\Kuota;
use App\XMPPQuery;
use App\Kelas;
use App\PreOrderData;

class LINEBotController extends Controller
{

	public function bot(Request $request)
    {

    	// get request body and line signature header

    	$bodies = $request->all();

    	// error_log("\r\n".$_SERVER['HTTP_X_LINE_SIGNATURE']."yes1\r\n",3,__DIR__."/log/envaya.log");

    	$body = file_get_contents('php://input');

    	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

    	//error_log("\r\n".$_SERVER['HTTP_X_LINE_SIGNATURE']."ok1\r\n",3,__DIR__."/log/envaya.log");

    	// is LINE_SIGNATURE exists in request header?

		if (empty($signature)){

			abort(400, 'Signature not set');

		}

		// is this request comes from LINE?
// 		if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, "	
// c1e97f1d72e19a6d30302ada807611e1", $signature)){

// 		    abort(400, 'Invalid signature');

// 		}

    	$httpClient = new CurlHTTPClient("CdKB3m7TTrjK3kRZqTZuYay0GyybS6chWnWG468GunvL3UEF+wDzbP3WBrUhU3OeGBLqcvHDy3Hhgzl67iMPzqDGSeZwe6ZiyNMllbLNCQ+LKSf4Vs3NJqkeQEEiAjnkjCB6YFOFzY86grEYIwfhrAdB04t89/1O/w1cDnyilFU=");

		$bot = new LINEBot($httpClient, ['channelSecret' => "	
c1e97f1d72e19a6d30302ada807611e1"]);

		$data = json_decode($body, true);

		foreach ($data['events'] as $event) {

		    if ($event['type'] == 'message') {

			    if($event['message']['type'] == 'text') {

			        // send same message as reply to user

			        $wa = new WACommand($event['message']['text'], $event['source']['userId']);

			        # Inisial Menu

			        $menuAwal = new MenuAwal(0, 'Menu awal');

			        $kuota = new MenuKuota(1, 'Kuota', $wa->getFrom());

			        //$preOrder = new PreOrder(2, 'Pre-order (16-18 Jan)', $wa->getFrom());
			        
			        $keranjang = new Keranjang(2, 'Keranjang belanja', $wa->getFrom());

			        # Tambah Menu ke Menu Awal

			        $menuAwal->addSubMenu($kuota);

			        // $menuAwal->addSubMenu($preOrder);
			        
			        $menuAwal->addSubMenu($keranjang);

			        # Run command wa

			  //       try{

			  //       	$menuAwal->run($wa);

					// } 
					// catch(\Exception $e){

					//     error_log($e->getMessage(),3,__DIR__."/log/envaya.log");

					// }

					$reply = preg_replace("/(\*(?=[a-zA-Z|01])|(?<=\w)\*|(?<=:)\*)/", "",$menuAwal->run($wa));

					$reply = preg_replace("/(📋)/", "\u{10003D}",$reply);
					// |🎁|📋\s?|✅\s?|📝\s?|⏱\s?

			        $result = $bot->replyText($event['replyToken'], $reply);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			    }

		    }

		}

    }

}