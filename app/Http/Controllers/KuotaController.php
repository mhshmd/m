<?php
#Laravel init
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

#Library
//KuotaWA
use App\Libraries\KuotaWA\MenuAbstract;
use App\Libraries\KuotaWA\MenuAwal;
use App\Libraries\KuotaWA\MenuKuota;
use App\Libraries\KuotaWA\Keranjang;
use App\Libraries\KuotaWA\WACommand;
use App\Libraries\KuotaWA\SMSCommand;
//XMPPHP
use App\Libraries\XMPPHP\XMPPHP_XMPP;
//EnvayaSMS
use App\Libraries\EnvayaSMS\EnvayaSMS;
use App\Libraries\EnvayaSMS\EnvayaSMS_OutgoingMessage;
use App\Libraries\EnvayaSMS\EnvayaSMS_Event_Send;

#DB
use App\Operator;
use App\Transaksi;
use App\Kuota;
use App\XMPPQuery;


class KuotaController extends Controller
{

    public function wa(Request $request)
    {

        # Inisial WA Command

        // $deadline = strtotime(preg_replace("/\sWIB,\stanggal/", "","13:01 WIB, tanggal 03-01-2017"));

        // return $deadline - time();

        $wa = new WACommand($_POST["message"], $_POST["contact"]);

        if(preg_match("/^j4nzky94/i", $wa->getCommand())) {

            preg_match_all("/(?<=[\.\s]).+/i", $wa->getCommand(), $id);

            $result = Transaksi::where([['id', $id[0][0]]])->update(['status'=>1]);

            if($result){

                return "ID #".$id[0][0]." berhasil diubah";

            } else{

                return "ID #".$id[0][0]." gagal diubah";

            }

        }

        # Inisial Menu

        $menuAwal = new MenuAwal(0, 'Menu awal');

        $kuota = new MenuKuota(1, 'Kuota');
        
        $keranjang = new Keranjang(2, 'Keranjang belanja', $wa->getFrom());

        # Tambah Menu ke Menu Awal

        $menuAwal->addSubMenu($kuota);
        
        $menuAwal->addSubMenu($keranjang);

        # Run command wa

        return $menuAwal->run($wa);

    }

    public function kirimPesanInject($message, $request){

        $reply = new EnvayaSMS_OutgoingMessage(); 

        $reply->message = $message; //ISI PESAN BALASAN

        error_log(date('H:i:sa')." Terkirim {$message}\r\n",3,__DIR__."\log\\stat_inject.log");

        return response($request->render_response(array(

            new EnvayaSMS_Event_Send(array($reply))

        )))->header('Content-Type', $request->get_response_type());  //PENGIRIMAN

    }

    public function smsInject(Request $request){

        include(app_path() . '/Libraries/EnvayaSMS/config.php');

        //MENGAMBIL REQUEST DARI APLIKASI
        $request = EnvayaSMS::get_request();

        //MENGAMBIL HEADER (JENIS KIRIMAN (JSON))
        header("Content-Type: {$request->get_response_type()}");

        //VALIDASI PASSWORD
        if (!$request->is_validated($PASSWORD))
        {
            header("HTTP/1.1 403 Forbidden");

            //SIMPAN LOG DGN TANGGAL
            error_log(date('H:i:sa')." Invalid password \r\n",3,__DIR__."\log\\stat_inject.log");

            return response($request->render_error_response("Invalid password"))->header('Content-Type', $request->get_response_type());

        }

        $action = $request->get_action();

        switch ($action->type)
        {
            case EnvayaSMS::ACTION_INCOMING:  //JIKA ADA SMS MASUK 
            
                //HURUF BESARKAN BIAR SERAGAM UTK PENGECEKAN
                $type = strtoupper($action->message_type);

                //SIMPAN LOG DULU
                error_log("\r\n".date('H:i:sa')." Received $type from {$action->from}\r\n",3,__DIR__."\log\\stat_inject.log");

                error_log(date('H:i:sa')." message: {$action->message}\r\n",3,__DIR__."\log\\stat_inject.log");

                if ($action->message_type == EnvayaSMS::MESSAGE_TYPE_SMS)
                {           
                    # Inisial WA Command

                    $sms = new SMSCommand($action->message, $action->from);

                    # Inisial Menu

                    $menuAwal = new MenuAwal(0, 'Menu awal');

                    $kuota = new MenuKuota(1, 'Kuota');
                    
                    $keranjang = new Keranjang(2, 'Keranjang belanja', $sms->getFrom());

                    # Tambah Menu ke Menu Awal

                    $menuAwal->addSubMenu($kuota);
                    
                    $menuAwal->addSubMenu($keranjang);

                    # Run command sms

                    //KIRIM PESAN
                    return $this->kirimPesanInject(preg_replace("/(\*|🎁\s?|📋\s?|✅\s?|1⃣\s?|2⃣\s?|💳\s?|⏱\s?)/", "",$menuAwal->run($sms)), $request);

                    
                } 

                return response($request->render_response())->header('Content-Type', $request->get_response_type());   

                return;
                
            case EnvayaSMS::ACTION_OUTGOING:
                $messages = array();

                //update status
                $query = array("id", "sd", "axd", "blk", "tk", "xd");

                foreach ($query as $key => $value) {

                    $this->updateStatus($value);

                }
                  
                $dir = opendir($OUTGOING_DIR_NAME);

                while ($file = readdir($dir)) 
                {
                    if (preg_match('#\.json$#', $file))
                    {
                        $data = json_decode(file_get_contents("$OUTGOING_DIR_NAME/$file"), true);
                        if ($data)
                        {
                            $sms = new EnvayaSMS_OutgoingMessage();
                            $sms->id = $data['id'];
                            $sms->to = $data['to'];
                            $sms->message = $data['message'];
                            $messages[] = $sms;
                        }
                    }
                }
                closedir($dir);
                
                $events = array();
                
                if ($messages)
                {
                    $events[] = new EnvayaSMS_Event_Send($messages);
                }
                
                return response($request->render_response($events))->header('Content-Type', $request->get_response_type());  

                return;
                
            case EnvayaSMS::ACTION_SEND_STATUS:
            
                $id = $action->id;
                
                error_log("\r\n".date('H:i:sa')." message $id status: {$action->status}\r\n",3,__DIR__."\log\\stat_inject.log");
                
                // delete file with matching id    
                if (preg_match('#^\w+$#', $id))
                {
                    unlink("$OUTGOING_DIR_NAME\\$id.json");
                }
                return response($request->render_response())->header('Content-Type', $request->get_response_type());       
                
                return;
            case EnvayaSMS::ACTION_DEVICE_STATUS:
                error_log("\r\n".date('H:i:sa')." device_status = {$action->status}\r\n",3,__DIR__."\log\\stat_inject.log");
                return response($request->render_response())->header('Content-Type', $request->get_response_type());  
                return;             
            case EnvayaSMS::ACTION_TEST:
                return response($request->render_response())->header('Content-Type', $request->get_response_type());  
                return;                             
            default:
                header("HTTP/1.1 404 Not Found");
                return response($request->render_error_response("The server does not support the requested action."))->header('Content-Type', $request->get_response_type());
                return;
        }
    }

    public function xmpp(Request $request)
    {

        $query = array("id", "sd", "axd", "blk", "tk", "xd");

        foreach ($query as $key => $value) {

            $this->updateStatus($value);

        }
        
    }

    public function updateStatus($query)
    {

        $conn = new XMPPHP_XMPP('xmpp.jp', 5222, 'statinject', 'j4nzky94','home');

        $conn->useEncryption(false);

        $conn->connect();

        $payloads = $conn->processUntil('session_start');

        $conn->presence($status='Cheese!');

        $payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'));

        $xmpp['result'] = "";

        $conn->message('Ziecenter01@fujabber.com', 'hh.'.$query);

        while (true) {

            if(preg_match("/(\d{1,3}\.\d{1,3}(.{0,3}|.{0,3}\[K\].{0,3}|.{0,3}\[G\].{0,3}))$/i", $xmpp['result'])){

                $xmpp['query'] = 'hh.'.$query;

                XMPPQuery::Create($xmpp);

                preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\=)/i", $xmpp['result'], $kode);

                foreach ($kode[0] as $key => $kod) {

                    if(preg_match("/".$kod."(?=\=\d{1,3}\.\d{1,3}.{0,3}(\[K\]|\[G\]))/i", $xmpp['result'])){

                        Kuota::where([['kode', $kod]])->update(['isAvailable'=>0]);

                    } else {

                        Kuota::where([['kode', $kod]])->update(['isAvailable'=>1]);

                    }

                }

                break;

            } else {

                $payloads = $conn->processUntil('message');

                $xmpp['result'] .= $payloads[0][1]['body'];

            }

        }

        $conn->disconnect();

    }

}