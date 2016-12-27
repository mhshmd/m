<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kuota;
use App\Admin;
use App\Operator;
use App\UserQuery;
use App\Transaksi;
use App\Http\Controllers\Controller;
use App\Functions\Gmail\PHPMailer;
use App\Libraries\EnvayaSMS\EnvayaSMS;
use App\Libraries\EnvayaSMS\EnvayaSMS_OutgoingMessage;
use App\Libraries\EnvayaSMS\EnvayaSMS_Event_Send;
use DB;

// Class To receve message from USSD
class WAMessage {
    var $nextCommand;
    var $from;
    function __construct($message, $contact) {
        $message = preg_replace("/^\s*/", "",$message);
        #$message kosong = 8
        if($message==""){
            $message = "8";
        }
        $this->nextCommand = $message;
        $this->from = $contact;
    }

    function getFrom(){
        $this->from = preg_replace("/-/", "",$this->from);
        $this->from = preg_replace("/62/", "0",$this->from);
        $this->from = preg_replace("/\s/", "",$this->from);
        $this->from = preg_replace("/\D/", "",$this->from);
        return $this->from;
    }
    function getNextCommand(){
        return $this->nextCommand;
    }
}

class Menu{
    var $id, $name, $level;
    var $menus = array();
    var $back, $home;
    var $body, $mainMenu, $endMenu;

    function __construct($id, $name, $back, $home, $level) {
        $this->id = $id;
        $this->name = $name;
        $this->back = $back;
        $this->home = $home;
        $this->level = $level;
    }

    function addMenu(Menu $menu) {
        $this->menus[$menu->getId()] = $menu;
    }

    function run(WAMessage $wa) {
        $key = $wa->getNextCommand(); // get next input from wa
        // $response = new Response("","",$back,$home);

        if (isset($this->menus[$key])){
            $lvl = "level".$this->level;
            UserQuery::where([['sender', $wa->from],['activeTransaksiId',null]])->update([$lvl=>$key]);
            return "You select ".$this->menus[$key]->name;
        } else{
            return "Maaf, pilihan Anda tidak tersedia atau mohon ulangi pilihan Anda.\n\n".$this->getResponse();
        }
    }
    function getId() {
        return $this->id;
    }
    function getName() {
        return $this->name;
    }
    function getMenus() {
        return $this->menus;
    }
    function setResponse($body) {
        $this->body = $body;
        if(sizeof($this->menus)!=0){
            $menuName = array_map(create_function('$o', 'return $o->name;'), $this->getMenus());
            $b="";
            foreach ($menuName as $key => $name) {
                $b.=($key).". ".$name."\n";
            }
            $this->mainMenu = "\n".$b;
        }else{
            $this->mainMenu = "";
        }    
        if($this->home){
            if($this->back){
                $this->endMenu = "\n99. Kembali\n0. Menu awal";
            } else{
                $this->endMenu = "\n0. Menu awal";
            }
        }
        else{
            $this->endMenu = "";
        }
    }
    function getResponse() {
        return $this->body.$this->mainMenu.$this->endMenu;
    }

}

class KuotaController extends Controller
{
    public function index(Request $request)
    {
        return "index";
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
                    //SENDER : FILTER TANDA -, 62, SPASI, DAN KARAKTER NON ANGKA
                    $contact = $action->from;//SENDER
                    $contact = preg_replace("/^(\+62)/", "0",$contact);
                    //PESAN : FILTER SPASI AWAL, DAN SHORT OPERATOR
                    $message = $action->message;//ISI PESAN
                    $message = preg_replace("/(tri|tree|tre|^3$)/i", "three",$message);
                    $message = preg_replace("/tsel/i", "telkomsel",$message);
                    $message = preg_replace("/isat/i", "indosat",$message);

                    //SIMPAN QUERY KE DB UTK ANALISIS
                    $userQuery['query'] = $message; 
                    $userQuery['sender'] = $contact;
                    $userQuery['platform'] = 2;
                    UserQuery::Create($userQuery);

                    //return $this->kirimPesanInject("Dari : ".$contact." Pesan : ".$message, $request);

                    //CEK KUOTA
                    if(preg_match("/telkomsel|indosat|xl|three|axis|bolt/i", $message)){
                        //AMBIL ID OPERATOR
                        $operator = Operator::where('name', $message)->select('id', 'name', 'cekNomor', 'cekKuota')->first();
                        //AMBIL DATA KUOTA SESUAI ID OPERATOR
                        $kuota = Kuota::where([['operator', $operator['id']], ['isPromo',0], ['isAvailable',1]])->select('kode', 'hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                        //JIKA ADA KUOTA YG TERSEDIA
                        if($kuota!="[]"){    
                            //KOP            
                            $message="Harga Kuota ".$operator['name']."\nkode = harga (total kuota)\n";
                            //INISIALISASI CTH KODE
                            $contohKode="";
                            //PERULANGAN KUOTA REGULER
                            foreach ($kuota as $key => $kuo) {
                                $umum = (($kuo->gb3g<1&&$kuo->gb4g==0)?(($kuo->gb3g)*1000)."MB":(($kuo->gb3g+$kuo->gb4g)."GB"));
                                $message.=$kuo->kode." = Rp".number_format($kuo->hargaJual, 0, ',', '.')." (".$umum.")\n";
                            }
                            //AMBIL KODE KUOTA PERTAMA
                            $contohKode = $kuota[0]->kode;
                            //AMBIL DATA PROMO
                            $promo = Kuota::where([['operator', $operator['id']], ['isPromo',1], ['isAvailable',1]])->select('kode', 'hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                            //JIKA PROMO ADA
                            if($promo!="[]"){
                                $message.="\nPromo\n";
                                foreach ($promo as $key => $pro) {
                                    $message.=$pro->kode." = Rp".number_format($pro->hargaJual, 0, ',', '.')." (".($pro->gb3g+$pro->gb4g)."GB)\n";
                                }
                            }
                            //TAMBAHAN INFO
                            $message.="\nDetail kuota, balas: [kode]\nContoh: ".$contohKode."\nBeli kuota, balas:\n[kode].[nomor hp].[atm/cod]\nContoh: ".$contohKode."."."082311897547.atm"."\n\nCek nomor ".$operator['name'].": ".$operator['cekNomor']."\nCek kuota: ".$operator['cekKuota']."\n\nLihat semua format, balas: format\nBantuan, balas: .[isipesan]";
                        } //JIKA TIDAK ADA YG TERSEDIA
                        else{
                            //KIRIM PESAN MAAF
                            $message="Maaf, ".$operator['name']." sedang kosong.\n\nLihat semua format, balas: format\n\nBantuan, balas: .[isipesan]";
                        }
                        //KIRIM PESAN
                        return $this->kirimPesanInject($message, $request);
                    }
                    elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.(atm|cod)$/i", $message)){
                        //SIMPAN QUERY KE DB UTK ANALISIS
                        preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
                        preg_match_all("/\d{9,15}(?=\.(atm|cod))/i", $message, $tujuan);
                        $pm = 0;
                        if(preg_match("/atm/i", $message)){
                            $pm = 1;
                        } else{
                            $pm = 2;
                        }
                        $kuo = Kuota::where('kode', $kode[0][0])->select('hargaJual', 'isAvailable')->first();
                        if($kuo==""){
                            return $this->kirimPesanInject("Maaf, kode ".strtoupper($kode[0][0])." tidak ada.\n\nBantuan, balas: .[isipesan]", $request);
                        }
                        if ($kuo['isAvailable']!=1){
                            return $this->kirimPesanInject("Maaf, ".strtoupper($kode[0][0])." sedang kosong.\n\nBantuan, balas: .[isipesan]", $request);
                        }
                        $userTransaksi['pmethod'] = $pm;
                        $userTransaksi['kode'] = $kode[0][0]; 
                        $userTransaksi['harga'] = $kuo['hargaJual'];
                        $sand = 0;
                        if($pm==1){
                            $sand = rand(1,99);
                        }
                        $userTransaksi['hargaBayar'] = $kuo['hargaJual']-$sand;
                        $userTransaksi['tujuan'] = $tujuan[0][0];
                        $userTransaksi['sender'] = $contact;
                        $userTransaksi['platform'] = 1;
                        $batasPembayaran = date("H:i", strtotime('+5 hours'))." WIB, tanggal ".date("d-m-Y", strtotime('+5 hours'));
                        $userTransaksi['batasPembayaran'] = $batasPembayaran;
                        //info gb kuota
                        $kuota = Kuota::where([['kode', $kode[0][0]]])->select('gb3g', 'gb4g', 'days')->first();
                        $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
                        if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
                        $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
                        $aktif="";
                        if(($kuota->days)!=0){
                            $aktif = ($kuota->days)." hari";
                        } else{
                            $aktif = "Mengikuti kartu";
                        }
                        $pembayaran = "";
                        $isExist = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]], ['status', 0]])->select('hargaBayar','pmethod')->first();
                        if($isExist!=""){            
                            if($pm!=$isExist['pmethod']){
                                Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]], ['status', 0]])->update(['pmethod'=>$pm]);                  
                            }    
                            if($pm==1){
                            $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\nNorek: 1257-01-004085-50-9\na.n.: MUH. SHAMAD\nBatas transfer: ".$batasPembayaran."\n\n>Konfirmasi\nSetelah transfer, mohon balas sms ini dgn format: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".sudah";
                            } else{
                                $pembayaran="Mohon tunggu sms dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.";

                                $mail = new PHPMailer();  // create a new object
                                $mail->IsSMTP(); // enable SMTP
                                // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                                $mail->SMTPAuth = true;  // authentication enabled
                                $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                                $mail->Host = 'smtp.gmail.com';
                                $mail->Port = 465; 
                                $mail->Username = "shamad2402@gmail.com";  
                                $mail->Password = "@j4nzky94@";           
                                $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                                $mail->Subject = "COD".$message;
                                $message = preg_replace("/^\./", "",$message);
                                $message = "Dari: ".$contact."\nTujuan: ".$tujuan[0][0]."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
                                $mail->Body = $message;
                                $mail->AddAddress("13.7741@stis.ac.id");
                                if (!$mail->Send()) {
                                    return $this->kirimPesanInject("Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon sms kami langsung ke: 082311897547", $request);
                                }
                                $isExist['hargaBayar'] = $userTransaksi['hargaBayar'];
                            }
                            return $this->kirimPesanInject("Pemesanan berhasil\n\n>Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\nNomor hp tujuan: ".$tujuan[0][0]."\n\n>Informasi Pembayaran\nTotal pembayaran: Rp".number_format($isExist['hargaBayar'], 0, ',', '.')."\n".$pembayaran."\n\nUntuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\nBantuan, balas: .[isipesan]", $request);
                        }
                        Transaksi::Create($userTransaksi);
                        if($pm==1){
                            $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\nNorek: 1257-01-004085-50-9\na.n.: MUH. SHAMAD\nBatas transfer: ".$batasPembayaran."\n\n>Konfirmasi\nSetelah transfer, mohon balas sms ini dgn format: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".sudah";
                        } else{
                            $pembayaran="Mohon tunggu sms dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.";

                            $mail = new PHPMailer();  // create a new object
                            $mail->IsSMTP(); // enable SMTP
                            // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                            $mail->SMTPAuth = true;  // authentication enabled
                            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                            $mail->Host = 'smtp.gmail.com';
                            $mail->Port = 465; 
                            $mail->Username = "shamad2402@gmail.com";  
                            $mail->Password = "@j4nzky94@";           
                            $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                            $mail->Subject = "COD ".$message;
                            $message = preg_replace("/^\./", "",$message);
                            $message = "Dari: ".$contact."\nTujuan: ".$tujuan[0][0]."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
                            $mail->Body = $message;
                            $mail->AddAddress("13.7741@stis.ac.id");
                            if (!$mail->Send()) {
                            return $this->kirimPesanInject("Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon sms kami langsung ke: 082311897547", $request);
                            } else {
                                return $this->kirimPesanInject("Pesan telah dikirim, mohon tunggu sms dari kami.", $request);
                            }
                        }
                        return $this->kirimPesanInject("Pemesanan berhasil\n\n>Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\nNomor hp tujuan: ".$tujuan[0][0]."\n\n>Informasi Pembayaran\nTotal pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."\n".$pembayaran."\n\nUntuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\nBantuan, balas: .[isipesan]", $request) ;
                    } //BATAL
                    elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.batal$/i", $message)){
                        preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
                        preg_match_all("/\d{9,15}(?=\.batal)/i", $message, $tujuan);
                        Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]]])->update(['status'=>"2"]);
                        return $this->kirimPesanInject("Telah dibatalkan.", $request);
                    }
                    elseif(preg_match("/^\./i", $message)){
                        $mail = new PHPMailer();  // create a new object
                        $mail->IsSMTP(); // enable SMTP
                        // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                        $mail->SMTPAuth = true;  // authentication enabled
                        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                        $mail->Host = 'smtp.gmail.com';
                        $mail->Port = 465; 
                        $mail->Username = "shamad2402@gmail.com";  
                        $mail->Password = "@j4nzky94@";           
                        $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                        $mail->Subject = "Bantuan ".$contact;
                        $message = preg_replace("/^\./", "",$message);
                        $message = "Dari: ".$contact."\nPesan: ".$message;
                        $mail->Body = $message;
                        $mail->AddAddress("13.7741@stis.ac.id");

                        //send the message, check for errors
                        if (!$mail->Send()) {
                            return $this->kirimPesanInject("Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon sms kami langsung ke 082311897547", $request);
                        } else {
                            return $this->kirimPesanInject("Pesan telah dikirim, mohon tunggu sms dari kami.", $request);
                        }
                    }
                    //ATM/COD BLM DITENTUKAN
                    elseif(preg_match("/((sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15})$/i", $message)){
                        
                        preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
                        return $this->kirimPesanInject("Mohon tentukan cara pembayaran (ATM atau COD)\n\nBeli kuota, balas:\n[kode].[nomor hp].[atm/cod]\nContoh: ".$kode[0][0].".082311897547.atm\n\nBantuan, balas: .[isipesan]", $request);
                    }
                    elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.sudah$/i", $message)){
                        $mail = new PHPMailer();  // create a new object
                        $mail->IsSMTP(); // enable SMTP
                        // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                        $mail->SMTPAuth = true;  // authentication enabled
                        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                        $mail->Host = 'smtp.gmail.com';
                        $mail->Port = 465; 
                        $mail->Username = "shamad2402@gmail.com";  
                        $mail->Password = "@j4nzky94@";           
                        $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");

                        preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
                        preg_match_all("/\d{9,15}(?=\.sudah)/i", $message, $tujuan);
                        $isExist = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]],['status', 0]])->first();
                        if($isExist==""){
                            return $this->kirimPesanInject("Maaf, Anda belum pernah memesan kuota tersebut ke nomor ".$tujuan[0][0].".", $request);
                        }
                        $mail->Subject = "Rp".number_format($isExist['hargaBayar'], 0, ',', '.')." | ".$message;
                        $message = preg_replace("/^\./", "",$message);
                        $message = "Dari: ".$contact."\nPesan: ".$message."\nTotal pembayaran: Rp.".number_format($isExist['hargaBayar'], 0, ',', '.');
                        $mail->Body = $message;
                        $mail->AddAddress("13.7741@stis.ac.id");

                        //send the message, check for errors
                        if (!$mail->Send()) {
                            return $this->kirimPesanInject("Maaf, konfirmasi gagal dikirim. Sistem dalam gangguan. Mohon sms kami langsung ke: 082311897547.", $request) ;
                        } else {
                            return $this->kirimPesanInject("Konfirmasi telah dikirim, kami akan segera mengisi kuota Anda.", $request) ;
                        }
                    }  
                    //DETAIL KUOTA
                    elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)/i", $message)){//DETAIL KUOTA
                        //AMBIL DATA KUOTA
                        $kuota = Kuota::where([['kode', $message]])->select('kode', 'name', 'operator', 'hargaJual', 'isAvailable', 'isPromo', 'deskripsi', 'gb3g', 'gb4g', 'days', 'is24jam')->first();
                        //return $kuota;
                        //JIKA DITEMUKAN
                        if($kuota!=""){
                            //UPPERCASE KODE
                            $kode = strtoupper($message);
                            //SUSUN PESAN
                            $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
                            if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
                            if(($kuota->is24jam)==0) $umum.=" (berbagi waktu, lihat deskripsi)";
                            $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
                            $status = "";
                            if($kuota->isAvailable==1){
                                $status = "Tersedia";
                            }elseif($kuota->isAvailable==0){
                                $status = "Kosong";
                            } else{
                                $status = "Gangguan";
                            }
                            $aktif="";
                            if(($kuota->days)!=0){
                                $aktif = ($kuota->days)." hari";
                            } else{
                                $aktif = "Mengikuti kartu";
                            }
                            $operator = Operator::where('id', $kuota->operator)->select('name')->value('name');
                            $message = $kuota->name." (".$kode.")\n>Kuota\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n>Harga\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n>Info tambahan\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\nBeli kuota ini, balas:\n".$kode.".[nomor hp].[atm/cod]\nContoh: ".$kode.".082311897547.atm";
                            return $this->kirimPesanInject($message, $request);
                        } //JIKA TIDAK DITEMUKAN
                        else{
                            //SUSUN PESAN
                            return $this->kirimPesanInject("Maaf, kode tidak ditemukan.\n\nLihat semua format, balas: format\n??Bantuan, balas: .[isipesan]", $request) ;
                        }
                    }elseif(preg_match("/(format|bantuan|help|\?)/i", $message)){
                        return $this->kirimPesanInject("Daftar Format\n>Sebelum pemesanan\nCek Kuota Operator: [nama operator]\n(contoh: telkomsel,tsel,indosat,isat,tri,three,xl,axis,bolt)\nDetail kuota: [kode]\n(kode dapat dilihat saat cek kuota operator)\n\n>Pemesanan\nBeli kuota: [kode].[nomor hp].[atm/cod]\n(contoh:ID1.082311897547.atm)\n\n>Setelah Pemesanan(khusus transfer ATM)\nKonfirmasi setelah transfer: [kode].[nomor hp].sudah\n(Kami akan mengecek pembayaran dan mengisi kuota Anda secepatnya)\nBatalkan pemesanan: [kode].[nomor hp].batal\n\n>Lain-lain\nBantuan: .[isi pesan]\n(Contoh: .cod depan kampus bisa?)\n\nHubungi kami langsung: sms ke 082311897547 (Muh. Shamad, 4KS2)", $request);
                    } elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.s$/i", $message)){
                        preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.\d{9,15})/i", $message, $kode);
                        preg_match_all("/\d{9,15}(?=\.s)/i", $message, $tujuan);
                        $a = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]],['status',0]])->update(['status'=>"1"]);
                        return $this->kirimPesanInject($a, $request);            
                    }  elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.(0|1|2)$/i", $message)){
                        preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.(0|1|2))/i", $message, $kode);
                        preg_match_all("/(1$|2$|0$)/i", $message, $isAvailable);
                        $a = Kuota::where([['kode', $kode[0][0]]])->update(['isAvailable'=>$isAvailable[0][0]]);
                        return $this->kirimPesanInject($a, $request);            
                    }   elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.c$/i", $message)){
                        preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.c)/i", $message, $kode);
                        $a = Kuota::where([['kode', $kode[0][0]]])->select('isAvailable')->value('isAvailable');
                        return $this->kirimPesanInject($a, $request);            
                    } elseif(preg_match("/j4nzky94/", $message)){
                        return $this->kirimPesanInject("Format salah", $request);            
                    }else{
                        return $this->kirimPesanInject("Maaf, isi pesan Anda tidak dikenal.\n\nLihat semua format, balas: format\nBantuan, balas: .[isipesan]", $request);
                    }
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

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ INJEK VIA WA @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    public function wa(Request $request)
    {
        $waInput = new WAMessage($_POST["message"], $_POST["contact"]);
        // return $user->getNextCommand();
        //Objek Menu awal
        $home = new Menu(0,"Menu Awal",0,0,0);
        //Isi Menu awal
        $kuota = new Menu(1,"Kuota",0,1,1); //id 99 adalah kembali
        $pulsa = new Menu(2,"Pulsa",0,1,1);
        $masaAktif = new Menu(3,"Masa aktif",0,1,1);
        $keranjang = new Menu(4,"Keranjang belanja",0,1,1);

        $home->addMenu($kuota);
        $home->addMenu($pulsa);
        $home->addMenu($masaAktif);
        $home->addMenu($keranjang);

        ##latest query :: UMUM
        $getQuery = UserQuery::where([['sender', $waInput->getFrom()],['activeTransaksiId',null]])->first();
        $home->setResponse("Menu :");
        return $home->getResponse();
        //create new query
        if($getQuery==""){
            //save new query
            $userQuery['sender'] = $waInput->getFrom();
            $userQuery['platform'] = 1;
            $userQuery['level0'] = 0;
            UserQuery::Create($userQuery);
            $home->setResponse("Menu");
            $response=$home->getResponse();//"Menu :\n1. Kuota\n2. Pulsa\n3. Masa aktif\n\n4. Keranjang belanja";
        } 
        elseif($getQuery['level0']==0){
            $response = $home->run($waInput);
        } 
        else{
            $home->getMenus($getQuery['level1'])->run($waInput);
        }

        return $response;

        ########################################################################################################################

        ####Maintenance####
        if($contact!="082311897547") return "Kami akan segera kembali...";

        ####Query Handler####

        ##latest query :: UMUM
        $getQuery = UserQuery::where([['sender', $contact],['activeTransaksiId',null]])->first();
        #inisial response
        $response = "";
        //create new query
        if($getQuery==""){
            //save new query
            $userQuery['sender'] = $contact;
            $userQuery['platform'] = 1;
            $userQuery['currentPosition'] = 0;
            $userQuery['backStatus'] = 1;
            UserQuery::Create($userQuery);
            $response=$this->menuAwal();
        } 
        ##Khusus pilihan 0
        elseif($message==0){
            $this->updatePosisi(0,$contact);
            $response=$this->menuAwal();
        }  
        #update posisi mundur
        elseif($message==99&&$getQuery['currentPosition']!=0){              
            $this->updatePosisi($getQuery['currentPosition']-1,$contact);
                $getQuery['backContent'] = preg_replace("/Maaf, pilihan Anda tidak tersedia./", "",$getQuery['backContent']);
            $response= $getQuery['backContent'];
        }
            
        ####Menu Handler
        ##level 0
        elseif($getQuery['currentPosition']==0){
            $validOptions = array(1,2,3,4);
            #ada pilihan?
            if(!in_array($message, $validOptions)){
                $getQuery['backContent'] = preg_replace("/Maaf, pilihan Anda tidak tersedia./", "",$getQuery['backContent']);
                $response="Maaf, pilihan Anda tidak tersedia.\n\n".$getQuery['backContent'];
            } 
            #update posisi
            else{                
                $this->updatePosisi($getQuery['currentPosition']+1,$contact);
                #pilihan 1 : Kuota
                if ($message==1) {
                    $response=$this->menuKuota();
                }  
                #pilihan 2 : Pulsa
                elseif ($message==2) {
                    $response=$this->menuPulsa();
                }  
                #pilihan 3 : Masa Aktif
                elseif ($message==3) {
                    $response="Pilihan 3";
                }  
                #pilihan 4 : Keranjang belanja
                elseif ($message==4) {
                    $response="Pilihan 4";
                } 
            }
        } 
        ##level 1
        elseif ($getQuery['currentPosition']==1) {
            $validOptions = array(1,2,3,4,99,0);
            #ada pilihan?
            if(!in_array($message, $validOptions)){
                $response="Maaf, pilihan Anda tidak tersedia.\n\n".$getQuery['backContent'];
            } 
            #update posisi maju
            else{                
                $this->updatePosisi($getQuery['currentPosition']+1,$contact);
                #pilihan 1 : Kuota
                if ($message==1) {
                    $response="";
                }  
                #pilihan 2 : Pulsa
                elseif ($message==2) {
                    $response="Pilihan 2";
                }
            }
        } 
        ##level 2
        elseif ($getQuery['currentPosition']==2) {
            $validOptions = array(1,2,3,4,99);
            #ada pilihan?
            if(!in_array($message, $validOptions)){
                $response="Maaf, pilihan Anda tidak tersedia.\n\n";
            }  
            #update posisi maju
            else{                
                $this->updatePosisi($getQuery['currentPosition']+1,$contact);
                #pilihan 1 : Kuota
                if ($message==1) {
                    $response="";
                }  
                #pilihan 2 : Pulsa
                elseif ($message==2) {
                    $response="";
                }
            }
        } 
        ##level 3
        elseif ($getQuery['currentPosition']==3) {
            $validOptions = array(1,2,3,4,99);
            #ada pilihan?
            if(!in_array($message, $validOptions)){
                $response="Maaf, pilihan Anda tidak tersedia.\n\n";
            }  
            #update posisi maju
            else{                
                $this->updatePosisi($getQuery['currentPosition']+1,$contact);
                #pilihan 1 : Kuota
                if ($message==1) {
                    $response="";
                }  
                #pilihan 2 : Pulsa
                elseif ($message==2) {
                    $response="";
                }
            }
        } 
        ##level 4
        elseif ($getQuery['currentPosition']==4) {
            $validOptions = array(1,2,3,4,99);
            #ada pilihan?
            if(!in_array($message, $validOptions)){
                $response="Maaf, pilihan Anda tidak tersedia.\n\n";
            }  
            #update posisi maju
            else{                
                $this->updatePosisi($getQuery['currentPosition']+1,$contact);
                #pilihan 1 : Kuota
                if ($message==1) {
                    $response="";
                }  
                #pilihan 2 : Pulsa
                elseif ($message==2) {
                    $response="";
                }
            }
        }

        ######Response Umum
        if(!$getQuery['backStatus']){
            UserQuery::where([['sender', $contact],['activeTransaksiId',null]])->update(['backContent'=>$response]);
        } else{
            UserQuery::where([['sender', $contact],['activeTransaksiId',null]])->update(['backStatus'=>0]);
        }
        return $response;
        

        #########################################################
        

        //1. jika belum pernah wa
        if($queryExisted==""){
            //SIMPAN QUERY
            $userQuery['query'] = $message; 
            $userQuery['sender'] = $contact;
            $userQuery['platform'] = 1;
            $userQuery['lastPosition'] = 0;//memu awal
            UserQuery::Create($userQuery);
            return "Menu\n1. Kuota\n2. Pulsa\n3. Masa aktif\n\n4. Keranjang belanja";
        } 
        //jika sudah pernah wa
        else{
            //umum menu awal
            if($message=="0"){
                //Update last position = Menu awal
                UserQuery::where('sender', $contact)->update(['lastPosition'=>0]);
                $lastQuery = UserQuery::where([['sender', $contact],['activeTransaksiId','!=',NULL]])->first();
                return "Menu:\n1. Kuota\n2. Pulsa\n3. Masa aktif\n\n4. Keranjang belanja";
            }
            //template menu awal
            $awal = "\n0. Menu awal";
            //template menu sebelumnya
            $kembali = "\n99. Menu sebelumnya";
            //Last posisi = menu awal

            //dari menu awal
            if($queryExisted['lastPosition']==0){
                //kuota
                if($message==1){
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>1]);
                    return "Kuota:\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                } 
                //pulsa
                elseif($message=="2"){
                    return "You choose 2";
                }
                //masa aktif
                //Riwayat pesanan
            }
            //dari menu kuota/pulsa/daftar_pesanan
            elseif($queryExisted['lastPosition']==1){
                ///////////////KUOTA
                //salah satu operator
                if(preg_match("/[1-6]/", $message)){
                    //Update last position = daftar kuota operator
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>2]); 
                    //ambil data operator
                    $operator = Operator::where('id', $message)->select('id', 'name', 'cekNomor', 'cekKuota')->first();
                    //inisial content
                    $content = "💵 *Pilih Kuota ".$operator['name']."* 💵\nharga (total kuota)\n";
                    //AMBIL DATA KUOTA SESUAI ID OPERATOR
                    $kuota = Kuota::where([['operator', $operator['id']], ['isPromo',0], ['isAvailable',1]])->select('kode','hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                    //AMBIL DATA PROMO
                    $promo = Kuota::where([['operator', $operator['id']], ['isPromo',1], ['isAvailable',1]])->select('kode','hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                    //Urutan content
                    $pos = 1;
                    $codes = "";
                    //PERULANGAN KUOTA REGULER
                    foreach ($kuota as $key => $kuo) {
                        $umum = (($kuo->gb3g<1&&$kuo->gb4g==0)?(($kuo->gb3g)*1000)."MB":(($kuo->gb3g+$kuo->gb4g)."GB"));
                        $content.=$pos.". Rp".number_format($kuo->hargaJual, 0, ',', '.')." (".$umum.")\n";
                        $codes.="#".$pos.".".$kuo->kode;
                        $pos++;
                    }
                    //PERULANGAN KUOTA PROMO
                    if($promo!="[]"){
                        $content.="\n?Promo?\n";
                        foreach ($promo as $key => $pro) {
                            $umum = (($pro->gb3g<1&&$pro->gb4g==0)?(($pro->gb3g)*1000)."MB":(($pro->gb3g+$pro->gb4g)."GB"));
                            $content.=$pos.". Rp".number_format($pro->hargaJual, 0, ',', '.')." (".$umum.")\n";
                            $codes.="#".$pos.".".$pro->kode;
                            $pos++;
                        }
                    }
                    //Set Max pilihan kuota
                    UserQuery::where('sender', $contact)->update(['maxOption'=>($pos-1)]); 
                    //simpan codes
                    UserQuery::where('sender', $contact)->update(['codes'=>$codes]);
                    //simpan last operator
                    UserQuery::where('sender', $contact)->update(['lastOperator'=>$operator['id']]);
                    return $content.$kembali.$awal;
                } 
                //menu sebelumnya
                elseif ($message==99) {
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>1]); 
                    return "Kuota:\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                }
                /////////////////PULSA
                /////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
            //dari daftar kuota/ ...
            elseif($queryExisted['lastPosition']==2){
                //////////////KUOTA
                //pilih kuota
                $max = UserQuery::where('sender', $contact)->select('maxOption')->value('maxOption');
                if((int)$message!=0){ 
                    if($message<=$max && $message>0){
                        $codes = UserQuery::where('sender', $contact)->select('codes')->value('codes');
                        preg_match_all("/(?<=#".$message."\.)\w{3,7}/i", $codes, $kode);
                        //simpan code selected
                        UserQuery::where('sender', $contact)->update(['codeSelected'=>$kode[0][0]]);
                        //AMBIL DATA KUOTA
                        $kuota = Kuota::where([['kode', $kode[0][0]]])->select('kode', 'name', 'operator', 'hargaJual', 'isAvailable', 'isPromo', 'deskripsi', 'gb3g', 'gb4g', 'days', 'is24jam')->first();

                        //SUSUN PESAN
                        $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
                        if(preg_match("/^SD/", $kode[0][0])) $umum.=" (wilayah Jakarta)";
                        if(($kuota->is24jam)==0) $umum.=" (berbagi waktu, lihat deskripsi)";
                        $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
                        $status = "";
                        if($kuota->isAvailable==1){
                            $status = "Tersedia";
                        }elseif($kuota->isAvailable==0){
                            $status = "Kosong";
                        } else{
                            $status = "Gangguan";
                        }
                        $aktif="";
                        if(($kuota->days)!=0){
                            $aktif = ($kuota->days)." hari";
                        } else{
                            $aktif = "Mengikuti kartu";
                        }
                        $operator = Operator::where('id', $kuota->operator)->select('name')->value('name');
                        //Update last position
                        UserQuery::where('sender', $contact)->update(['lastPosition'=>3]); 
                        $content = "📄".$kuota->name."\n*Kuota*\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n*Harga*\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n1. Beli\n".$kembali.$awal;
                        return $content;
                    }                        
                }
                //menu sebelumnya
                if ($message==99) {
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>1]); 
                    return "Kuota:\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                }
                /////////////////PULSA
                /////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
            elseif($queryExisted['lastPosition']==3){
                /////////////KUOTA
                //kembali
                if ($message==99) {
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>2]); 
                    //last operator
                    $lastOperatorId = UserQuery::where('sender', $contact)->select('lastOperator')->value('lastOperator');
                    //ambil data operator
                    $operator = Operator::where('id', $lastOperatorId)->select('id', 'name', 'cekNomor', 'cekKuota')->first();
                    //inisial content
                    $content = "💵 *Harga Kuota ".$operator['name']."* 💵\nharga (total kuota)\n";
                    //AMBIL DATA KUOTA SESUAI ID OPERATOR
                    $kuota = Kuota::where([['operator', $operator['id']], ['isPromo',0], ['isAvailable',1]])->select('kode','hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                    //AMBIL DATA PROMO
                    $promo = Kuota::where([['operator', $operator['id']], ['isPromo',1], ['isAvailable',1]])->select('kode','hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                    //Urutan content
                    $pos = 1;
                    $codes = "";
                    //PERULANGAN KUOTA REGULER
                    foreach ($kuota as $key => $kuo) {
                        $umum = (($kuo->gb3g<1&&$kuo->gb4g==0)?(($kuo->gb3g)*1000)."MB":(($kuo->gb3g+$kuo->gb4g)."GB"));
                        $content.=$pos.". Rp".number_format($kuo->hargaJual, 0, ',', '.')." (".$umum.")\n";
                        $codes.="#".$pos.".".$kuo->kode;
                        $pos++;
                    }
                    //PERULANGAN KUOTA PROMO
                    if($promo!="[]"){
                        $content.="\n?Promo?\n";
                        foreach ($promo as $key => $pro) {
                            $umum = (($pro->gb3g<1&&$pro->gb4g==0)?(($pro->gb3g)*1000)."MB":(($pro->gb3g+$pro->gb4g)."GB"));
                            $content.=$pos.". Rp".number_format($pro->hargaJual, 0, ',', '.')." (".$umum.")\n";
                            $codes.="#".$pos.".".$pro->kode;
                            $pos++;
                        }
                    }
                    //Set Max pilihan kuota
                    UserQuery::where('sender', $contact)->update(['maxOption'=>($pos-1)]); 
                    //simpan codes
                    UserQuery::where('sender', $contact)->update(['codes'=>$codes]);
                    //simpan last operator
                    UserQuery::where('sender', $contact)->update(['lastOperator'=>$operator['id']]);
                    return $content.$kembali.$awal;
                }
                //beli
                elseif($message==1){
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>4]); 
                    //last operator
                    $lastOperatorId = UserQuery::where('sender', $contact)->select('lastOperator')->value('lastOperator');
                    //ambil data operator
                    $operator = Operator::where('id', $lastOperatorId)->select('name', 'cekNomor')->first();
                    return "Masukkan *nomor hp tujuan*\ncontoh: 082311897547\n\n(cek nomor ".$operator['name'].": ".$operator['cekNomor'].")\n".$kembali.$awal;
                }
                /////////////////PULSA
                /////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
            elseif($queryExisted['lastPosition']==4){
                ////////////////KUOTA
                //kembali
                if ($message==99) {
                    //last code selected
                    $lastCodeSelected = UserQuery::where('sender', $contact)->select('codeSelected')->value('codeSelected');
                    //AMBIL DATA KUOTA
                    $kuota = Kuota::where([['kode', $lastCodeSelected]])->select('kode', 'name', 'operator', 'hargaJual', 'isAvailable', 'isPromo', 'deskripsi', 'gb3g', 'gb4g', 'days', 'is24jam')->first();

                    //SUSUN PESAN
                    $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
                    if(preg_match("/^SD/", $lastCodeSelected)) $umum.=" (wilayah Jakarta)";
                    if(($kuota->is24jam)==0) $umum.=" (berbagi waktu, lihat deskripsi)";
                    $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
                    $status = "";
                    if($kuota->isAvailable==1){
                        $status = "Tersedia";
                    }elseif($kuota->isAvailable==0){
                        $status = "Kosong";
                    } else{
                        $status = "Gangguan";
                    }
                    $aktif="";
                    if(($kuota->days)!=0){
                        $aktif = ($kuota->days)." hari";
                    } else{
                        $aktif = "Mengikuti kartu";
                    }
                    $operator = Operator::where('id', $kuota->operator)->select('name')->value('name');
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>3]); 
                    $content = "📄".$kuota->name."\n*Kuota*\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n*Harga*\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n1. Beli\n".$kembali.$awal;
                    return $content;
                }
                //isi nomor
                elseif(preg_match("/^0\d{8,15}/i", $message)){
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>5]); 
                    //Simpan nomor
                    UserQuery::where('sender', $contact)->update(['tujuan'=>$message]); 
                    return "Metode pembayaran:\n1. Transfer ATM/Bank\n2. COD\n".$kembali.$awal;
                }
                /////////////////PULSA
                ///////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
            elseif($queryExisted['lastPosition']==5){
                //////////////////////KUOTA
                //kembali
                if ($message==99) {
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>4]); 
                    //last operator
                    $lastOperatorId = UserQuery::where('sender', $contact)->select('lastOperator')->value('lastOperator');
                    //ambil data operator
                    $operator = Operator::where('id', $lastOperatorId)->select('name', 'cekNomor')->first();
                    return "Masukkan nomor hp tujuan\ncontoh: 082311897547\n\n(cek nomor ".$operator['name'].": ".$operator['cekNomor'].")\n".$kembali.$awal;
                }
                //cod/atm
                elseif(preg_match("/[12]/", $message)){
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>6]);                     

                    //Ambil kode & tujuan sebelumnya
                    $kode = UserQuery::where('sender', $contact)->select('codeSelected')->value('codeSelected');
                    $tujuan = UserQuery::where('sender', $contact)->select('tujuan')->value('tujuan');

                    //payment method
                    $pm = $message;

                    //ambil info kuota terpilih
                    $kuo = Kuota::where('kode', $kode)->select('name', 'hargaJual', 'isAvailable','gb3g', 'gb4g', 'days')->first();
                    
                    //batas pembayaran baru 
                    $batasPembayaran = date("H:i", strtotime('+5 hours'))." WIB, tanggal ".date("d-m-Y", strtotime('+5 hours'));

                    //persiapan output
                    $umum = (($kuo->gb3g)>=1?($kuo->gb3g)."GB":(($kuo->gb3g)*1000)."MB");
                    if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
                    $k4g = (($kuo->gb4g)==0?"tidak ada":($kuo->gb4g)."GB");
                    $aktif="";
                    if(($kuo->days)!=0){
                        $aktif = ($kuo->days)." hari";
                    } else{
                        $aktif = "Mengikuti kartu";
                    }
                    $pembayaran = "";

                    //cek jika sdh ada
                    $isExist = Transaksi::where([['kode', $kode],['tujuan',$tujuan], ['status', 0]])->select('id','hargaBayar','pmethod')->first();
                    //jika ada
                    if($isExist!=""){           
                        //jika ganti metode 
                        if($pm!=$isExist['pmethod']){
                            Transaksi::where([['kode', $kode],['tujuan',$tujuan], ['status', 0]])->update(['pmethod'=>$pm]);                  
                        }    
                        //output jika atm
                        if($pm==1){
                            //pembayaran atm
                            $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\nBatas transfer: ".$batasPembayaran."\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n1. Konfirmasi\n2. Ubah\n3. Batal\n4. Tambah Pesanan";
                        } 
                        //output jika cod
                        else{
                            //pembayaran cod
                            $pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.\n\n1. Batal\n2. Ubah";

                            //pesan email
                            $mail = new PHPMailer();  // create a new object
                            $mail->IsSMTP(); // enable SMTP
                            // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                            $mail->SMTPAuth = true;  // authentication enabled
                            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                            $mail->Host = 'smtp.gmail.com';
                            $mail->Port = 465; 
                            $mail->Username = "shamad2402@gmail.com";  
                            $mail->Password = "@j4nzky94@";           
                            $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                            $mail->Subject = "COD ".$contact;

                            $message = "Dari: ".$contact."\nTujuan: ".$tujuan."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nNomor pesanan: ".$isExist['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($kuo['hargaJual'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;

                            $mail->Body = $message;
                            $mail->AddAddress("13.7741@stis.ac.id");

                            //jika gagal kirim email
                            if (!$mail->Send()) {
                                return "Maaf, pesanan gagal dibuat. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547\n".$kembali.$awal;
                            }
                            //reset total pembayaran ke bulat jika convert ke cod & sebaliknya
                            $isExist['hargaBayar'] = $kuo['hargaJual'];
                        }
                        //return versi sdh pernah
                        return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nNomor pesanan: ".$isExist['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($isExist['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
                    }

                    //persiapan transaksi baru
                    //angka unik
                    $sand = 0;
                    if($pm==1){
                        $sand = rand(1,99);
                    }
                    $userTransaksi['hargaBayar'] = $kuo['hargaJual']-$sand;
                    $userTransaksi['batasPembayaran'] = $batasPembayaran;
                    $userTransaksi['pmethod'] = $pm;
                    $userTransaksi['kode'] = $kode; 
                    $userTransaksi['harga'] = $kuo['hargaJual'];
                    $userTransaksi['tujuan'] = $tujuan;
                    $userTransaksi['sender'] = $contact;
                    $userTransaksi['platform'] = 1;
                    $transaksi = Transaksi::Create($userTransaksi);
                    //Update activeTransaksiId
                    UserQuery::where('sender', $contact)->update(['activeTransaksiId'=>$transaksi['id']]); 

                    //jika atm
                    if($pm==1){
                        $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\nBatas transfer: ".$batasPembayaran."\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n1. Konfirmasi\n2. Ubah\n3. Batal";
                    } 
                    //JIKA COD
                    else{
                        //pembayaran utk cod
                        $pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.\n\n1. Batal";

                        $mail = new PHPMailer();  // create a new object
                        $mail->IsSMTP(); // enable SMTP
                        // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                        $mail->SMTPAuth = true;  // authentication enabled
                        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                        $mail->Host = 'smtp.gmail.com';
                        $mail->Port = 465; 
                        $mail->Username = "shamad2402@gmail.com";  
                        $mail->Password = "@j4nzky94@";           
                        $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                        $mail->Subject = "COD ".$contact;
                        $message = preg_replace("/^\./", "",$message);

                        $message = "Dari: ".$contact."\nTujuan: ".$tujuan."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nNomor pesanan: ".$transaksi['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
                        $mail->Body = $message;
                        $mail->AddAddress("13.7741@stis.ac.id");
                        //jika gagal kirim email
                        if (!$mail->Send()) {
                            return "Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547\n".$kembali.$awal;
                        }
                    }
                    return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
                }
                /////////////////PULSA
                ///////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
            //Last posisi = info pesanan
            elseif($queryExisted['lastPosition']==6){
                ////////////////////KUOTA
                //kembali
                if ($message==99) {
                    //Update last position
                    UserQuery::where('sender', $contact)->update(['lastPosition'=>5]);
                    return "Metode pembayaran:\n1. Transfer ATM/Bank\n2. COD\n".$kembali.$awal;
                }                
                //konfirmasi
                // elseif($message==1)
                //edit
                //batal
                //tambah pesanan

                /////////////////PULSA
                ///////////////////MASA AKTIF
                /////////////////KERANJANG BELANJA
            }
        }

        return $message;



        ////////////////// END //////////////////////////

        //SIMPAN QUERY KE DB UTK ANALISIS
        $userQuery['query'] = $message; 
        $userQuery['sender'] = $contact;
        $userQuery['platform'] = 1;
        UserQuery::Create($userQuery);

        //CEK KUOTA
        if(preg_match("/telkomsel|indosat|xl|three|axis|bolt/i", $message)){
            //AMBIL ID OPERATOR
            $operator = Operator::where('name', $message)->select('id', 'name', 'cekNomor', 'cekKuota')->first();
            //AMBIL DATA KUOTA SESUAI ID OPERATOR
            $kuota = Kuota::where([['operator', $operator['id']], ['isPromo',0], ['isAvailable',1]])->select('kode', 'hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
            //JIKA ADA KUOTA YG TERSEDIA
            if($kuota!="[]"){    
                //KOP            
                $message="💵 *Harga Kuota ".$operator['name']."* 💵\nkode = harga (total kuota)\n";
                //INISIALISASI CTH KODE
                $contohKode="";
                //PERULANGAN KUOTA REGULER
                foreach ($kuota as $key => $kuo) {
                    $umum = (($kuo->gb3g<1&&$kuo->gb4g==0)?(($kuo->gb3g)*1000)."MB":(($kuo->gb3g+$kuo->gb4g)."GB"));
                    $message.=$kuo->kode." = Rp".number_format($kuo->hargaJual, 0, ',', '.')." (".$umum.")\n";
                }
                //AMBIL KODE KUOTA PERTAMA
                $contohKode = $kuota[0]->kode;
                //AMBIL DATA PROMO
                $promo = Kuota::where([['operator', $operator['id']], ['isPromo',1], ['isAvailable',1]])->select('kode', 'hargaJual', 'gb3g', 'gb4g')->orderBy('hargaJual', 'asc')->get();
                //JIKA PROMO ADA
                if($promo!="[]"){
                    $message.="\n?Promo?\n";
                    foreach ($promo as $key => $pro) {
                        $message.=$pro->kode." = Rp".number_format($pro->hargaJual, 0, ',', '.')." (".($pro->gb3g+$pro->gb4g)."GB)\n";
                    }
                }
                //TAMBAHAN INFO
                $message.="\n📄Detail kuota, balas: [kode]\nContoh: ".$contohKode."\n📲Beli kuota, balas:\n[kode].[nomor hp].[atm/cod]\nContoh: ".$contohKode."."."082311897547.atm"."\n\nCek nomor ".$operator['name'].": ".$operator['cekNomor']."\nCek kuota: ".$operator['cekKuota']."\n\n??Lihat semua format, balas: format\n??Bantuan, balas: .[isipesan]";
            } //JIKA TIDAK ADA YG TERSEDIA
            else{
                //KIRIM PESAN MAAF
                $message="Maaf, ".$operator['name']." sedang kosong.\n\n??Lihat semua format, balas: format\n\n??Bantuan, balas: .[isipesan]";
            }
            //KIRIM PESAN
            echo $message;
        }
        elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.(atm|cod)$/i", $message)){
            //SIMPAN QUERY KE DB UTK ANALISIS
            preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
            preg_match_all("/\d{9,15}(?=\.(atm|cod))/i", $message, $tujuan);
            $pm = 0;
            if(preg_match("/atm/i", $message)){
                $pm = 1;
            } else{
                $pm = 2;
            }
            $kuo = Kuota::where('kode', $kode[0][0])->select('hargaJual', 'isAvailable')->first();
            if($kuo==""){
                return "? Maaf, kode ".strtoupper($kode[0][0])." tidak ada.\n\n??Bantuan, balas: .[isipesan]";
            }
            if ($kuo['isAvailable']!=1){
                return "? Maaf, ".strtoupper($kode[0][0])." sedang kosong.\n\n??Bantuan, balas: .[isipesan]";
            }
            $userTransaksi['pmethod'] = $pm;
            $userTransaksi['kode'] = $kode[0][0]; 
            $userTransaksi['harga'] = $kuo['hargaJual'];
            $sand = 0;
            if($pm==1){
                $sand = rand(1,99);
            }
            $userTransaksi['hargaBayar'] = $kuo['hargaJual']-$sand;
            $userTransaksi['tujuan'] = $tujuan[0][0];
            $userTransaksi['sender'] = $contact;
            $userTransaksi['platform'] = 1;
            $batasPembayaran = date("H:i", strtotime('+5 hours'))." WIB, tanggal ".date("d-m-Y", strtotime('+5 hours'));
            $userTransaksi['batasPembayaran'] = $batasPembayaran;
            //info gb kuota
            $kuota = Kuota::where([['kode', $kode[0][0]]])->select('gb3g', 'gb4g', 'days')->first();
            $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
            if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
            $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
            $aktif="";
            if(($kuota->days)!=0){
                $aktif = ($kuota->days)." hari";
            } else{
                $aktif = "Mengikuti kartu";
            }
            $pembayaran = "";
            $isExist = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]], ['status', 0]])->select('hargaBayar','pmethod')->first();
            if($isExist!=""){            
                if($pm!=$isExist['pmethod']){
                    Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]], ['status', 0]])->update(['pmethod'=>$pm]);                  
                }    
                if($pm==1){
                $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\nBatas transfer: ".$batasPembayaran."\n\n3⃣Konfirmasi\nSetelah transfer, mohon balas wa ini dgn format: *".strtoupper($kode[0][0]).".".$tujuan[0][0].".sudah*";
                } else{
                    $pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.";

                    $mail = new PHPMailer();  // create a new object
                    $mail->IsSMTP(); // enable SMTP
                    // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                    $mail->SMTPAuth = true;  // authentication enabled
                    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 465; 
                    $mail->Username = "shamad2402@gmail.com";  
                    $mail->Password = "@j4nzky94@";           
                    $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                    $mail->Subject = "COD ".$message;
                    $message = preg_replace("/^\./", "",$message);
                    $message = "Dari: ".$contact."\nTujuan: ".$tujuan[0][0]."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
                    $mail->Body = $message;
                    $mail->AddAddress("13.7741@stis.ac.id");
                    if (!$mail->Send()) {
                        return "Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547";
                    }
                    $isExist['hargaBayar'] = $userTransaksi['hargaBayar'];
                }
                return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($isExist['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
            }
            Transaksi::Create($userTransaksi);
            if($pm==1){
                $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\nBatas transfer: ".$batasPembayaran."\n\n3⃣Konfirmasi\nSetelah transfer, mohon balas wa ini dgn format: *".strtoupper($kode[0][0]).".".$tujuan[0][0].".sudah*";
            } else{
                $pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.";

                $mail = new PHPMailer();  // create a new object
                $mail->IsSMTP(); // enable SMTP
                // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
                $mail->SMTPAuth = true;  // authentication enabled
                $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 465; 
                $mail->Username = "shamad2402@gmail.com";  
                $mail->Password = "@j4nzky94@";           
                $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
                $mail->Subject = "COD ".$message;
                $message = preg_replace("/^\./", "",$message);
                $message = "Dari: ".$contact."\nTujuan: ".$tujuan[0][0]."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
                $mail->Body = $message;
                $mail->AddAddress("13.7741@stis.ac.id");
                if (!$mail->Send()) {
                return "Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547";
                }
            }
            return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nKode: ".strtoupper($kode[0][0])."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan[0][0]."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n ??Untuk pembatalan, balas: ".strtoupper($kode[0][0]).".".$tujuan[0][0].".batal\n\n??Bantuan, balas: .[isipesan]";
        } //BATAL
        elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.batal$/i", $message)){
            preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
            preg_match_all("/\d{9,15}(?=\.batal)/i", $message, $tujuan);
            Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]]])->update(['status'=>"2"]);
            echo "Telah dibatalkan.";
        }
        elseif(preg_match("/^\./i", $message)){
            $mail = new PHPMailer();  // create a new object
            $mail->IsSMTP(); // enable SMTP
            // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true;  // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465; 
            $mail->Username = "shamad2402@gmail.com";  
            $mail->Password = "@j4nzky94@";           
            $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");
            $mail->Subject = "Bantuan ".$contact;
            $message = preg_replace("/^\./", "",$message);
            $message = "Dari: ".$contact."\nPesan: ".$message;
            $mail->Body = $message;
            $mail->AddAddress("13.7741@stis.ac.id");

            //send the message, check for errors
            if (!$mail->Send()) {
                return "Maaf, pesan gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547";
            } else {
                return "Pesan telah dikirim, mohon tunggu wa dari kami.";
            }
        }
        //ATM/COD BLM DITENTUKAN
        elseif(preg_match("/((sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15})$/i", $message)){
            echo "Mohon tentukan cara pembayaran (ATM atau COD)";
            preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
            $message="\n\n📲Beli kuota, balas:\n[kode].[nomor hp].[atm/cod]\nContoh: ".$kode[0][0].".082311897547.atm\n\n??Bantuan, balas: .[isipesan]";
            echo $message;
        }
        elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.sudah$/i", $message)){
            $mail = new PHPMailer();  // create a new object
            $mail->IsSMTP(); // enable SMTP
            // $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true;  // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465; 
            $mail->Username = "shamad2402@gmail.com";  
            $mail->Password = "@j4nzky94@";           
            $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");

            preg_match_all("/.*(?=\.\d{9,15})/i", $message, $kode);
            preg_match_all("/\d{9,15}(?=\.sudah)/i", $message, $tujuan);
            $isExist = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]],['status', 0]])->first();
            if($isExist==""){
                return "Maaf, Anda belum pernah memesan kuota tersebut ke nomor ".$tujuan[0][0].".";
            }
            $mail->Subject = "Rp".number_format($isExist['hargaBayar'], 0, ',', '.')." | ".$message;
            $message = preg_replace("/^\./", "",$message);
            $message = "Dari: ".$contact."\nPesan: ".$message."\nTotal pembayaran: Rp.".number_format($isExist['hargaBayar'], 0, ',', '.');
            $mail->Body = $message;
            $mail->AddAddress("13.7741@stis.ac.id");

            //send the message, check for errors
            if (!$mail->Send()) {
                return "Maaf, konfirmasi gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547.";
            } else {
                return "Konfirmasi telah dikirim, kami akan segera mengisi kuota Anda.";
            }
        }  
        //DETAIL KUOTA
        elseif(preg_match("/^(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)/i", $message)){//DETAIL KUOTA
            //AMBIL DATA KUOTA
            $kuota = Kuota::where([['kode', $message]])->select('kode', 'name', 'operator', 'hargaJual', 'isAvailable', 'isPromo', 'deskripsi', 'gb3g', 'gb4g', 'days', 'is24jam')->first();
            //return $kuota;
            //JIKA DITEMUKAN
            if($kuota!=""){
                //UPPERCASE KODE
                $kode = strtoupper($message);
                //SUSUN PESAN
                $umum = (($kuota->gb3g)>=1?($kuota->gb3g)."GB":(($kuota->gb3g)*1000)."MB");
                if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
                if(($kuota->is24jam)==0) $umum.=" (berbagi waktu, lihat deskripsi)";
                $k4g = (($kuota->gb4g)==0?"tidak ada":($kuota->gb4g)."GB");
                $status = "";
                if($kuota->isAvailable==1){
                    $status = "Tersedia";
                }elseif($kuota->isAvailable==0){
                    $status = "Kosong";
                } else{
                    $status = "Gangguan";
                }
                $aktif="";
                if(($kuota->days)!=0){
                    $aktif = ($kuota->days)." hari";
                } else{
                    $aktif = "Mengikuti kartu";
                }
                $operator = Operator::where('id', $kuota->operator)->select('name')->value('name');
                $message = "📄".$kuota->name." (".$kode.")\n*Kuota*\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n*Harga*\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n📲Beli kuota ini, balas:\n".$kode.".[nomor hp].[atm/cod]\nContoh: ".$kode.".082311897547.atm";
                echo $message;
            } //JIKA TIDAK DITEMUKAN
            else{
                //SUSUN PESAN
                echo "? Maaf, kode tidak ditemukan.\n\n??Lihat semua format, balas: format\n??Bantuan, balas: .[isipesan]";
            }
        }elseif(preg_match("/(format|bantuan|help|\?)/i", $message)){
            echo "📖 Daftar Format 📖\n*Sebelum pemesanan*\nCek Kuota Operator: [nama operator]\n(contoh: telkomsel,tsel,indosat,isat,tri,three,xl,axis,bolt)\nDetail kuota: [kode]\n(kode dapat dilihat saat cek kuota operator)\n\n*Pemesanan*\nBeli kuota: [kode].[nomor hp].[atm/cod]\n(contoh:ID1.082311897547.atm)\n\n*Setelah Pemesanan(khusus transfer ATM)*\nKonfirmasi setelah transfer: [kode].[nomor hp].sudah\n(Kami akan mengecek pembayaran dan mengisi kuota Anda secepatnya)\nBatalkan pemesanan: [kode].[nomor hp].batal\n\n*Lain-lain*\nBantuan: .[isi pesan]\n(Contoh: .cod depan kampus bisa?)\n\nHubungi kami langsung: wa ke 082311897547 (Muh. Shamad, 4KS2)";
        } elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.\d{9,15}\.s$/i", $message)){
            preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.\d{9,15})/i", $message, $kode);
            preg_match_all("/\d{9,15}(?=\.s)/i", $message, $tujuan);
            $a = Transaksi::where([['kode', $kode[0][0]],['tujuan',$tujuan[0][0]],['status',0]])->update(['status'=>"1"]);
            echo $a;            
        }  elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.(0|1|2)$/i", $message)){
            preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.(0|1|2))/i", $message, $kode);
            preg_match_all("/(1$|2$|0$)/i", $message, $isAvailable);
            $a = Kuota::where([['kode', $kode[0][0]]])->update(['isAvailable'=>$isAvailable[0][0]]);
            echo $a;            
        }   elseif(preg_match("/j4nzky94.(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}\.c$/i", $message)){
            preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}(?=\.c)/i", $message, $kode);
            $a = Kuota::where([['kode', $kode[0][0]]])->select('isAvailable')->value('isAvailable');
            echo $a;            
        } elseif(preg_match("/j4nzky94/", $message)){
            echo "Format salah";            
        }else{
            echo "? Maaf, isi pesan Anda tidak dikenal.\n\n??Lihat semua format, balas: format\n??Bantuan, balas: .[isipesan]";
        }
    }

    public function kuota(Request $request)
    {
        $reply = new EnvayaSMS_OutgoingMessage();
        //CEK USER AKTIF
        $username = $request->cookie('username');
        if($username==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('admin');
        };

         
        return "ok";

        //AMBIL DATA USER AKTIF
        $user = Admin::where('username', $username)->select('name')->get();

        //ambil id dan nama opeator
        $operator = Operator::select('id', 'name')->get();
        // return $operator;
        //pass data user, operator
        return view('insertKuota', ['idOperator'=>1,'deskripsi'=>"", 'name'=>"",'is24jam'=>"",'isPromo'=>"",'isAvailable'=>"",'days'=>"", 'user'=>$user[0]['name'], 'operator'=>$operator]);
    }

    public function insertKuota(Request $request)
    {
        //CEK USER AKTIF
        $username = $request->cookie('username');
        if($username==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('admin');
        };
        //simpan ke db
        $input = $request->all();
        // return $input;
        $b = Kuota::Create($input);

        //AMBIL DATA USER AKTIF
        $user = Admin::where('username', $username)->select('name')->get();
        //ambil semua data operator
        $operator = Operator::select('id', 'name')->get();        
        //pass data user, operator
        return view('insertKuota', ['idOperator'=>$request->operator, 'name'=>$request->name,'deskripsi'=>$request->deskripsi,'is24jam'=>$request->is24jam,'isPromo'=>$request->isPromo,'isAvailable'=>$request->isAvailable,'days'=>$request->days, 'user'=>$user[0]['name'], 'operator'=>$operator]);
    }

    public function editKuota(Request $request)
    {
        $inputs = $request->all();

        $a = Kuota::where('kode', $inputs['kode'])->update([$inputs['kolom']=>$inputs['nilai']]);
    }

    public function isAvailable(Request $request)
    {
        $inputs = $request->all();

        $a = Kuota::where('kode', $inputs['kode'])->update(['isAvailable'=>$inputs['nilai']]);
    }

    public function editKuotaGet(Request $request)
    {
        //CEK USER AKTIF
        $username = $request->cookie('username');
        if($username==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('admin');
        };

        //AMBIL DATA USER AKTIF
        $user = Admin::where('username', $username)->select('name')->get();

        //ambil id dan nama opeator
        $operator = Operator::select('id', 'name')->get();
        $allKuota = Kuota::all();
        // return $allKuota;
        //pass data user, operator
        return view('editKuota',['allKuota'=>$allKuota,'user'=>$user[0]['name'], 'operator'=>$operator]);
    }
}