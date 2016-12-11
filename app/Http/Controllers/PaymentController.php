<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserTryout;
use App\UserPayment;
use App\LockDB;
use App\Http\Controllers\Controller;
use DB;

class PaymentController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function index(Request $request)
    {
        //GATEWAY SMS
        ini_set('display_errors','0');

        /*
         * This password must match the password in the EnvayaSMS app settings,
         * otherwise example/www/gateway.php will return an "Invalid request signature" error.
         */

        //PASSWORD UTK VALIDASI ANDROID ASAL
        $PASSWORD = 'j4nzky94@@@';

        /*
         * example/send_sms.php uses the local file system to queue outgoing messages
         * in this directory.
         */

        //FOLDER UTK QUEUE SMS OUT
        $OUTGOING_DIR_NAME = __DIR__."/outgoing_sms";

        /*
         * AMQP allows you to send outgoing messages in real-time (i.e. 'push' instead of polling).
         * In order to use AMQP, you would need to install an AMQP server such as RabbitMQ, and 
         * also enter the AMQP connection settings in the app. (The settings in the EnvayaSMS app
         * should use the same vhost and queue name, but may use a different host/port/user/password.)
         */

        //SETTING REALTIME
        $AMQP_SETTINGS = array(
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'queue_name' => "envayasms"
        );

        //MENGAMBIL REQUEST DARI APLIKASI
        $request = EnvayaSMS::get_request();

        //MENGAMBIL HEADER (JENIS KIRIMAN (JSON))
        header("Content-Type: {$request->get_response_type()}");

        //echo $request->render_error_response($request->get_response_type());
        // return;

        //VALIDASI PASSWORD
        if (!$request->is_validated($PASSWORD))
        {
            header("HTTP/1.1 403 Forbidden");
            //SIMPAN LOG DGN TANGGAL
            error_log(date('H:i:sa')." Invalid password \r\n",3,__DIR__."\log\\envaya.log");
            return response($request->render_error_response("Invalid password"))->header('Content-Type', $request->get_response_type());
            return;
        }

        $action = $request->get_action();

        //UNTUK CETAK JENIS REQUEST
        $payment['pesan'] = $action->type;

        //UserPayment::Create($payment);  

        switch ($action->type)
        {
            case EnvayaSMS::ACTION_INCOMING:  //JIKA ADA SMS MASUK 
            
                //HURUF BESARKAN BIAR SERAGAM UTK PENGECEKAN
                $type = strtoupper($action->message_type);

                //SIMPAN LOG DULU
                error_log("\r\n".date('H:i:sa')." Received $type from {$action->from}\r\n",3,__DIR__."\log\\envaya.log");
                error_log(date('H:i:sa')." message: {$action->message}\r\n",3,__DIR__."\log\\envaya.log");

                if ($action->message_type == EnvayaSMS::MESSAGE_TYPE_SMS && $action->from == "BankBRI")//JIKA TIPE SMS DAN SMS DARI BRI
                {
                    $allPrice = UserPayment::where('paid', 0)->select('price')->get();
                    $pesan = $action->message;
                    // $pr = number_format($allPrice[0]['price'], 2, '.', ',');
                    // $pesan = preg_replace("/".'188'."/", $pr, $pesan);
                    $pesan = preg_replace("/[,]/", "", $pesan);
                    $success = 0;
                    foreach ($allPrice as $key => $price) {
                        $regex = "/".$price['price']."/i";                       
                        if(preg_match($regex, $pesan)){
                            UserPayment::where('price', (string)$price['price'])->update(['paid'=>1, 'pesan'=>$action->message]);
                            $success = 1;
                            break;
                        }
                    }
                    if($success==1){
                        error_log(date('H:i:sa')." Lunas: {$action->message}\r\n",3,__DIR__."\log\\envaya.log");
                    } else{
                        error_log(date('H:i:sa')." Gagal Lunas: {$action->message}\r\n",3,__DIR__."\log\\envaya.log");
                    }


                    // $reply = new EnvayaSMS_OutgoingMessage();
                    // $reply->message = "Ok";
                
                    
                    
                    // return response($request->render_response(array(
                    //     new EnvayaSMS_Event_Send(array($reply))
                    // )))->header('Content-Type', $request->get_response_type());  
                } 
                return response($request->render_response())->header('Content-Type', $request->get_response_type());                                    
                return;
                
            case EnvayaSMS::ACTION_OUTGOING:
                $messages = array();
           
                // In this example implementation, outgoing SMS messages are queued 
                // on the local file system by send_sms.php. 
                  
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
                
                error_log("\r\n".date('H:i:sa')." message $id status: {$action->status}\r\n",3,__DIR__."\log\\envaya.log");
                
                // delete file with matching id    
                if (preg_match('#^\w+$#', $id))
                {
                    unlink("$OUTGOING_DIR_NAME\\$id.json");
                }
                return response($request->render_response())->header('Content-Type', $request->get_response_type());       
                
                return;
            case EnvayaSMS::ACTION_DEVICE_STATUS:
                error_log("\r\n".date('H:i:sa')." device_status = {$action->status}\r\n",3,__DIR__."\log\\envaya.log");
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

    public function pesan(Request $request){
        $email = $request->cookie('email');
        $user = "Profil";
        $payment = NULL;
        if($email!=""){
            $payment['email'] = $email;
            $user = User::where('email', $email)->get();
        } else{
            return redirect()->route('login');
        }
        //PENGUNCIAN DB
        // $a = "188";
        // $b = "Trx Rek 1257-01-004085-50-9: CN MASUK KE TABUNGAN sebesar Rp. 2,640,680.00 Pada Tanggal 26/01/16 Pukul 13:33:00\n";
        // $b = preg_replace("/\.00/", "", $b);
        // $b = preg_replace("/[.,]/", "", $b);
        // return $b;
        // $regex = "/\.0+/";//"/[^.,]/";
        // if(preg_match_all($regex, $b, $match)) {
        //   return $match;//.' Ada';
        // } else {
        //   return $match;//.' Tidak';
        // }
        // if(strpos($b, $a)!==false){
        //     return "ok";
        // }else{
        //     return "no";
        // }
        // $example = "1234567";
        // $subtotal =  number_format($example, 2, '.', ',');
        // return $subtotal


        while (true){
            $lockDB = LockDB::where('id', 1)->select('status')->get();
            // return $lockDB;
            if($lockDB[0]['status']==0){
                LockDB::where('id', 1)->update(['status'=>1]);
                break;
            }else{
                sleep(2);
            }
        }        
        
        //SIMPAN EMAIL JIKA ADA        

        //PROSES BIAYA+ANGKA RANDOM, DLL
        $payment['phone'] = preg_replace("/-/", "",$request['phone']);
        $payment['lifetime'] = $request['lifetime'];

        //CEK ANGKA RANDOM YANG SUDAH DIPAKAI
        $availableSand = UserPayment::where('paid', 1)->select('sand')->get();
        if($availableSand == '[]'){//JIKA TIDAK ADA
            while (true) {
                //PILIH ANGKA RANDOM
                $sand = rand(1,999);
                //CEK KLO SDH ADA
                $taken = UserPayment::where('sand', $sand)->select('paymentId')->get();
                if($taken=="[]"){
                    $payment['sand'] = $sand;
                    break;
                }
            }            
        } else{
            $payment['sand'] = $availableSand[0]['sand'];
        }

        $total = 0;
        switch($request['lifetime']) {
                    case '1':
                        $total = $request['lifetime']*20000;
                        break;
                    case '2':
                        $total = $request['lifetime']*20000-5000;
                        break;
                    case '3':
                        $total = ($request['lifetime']*20000)-10000;
                        break;
                    case '4':
                        $total = ($request['lifetime']*20000)-15000;
                        break;
                    case '5':
                        $total = ($request['lifetime']*20000)-20000;
                        break;
                    case '6':
                        $total = ($request['lifetime']*20000)-25000;
                        break;
                    case '7':
                        $total = ($request['lifetime']*20000)-30000;
                        break;
                    default:
                        $total = 0;
                }
        $payment['price'] = $total-$payment['sand'];
        $double = UserPayment::where([['sand', $payment['sand']],['paid', 0]])->select('paymentId')->get();
        // return $double;
        if($double=="[]"){
            UserPayment::Create($payment);
        }        

        //UNLOCK DB
        LockDB::where('id', 1)->update(['status'=>0]);

        //KIRIM PESAN TRANSFER BANK

        $message = new EnvayaSMS_OutgoingMessage();
        $message->id = uniqid("");
        $message->to = $payment['phone'];
        $message->message = "Dari: toSTIS.net \r\nSilahkan transfer tepat Rp".number_format($payment['price'], 0, ',', '.')." ke rekening BRI : 1257-01-004085-50-9 a.n. MUH. SHAMAD sebelum jam ".date("H:i", strtotime('+5 hours')).". Tryout otomatis dapat diikuti setelah transfer berhasil.";//"Trx Rek 1257-01-004085-50-9: CN MASUK KE TABUNGAN sebesar Rp. ".number_format($payment['price'], 2, '.', ',')." Pada Tanggal 26/01/16 Pukul 13:33:00\n";//
        //FOLDER UTK QUEUE SMS OUT
        $OUTGOING_DIR_NAME = __DIR__."/outgoing_sms";
        file_put_contents("$OUTGOING_DIR_NAME/{$message->id}.json", json_encode($message));

        error_log("\r\n".date('H:i:sa')." Message {$message->id} added to filesystem queue.\r\n",3,__DIR__."\log\\envaya.log");

        $batasPembayaran = date("H:i", strtotime('+5 hours'))." WIB, tanggal ".date("d-m-Y", strtotime('+5 hours'));
            
        return view('pesanberhasil', ['user'=>$user[0]['name'], 'price'=>number_format($payment['price'], 0, ',', '.'), 'sand'=>$payment['sand'], 'phone'=>$request['phone'], 'count'=>$payment['lifetime'], 'total'=>number_format($total, 0, ',', '.'), 'expire'=>$batasPembayaran]);
    }
}

class EnvayaSMS
{
    const ACTION_INCOMING = 'incoming';
    const ACTION_FORWARD_SENT = 'forward_sent';
    const ACTION_SEND_STATUS = 'send_status';
    const ACTION_DEVICE_STATUS = 'device_status';
    const ACTION_TEST = 'test';
    const ACTION_OUTGOING = 'outgoing';
    const ACTION_AMQP_STARTED = 'amqp_started';
        // ACTION_OUTGOING should probably be const ACTION_POLL = 'poll', 
        // but 'outgoing' maintains backwards compatibility between new phone versions with old servers    

    const STATUS_QUEUED = 'queued';
    const STATUS_FAILED = 'failed';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELLED = 'cancelled';
    
    const EVENT_SEND = 'send';
    const EVENT_CANCEL = 'cancel';
    const EVENT_CANCEL_ALL = 'cancel_all';
    const EVENT_LOG = 'log';
    const EVENT_SETTINGS = 'settings';
    
    const DEVICE_STATUS_POWER_CONNECTED = "power_connected";
    const DEVICE_STATUS_POWER_DISCONNECTED = "power_disconnected";
    const DEVICE_STATUS_BATTERY_LOW = "battery_low";
    const DEVICE_STATUS_BATTERY_OKAY = "battery_okay";
    const DEVICE_STATUS_SEND_LIMIT_EXCEEDED = "send_limit_exceeded";
    
    const MESSAGE_TYPE_SMS = 'sms';
    const MESSAGE_TYPE_MMS = 'mms';    
    const MESSAGE_TYPE_CALL = 'call';
    
    // power source constants same as from Android's BatteryManager.EXTRA_PLUGGED
    const POWER_SOURCE_BATTERY = 0;
    const POWER_SOURCE_AC = 1;
    const POWER_SOURCE_USB = 2;
    
    static function escape($val)
    {
        return htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
    }    
    
    private static $request;
    
    static function get_request()
    {
        if (!isset(self::$request))
        {
            $version = @$_POST['version'];     

            if (isset($_POST['action']))
            {
                self::$request = new EnvayaSMS_ActionRequest();
            }
            else
            {
                self::$request = new EnvayaSMS_Request();
            }
            
        }
        return self::$request;
    }                 
}

class EnvayaSMS_Request
{
    public $version;
    
    public $version_name;
    public $sdk_int;
    public $manufacturer;
    public $model;        
    
    function __construct()
    {
        $this->version = (int)@$_POST['version'];
        
        if (preg_match('#/(?P<version_name>[\w\.\-]+) \(Android; SDK (?P<sdk_int>\d+); (?P<manufacturer>[^;]*); (?P<model>[^\)]*)\)#', 
            @$_SERVER['HTTP_USER_AGENT'], $matches))
        {
            $this->version_name = $matches['version_name'];            
            $this->sdk_int = $matches['sdk_int'];
            $this->manufacturer = $matches['manufacturer'];
            $this->model = $matches['model'];
        }        
    }

    function supports_json()
    {
        return $this->version >= 28;
    }
    
    function supports_update_settings()
    {
        return $this->version >= 29;
    }
    
    function get_response_type()
    {
        if ($this->supports_json())
        {
            return 'application/json';
        }
        else
        {
            return 'text/xml';
        }
    }
    
    function render_response($events = null /* optional array of EnvayaSMS_Event objects */) 
    {
        if ($this->supports_json())
        {
            return json_encode(array('events' => $events));
        }
        else
        {        
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>\n";
            echo "<response>";
            
            if ($events)
            {
                foreach ($events as $event)
                {            
                    echo "<messages>";            
                    if ($event instanceof EnvayaSMS_Event_Send)
                    {
                        if ($event->messages)
                        {
                            foreach ($event->messages as $message)
                            {       
                                $type = isset($message->type) ? $message->type : EnvayaSMS::MESSAGE_TYPE_SMS;
                                $id = isset($message->id) ? " id=\"".EnvayaSMS::escape($message->id)."\"" : "";
                                $to = isset($message->to) ? " to=\"".EnvayaSMS::escape($message->to)."\"" : "";        
                                $priority = isset($message->priority) ? " priority=\"".$message->priority."\"" : "";        
                                echo "<$type$id$to$priority>".EnvayaSMS::escape($message->message)."</$type>";
                            }
                        }
                    }
                    echo "</messages>";        
                }
            }
            echo "</response>";
            return ob_get_clean();            
        }
    }
    
    function render_error_response($message)    
    {
        if ($this->supports_json())
        {
            return json_encode(array('error' => array('message' => $message)));
        }
        else
        {
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>\n";
            echo "<response>";
            echo "<error>";
            echo EnvayaSMS::escape($message);
            echo "</error>";
            echo "</response>";
            return ob_get_clean();
        }
    }
}

class EnvayaSMS_ActionRequest extends EnvayaSMS_Request
{   
    private $request_action;
    
    public $settings_version; // integer version of current settings (as provided by server)
    public $phone_number;   // phone number of Android phone 
    public $log;            // app log messages since last successful request
    public $now;            // current time (ms since Unix epoch) according to Android clock
    public $network;        // name of network, like WIFI or MOBILE (may vary depending on phone)
    public $battery;        // battery level as percentage   
    public $power;          // power source integer, see EnvayaSMS::POWER_SOURCE_*
   
    function __construct()
    {
        parent::__construct();
        
        $this->phone_number = $_POST['phone_number'];
        $this->log = $_POST['log'];
        $this->network = @$_POST['network'];
        $this->now = @$_POST['now'];
        $this->settings_version = @$_POST['settings_version'];
        $this->battery = @$_POST['battery'];
        $this->power = @$_POST['power'];
    }
               
    function get_action()
    {
        if (!$this->request_action)
        {
            $this->request_action = $this->_get_action();
        }
        return $this->request_action;
    }
    
    private function _get_action()
    {
        switch (@$_POST['action'])
        {
            case EnvayaSMS::ACTION_INCOMING:
                return new EnvayaSMS_Action_Incoming($this);
            case EnvayaSMS::ACTION_FORWARD_SENT:
                return new EnvayaSMS_Action_ForwardSent($this);                
            case EnvayaSMS::ACTION_OUTGOING:            
                return new EnvayaSMS_Action_Outgoing($this);                
            case EnvayaSMS::ACTION_SEND_STATUS:
                return new EnvayaSMS_Action_SendStatus($this);
            case EnvayaSMS::ACTION_TEST:
                return new EnvayaSMS_Action_Test($this);
            case EnvayaSMS::ACTION_DEVICE_STATUS:
                return new EnvayaSMS_Action_DeviceStatus($this);                
            case EnvayaSMS::ACTION_AMQP_STARTED:
                return new EnvayaSMS_Action_AmqpStarted($this);
            default:
                return new EnvayaSMS_Action($this);
        }
    }            
    
    function is_validated($correct_password)
    {
        $signature = @$_SERVER['HTTP_X_REQUEST_SIGNATURE'];        
        if (!$signature)
        {
            return false;
        }
        
        $is_secure = (!empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
        $protocol = $is_secure ? 'https' : 'http';
        $full_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];    
        
        $correct_signature = $this->compute_signature($full_url, $_POST, $correct_password);           
        
        //error_log(date('H:i:sa')." Correct signature: '$correct_signature'");
        
        return $signature === $correct_signature;
    }

    function compute_signature($url, $data, $password)
    {
        ksort($data);
        
        $input = $url;
        foreach($data as $key => $value)
            $input .= ",$key=$value";

        $input .= ",$password";
        
        return base64_encode(sha1($input, true));            
    }
}

class EnvayaSMS_OutgoingMessage
{
    public $id;             // ID generated by server
    public $to;             // destination phone number
    public $message;        // content of SMS message
    public $priority;       // integer priority, higher numbers will be sent first
    public $type;            // EnvayaSMS::MESSAGE_TYPE_* value (default sms)
}

/*
 * An 'action' is the term for a HTTP request that app sends to the server.
 */
 
class EnvayaSMS_Action
{
    public $type;    
    public $request;
    
    function __construct($request)
    {
        $this->request = $request;
    }
}

class EnvayaSMS_MMS_Part
{
    public $form_name;  // name of form field with MMS part content
    public $cid;        // MMS Content-ID
    public $type;       // Content type
    public $filename;   // Original filename of MMS part on sender phone
    public $tmp_name;   // Temporary file where MMS part content is stored
    public $size;       // Content length
    public $error;      // see http://www.php.net/manual/en/features.file-upload.errors.php

    function __construct($args)
    {
        $this->form_name = $args['name'];
        $this->cid = $args['cid'];
        $this->type = $args['type'];
        $this->filename = $args['filename'];
        
        $file = $_FILES[$this->form_name];
        
        $this->tmp_name = $file['tmp_name'];
        $this->size = $file['size'];
        $this->error = $file['error'];
    }
}

abstract class EnvayaSMS_Action_Forward extends EnvayaSMS_Action
{    
    public $message;        // The message body of the SMS, or the content of the text/plain part of the MMS.
    public $message_type;   // EnvayaSMS::MESSAGE_TYPE_MMS or EnvayaSMS::MESSAGE_TYPE_SMS
    public $mms_parts;      // array of EnvayaSMS_MMS_Part instances
    public $timestamp;      // timestamp of incoming message (added in version 12)

    function __construct($request)
    {
        parent::__construct($request);
        $this->message = @$_POST['message'];
        $this->message_type = $_POST['message_type'];
        $this->timestamp = @$_POST['timestamp'];
        
        if ($this->message_type == EnvayaSMS::MESSAGE_TYPE_MMS)
        {
            $this->mms_parts = array();
            foreach (json_decode($_POST['mms_parts'], true) as $mms_part)
            {
                $this->mms_parts[] = new EnvayaSMS_MMS_Part($mms_part);
            }
        }               
    }
}

class EnvayaSMS_Action_Incoming extends EnvayaSMS_Action_Forward
{    
    public $from;           // Sender phone number

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = EnvayaSMS::ACTION_INCOMING;
        $this->from = $_POST['from'];
    }    
}

class EnvayaSMS_Action_ForwardSent extends EnvayaSMS_Action_Forward
{    
    public $to;           // Recipient phone number

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = EnvayaSMS::ACTION_FORWARD_SENT;
        $this->to = $_POST['to'];
    }    
}

class EnvayaSMS_Action_AmqpStarted extends EnvayaSMS_Action
{
    public $consumer_tag;
    
    function __construct($request)
    {
        parent::__construct($request);
        $this->type = EnvayaSMS::ACTION_AMQP_STARTED;
        $this->consumer_tag = $_POST['consumer_tag'];
    }
}

class EnvayaSMS_Action_Outgoing extends EnvayaSMS_Action
{    
    function __construct($request)
    {
        parent::__construct($request);
        $this->type = EnvayaSMS::ACTION_OUTGOING;        
    }
}

class EnvayaSMS_Action_Test extends EnvayaSMS_Action
{    
    function __construct($request)
    {
        parent::__construct($request);
        $this->type = EnvayaSMS::ACTION_TEST;
    }
}

class EnvayaSMS_Action_SendStatus extends EnvayaSMS_Action
{    
    public $status;     // EnvayaSMS::STATUS_* values
    public $id;         // server ID previously used in EnvayaSMS_OutgoingMessage
    public $error;      // textual description of error (if applicable)
    
    function __construct($request)
    {
        parent::__construct($request);   
        $this->type = EnvayaSMS::ACTION_SEND_STATUS;        
        $this->status = $_POST['status'];
        $this->id = $_POST['id'];
        $this->error = $_POST['error'];
    } 
}

class EnvayaSMS_Action_DeviceStatus extends EnvayaSMS_Action
{    
    public $status;     // EnvayaSMS::DEVICE_STATUS_* values
    
    function __construct($request)
    {
        parent::__construct($request);   
        $this->type = EnvayaSMS::ACTION_DEVICE_STATUS;        
        $this->status = $_POST['status'];
    } 
}

/*
 * An 'event' is the term for something the server sends to the app,
 * either via a response to an 'action', or directly via AMQP.
 */

class EnvayaSMS_Event
{
    public $event;
    
    /*
     * Formats this event as the body of an AMQP message.
     */
    function render()
    {
        return json_encode($this);    
    }
}

/*
 * Instruct the phone to send one or more outgoing messages (SMS or USSD)
 */
class EnvayaSMS_Event_Send extends EnvayaSMS_Event
{    
    public $messages;
    
    function __construct($messages /* array of EnvayaSMS_OutgoingMessage objects */)
    {
        $this->event = EnvayaSMS::EVENT_SEND;
        $this->messages = $messages;
    }
}

/* 
 * Update some of the app's settings.
 */
class EnvayaSMS_Event_Settings extends EnvayaSMS_Event
{
    public $settings;
    
    function __construct($settings /* associative array of key => value pairs (values can be int, bool, or string) */)
    {
        $this->event = EnvayaSMS::EVENT_SETTINGS;
        $this->settings = $settings;
    }
}

/*
 * Cancel sending a message that was previously queued in the app via a 'send' event.
 * Has no effect if the message has already been sent.
 */
class EnvayaSMS_Event_Cancel extends EnvayaSMS_Event
{
    public $id;
    
    function __construct($id /* id of previously created EnvayaSMS_OutgoingMessage object (string) */)
    {
        $this->event = EnvayaSMS::EVENT_CANCEL;
        $this->id = $id;
    }
}

/*
 * Cancels all outgoing messages that are currently queued in the app. Incoming mesages are not affected.
 */
class EnvayaSMS_Event_CancelAll extends EnvayaSMS_Event
{
    function __construct()
    {
        $this->event = EnvayaSMS::EVENT_CANCEL_ALL;
    }
}

/*
 * Appends a message to the app log.
 */
class EnvayaSMS_Event_Log extends EnvayaSMS_Event
{
    public $message;
    
    function __construct($message)
    {
        $this->event = EnvayaSMS::EVENT_LOG;
        $this->message = $message;
    }
}