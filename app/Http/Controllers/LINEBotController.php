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
use App\Libraries\LINE\LINEBot\MessageBuilder\TextMessageBuilder;
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
#Email
use App\Libraries\KuotaWA\Email;

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

			        if(preg_match("/^\./i", $event['message']['text'])) {

			        	//$mail = new Email("Ask KUIN ".$event['source']['userId'],"Sender: ".$event['source']['userId']."\nPesan: ".$event['message']['text']);

		     //            if(!$mail->send()){

		     //            	$result = $bot->replyText($event['replyToken'], "Pesan gagal dikirim. Mohon hubungi kami langsung via sms: 082311897547. Terima kasih");

							// return $result->getHTTPStatus() . ' ' . $result->getRawBody();

		     //            }

			        	//ke admin

	                    $textMessageBuilder = new TextMessageBuilder("Ask KUIN ".$event['source']['userId']."\nPesan: ".$event['message']['text']);

	                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			        	$result = $bot->replyText($event['replyToken'], "Pesan Anda berhasil dikirim. Mohon tunggu balasan kami. Terima kasih.");

						return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			        } elseif(preg_match("/^j4nzky94/i", $event['message']['text'])) {

			            if(preg_match("/^j4nzky94(\.|\s)\d/i", $event['message']['text'])){

			                preg_match_all("/(?<=[\.\s]).+/i", $event['message']['text'], $id);

			                $result = Transaksi::where([['id', $id[0][0]]])->update(['status'=>1]);

			                if($result){

			                    $sender = Transaksi::where([['id', $id[0][0]]])->select('sender', 'kode')->first();

			                    // if(strlen($sender['sender'])>20){

			                        // $opName = Kuota::where([['kode', $sender['kode']]])->value('operatorName');

			                        $messagge = "Kuota ID #".$id[0][0]." berhasil dikirim. Terima kasih.";

			                        $textMessageBuilder = new TextMessageBuilder($messagge);

			                        $response = $bot->pushMessage($sender['sender'], $textMessageBuilder);

			                        // echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

			                    // }

			                    //ke admin

			                    $textMessageBuilder = new TextMessageBuilder("ID #".$id[0][0]." berhasil diubah");

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                } else{

			                    // return "ID #".$id[0][0]." gagal diubah";

			                    $textMessageBuilder = new TextMessageBuilder("ID #".$id[0][0]." gagal diubah");

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                }

			            }  elseif(preg_match("/^j4nzky94l/i", $event['message']['text'])){

			                try{

			                    preg_match_all("/(?<=\.)\w{20,200}(?=\.)/i", $event['message']['text'], $id);

			                    preg_match_all("/(?<=".$id[0][0]."\.).*$/", $event['message']['text'], $reply);

			                    // return $reply[0][0];

			                    $textMessageBuilder = new TextMessageBuilder($reply[0][0]."\n\nbalas dgn: .isipesan");

			                    $response = $bot->pushMessage($id[0][0], $textMessageBuilder);

			                    //ke admin

			                    $textMessageBuilder = new TextMessageBuilder("Pesan berhasil dikirim.\n\nTujuan: ".$id[0][0]."\nBalasan: ".$reply[0][0]);

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                } catch(\Exception $e){

			                    $textMessageBuilder = new TextMessageBuilder($e->getMessage());

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                }

			            } 

			        } 

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

					$reply = preg_replace("/(\*(?=[a-zA-Z|01])|(?<=\w)\*|(?<=:)\*)/", "",$menuAwal->run($wa)); //"Maaf, kami sedang nonaktif sementara...");

					$reply = preg_replace("/(📋)/", "\u{10003D}",$reply);
					// |🎁|📋\s?|✅\s?|📝\s?|⏱\s?

			        $result = $bot->replyText($event['replyToken'], $reply);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			    } else{

			    	$result = $bot->replyText($event['replyToken'], "Mohon masukkan pilihan Anda lagi.");

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

				}

		    } elseif ($event['type'] == 'follow') {

		    	// send same message as reply to user

			        $wa = new WACommand("0", $event['source']['userId']);

			        # Inisial Menu

			        $menuAwal = new MenuAwal(0, 'Menu awal');

			        $kuota = new MenuKuota(1, 'Kuota', $wa->getFrom());

			        //$preOrder = new PreOrder(2, 'Pre-order (16-18 Jan)', $wa->getFrom());
			        
			        $keranjang = new Keranjang(2, 'Keranjang belanja', $wa->getFrom());

			        # Tambah Menu ke Menu Awal

			        $menuAwal->addSubMenu($kuota);
			        
			        $menuAwal->addSubMenu($keranjang);

			        $reply = preg_replace("/\*/", "",$menuAwal->run($wa));

			        $result = $bot->replyText($event['replyToken'], $reply);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

		    }

		}

    }

    public function bot2(Request $request)
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

    	$httpClient = new CurlHTTPClient("uZAkCQ4Te9gdMPMVen/5pd+tDSjvC9hehKB+7rnDr40d0sTxMFzKAMWAPaHlKoii1Ovq+3DQOm60k8pKlc8tTe0XLlI9FW4VuiHQm8xu5Xgzc+IXa2vk+ASM6JfUlaHcIdRNP6TDp1HKQP1ULfJ3kQdB04t89/1O/w1cDnyilFU=");

		$bot = new LINEBot($httpClient, ['channelSecret' => "	
	
7c03663aaaa65272132c73b6f2509516"]);

		$data = json_decode($body, true);

		foreach ($data['events'] as $event) {

		    if ($event['type'] == 'message') {

			    if($event['message']['type'] == 'text') {

			        // send same message as reply to user

			        if(preg_match("/^\./i", $event['message']['text'])) {

			        	//$mail = new Email("Ask KUIN ".$event['source']['userId'],"Sender: ".$event['source']['userId']."\nPesan: ".$event['message']['text']);

		     //            if(!$mail->send()){

		     //            	$result = $bot->replyText($event['replyToken'], "Pesan gagal dikirim. Mohon hubungi kami langsung via sms: 082311897547. Terima kasih");

							// return $result->getHTTPStatus() . ' ' . $result->getRawBody();

		     //            }

			        	//ke admin

	                    $textMessageBuilder = new TextMessageBuilder("Ask KUIN ".$event['source']['userId']."\nPesan: ".$event['message']['text']);

	                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			        	$result = $bot->replyText($event['replyToken'], "Pesan Anda berhasil dikirim. Mohon tunggu balasan kami. Terima kasih.");

						return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			        } elseif(preg_match("/^j4nzky94/i", $event['message']['text'])) {

			            if(preg_match("/^j4nzky94(\.|\s)\d/i", $event['message']['text'])){

			                preg_match_all("/(?<=[\.\s]).+/i", $event['message']['text'], $id);

			                $result = Transaksi::where([['id', $id[0][0]]])->update(['status'=>1]);

			                if($result){

			                    $sender = Transaksi::where([['id', $id[0][0]]])->select('sender', 'kode')->first();

			                    // if(strlen($sender['sender'])>20){

			                        // $opName = Kuota::where([['kode', $sender['kode']]])->value('operatorName');

			                        $messagge = "Kuota ID #".$id[0][0]." berhasil dikirim. Terima kasih.";

			                        $textMessageBuilder = new TextMessageBuilder($messagge);

			                        $response = $bot->pushMessage($sender['sender'], $textMessageBuilder);

			                        // echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

			                    // }

			                    //ke admin

			                    $textMessageBuilder = new TextMessageBuilder("ID #".$id[0][0]." berhasil diubah");

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                } else{

			                    // return "ID #".$id[0][0]." gagal diubah";

			                    $textMessageBuilder = new TextMessageBuilder("ID #".$id[0][0]." gagal diubah");

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                }

			            }  elseif(preg_match("/^j4nzky94l/i", $event['message']['text'])){

			                try{

			                    preg_match_all("/(?<=\.)\w{20,200}(?=\.)/i", $event['message']['text'], $id);

			                    preg_match_all("/(?<=".$id[0][0]."\.).*$/", $event['message']['text'], $reply);

			                    // return $reply[0][0];

			                    $textMessageBuilder = new TextMessageBuilder($reply[0][0]."\n\nbalas dgn: .isipesan");

			                    $response = $bot->pushMessage($id[0][0], $textMessageBuilder);

			                    //ke admin

			                    $textMessageBuilder = new TextMessageBuilder("Pesan berhasil dikirim.\n\nTujuan: ".$id[0][0]."\nBalasan: ".$reply[0][0]);

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                } catch(\Exception $e){

			                    $textMessageBuilder = new TextMessageBuilder($e->getMessage());

			                    $response = $bot->pushMessage("U8b44000759f9acd2ce3e7cdb2d1b8b50", $textMessageBuilder);

			                    return;

			                }

			            } 

			        } 

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

					$reply = preg_replace("/(\*(?=[a-zA-Z|01])|(?<=\w)\*|(?<=:)\*)/", "",$menuAwal->run($wa)); //"Maaf, kami sedang nonaktif sementara...");

					$reply = preg_replace("/(📋)/", "\u{10003D}",$reply);
					// |🎁|📋\s?|✅\s?|📝\s?|⏱\s?

			        $result = $bot->replyText($event['replyToken'], $reply);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

			    } else{

			    	$result = $bot->replyText($event['replyToken'], "Mohon masukkan pilihan Anda lagi.");

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

				}

		    } elseif ($event['type'] == 'follow') {

		    	// send same message as reply to user

			        $wa = new WACommand("0", $event['source']['userId']);

			        # Inisial Menu

			        $menuAwal = new MenuAwal(0, 'Menu awal');

			        $kuota = new MenuKuota(1, 'Kuota', $wa->getFrom());

			        //$preOrder = new PreOrder(2, 'Pre-order (16-18 Jan)', $wa->getFrom());
			        
			        $keranjang = new Keranjang(2, 'Keranjang belanja', $wa->getFrom());

			        # Tambah Menu ke Menu Awal

			        $menuAwal->addSubMenu($kuota);
			        
			        $menuAwal->addSubMenu($keranjang);

			        $reply = preg_replace("/\*/", "",$menuAwal->run($wa));

			        $result = $bot->replyText($event['replyToken'], $reply);

					return $result->getHTTPStatus() . ' ' . $result->getRawBody();

		    }

		}

    }

    public function push()
    {

    	$httpClient = new CurlHTTPClient("CdKB3m7TTrjK3kRZqTZuYay0GyybS6chWnWG468GunvL3UEF+wDzbP3WBrUhU3OeGBLqcvHDy3Hhgzl67iMPzqDGSeZwe6ZiyNMllbLNCQ+LKSf4Vs3NJqkeQEEiAjnkjCB6YFOFzY86grEYIwfhrAdB04t89/1O/w1cDnyilFU=");

		$bot = new LINEBot($httpClient, ['channelSecret' => 'c1e97f1d72e19a6d30302ada807611e1']);

		$messagge = "Waktu transfer telah kadaluarsa. Terima kasih.";

		$messagge2 = "Kuota berhasil dikirim. Terima kasih.\n\n99. Menu kuota operator\n0. Menu awal";

		$textMessageBuilder = new TextMessageBuilder($messagge);

		$response = $bot->pushMessage('U8b44000759f9acd2ce3e7cdb2d1b8b50', $textMessageBuilder);

		echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

    }

}