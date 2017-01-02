<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kuota;
use App\Admin;
use App\Operator;
use App\UserQuery;
use App\Transaksi;
use App\XMPPQuery;
use App\Http\Controllers\Controller;
use App\Functions\Gmail\PHPMailer;
use App\Libraries\EnvayaSMS\EnvayaSMS;
use App\Libraries\EnvayaSMS\EnvayaSMS_OutgoingMessage;
use App\Libraries\EnvayaSMS\EnvayaSMS_Event_Send;
use App\Libraries\XMPPHP\XMPPHP_XMPP;
use DB;

class KuotaController extends Controller
{
    public function index(Request $request)
    {
        return "index";
    }

    public function wa(Request $request)
    {
        $contact = $_POST["contact"];
        $contact = preg_replace("/-/", "",$contact);
        $contact = preg_replace("/62/", "0",$contact);
        $contact = preg_replace("/\s/", "",$contact);
        $contact = preg_replace("/\D/", "",$contact);

        if($contact!="081511375460"){
            $a = 1/0;
        }

        $message = $_POST["message"];
        $message = preg_replace("/^\s*/", "",$message);
        if($message==""){
            $message = "8";
        }
        if(preg_match("/\D/", $message)){
            $a = 1/0;
        }

        $queryExisted = UserQuery::where([['sender', $contact],['saved',0]])->first();

        //1. jika belum pernah wa
        if($queryExisted==""){
            //SIMPAN QUERY
            $userQuery['sender'] = $contact;
            $userQuery['platform'] = 1;
            $userQuery['lastPosition'] = 0;//memu awal
            UserQuery::Create($userQuery);
            return "*Menu:*\n1. Kuota\n2. Keranjang belanja";
        } 
        //jika sudah pernah wa
        else{
            //umum menu awal
            if($message=="0"){
                //Update last position = Menu awal
                UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>0]);
                if(!is_null($queryExisted['activeTransaksiId'])){
                    UserQuery::where([['sender', $contact],['saved',0]])->update(['saved'=>1]);
                    //SIMPAN QUERY 
                    $userQuery['sender'] = $contact;
                    $userQuery['platform'] = 1;
                    $userQuery['lastPosition'] = 0;//memu awal
                    UserQuery::Create($userQuery);
                }
                return "*Menu:*\n1. Kuota\n2. Keranjang belanja";
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
                    UserQuery::where([['sender', $contact],['saved',0]])->update(['menuActive'=>1,'lastPosition'=>1]);
                    return "*Kuota:*\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                } 
                //pulsa
                elseif($message=="2"){
                    $cart = Transaksi::where([['sender', $contact],['status',0]])->get();
                    if($cart=="[]"){
                        return "*Keranjang belanja:*\n\n(kosong)\n".$awal;
                    }
                    $list = "";
                    $pos=1;
                    $tIds="";
                    foreach ($cart as $key => $item) {
                        $list.= ($key+1).". ".$item->tujuan." (Rp".number_format($item->hargaBayar, 0, ',', '.').")\n";
                        $tIds.="#".$pos.".".$item->id;
                        $pos++;
                    }
                    UserQuery::where([['sender', $contact],['saved',0]])->update(['menuActive'=>2,'lastPosition'=>1, 'maxCart'=>sizeof($cart), 'transaksiIds'=>$tIds]);
                    return "*Keranjang belanja:*\ntujuan (harga)\n".$list."\n98. Batalkan semua".$awal;
                }
                //masa aktif
                //Riwayat pesanan
            }
            elseif($queryExisted['menuActive']==1){
                //dari menu kuota/pulsa/daftar_pesanan
                if($queryExisted['lastPosition']==1){
                    ///////////////KUOTA
                    //salah satu operator
                    if(preg_match("/[1-6]/", $message)){
                        //Update last position = daftar kuota operator
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>2]); 
                        //ambil data operator
                        $operator = Operator::where('id', $message)->select('id', 'name', 'cekNomor', 'cekKuota')->first();
                        //inisial content
                        $content = "💵 *Kuota ".$operator['name']."* 💵\nharga (total kuota)\n";
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
                            $content.="\n🎁Promo🎁\n";
                            foreach ($promo as $key => $pro) {
                                $umum = (($pro->gb3g<1&&$pro->gb4g==0)?(($pro->gb3g)*1000)."MB":(($pro->gb3g+$pro->gb4g)."GB"));
                                $content.=$pos.". Rp".number_format($pro->hargaJual, 0, ',', '.')." (".$umum.")\n";
                                $codes.="#".$pos.".".$pro->kode;
                                $pos++;
                            }
                        }
                        //Set Max pilihan kuota
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['maxOption'=>($pos-1)]); 
                        //simpan codes
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['codes'=>$codes]);
                        //simpan last operator
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastOperator'=>$operator['id']]);
                        return $content.$kembali.$awal;
                    } 
                    //menu sebelumnya
                    elseif ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>1]); 
                        return "*Kuota:*\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                    }
                }
                //dari daftar kuota/ ...
                elseif($queryExisted['lastPosition']==2){
                    //////////////KUOTA
                    //pilih kuota
                    $max = UserQuery::where([['sender', $contact],['saved',0]])->select('maxOption')->value('maxOption');
                    if((int)$message!=0){ 
                        if($message<=$max && $message>0){
                            $codes = UserQuery::where([['sender', $contact],['saved',0]])->select('codes')->value('codes');
                            preg_match_all("/(?<=#".$message."\.)\w{3,7}/i", $codes, $kode);
                            //simpan code selected
                            UserQuery::where([['sender', $contact],['saved',0]])->update(['codeSelected'=>$kode[0][0]]);
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
                            UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>3]); 
                            $content = "📄".$kuota->name."\n*Kuota*\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n*Harga*\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n1. Beli\n".$kembali.$awal;
                            return $content;
                        }                        
                    }
                    //menu sebelumnya
                    if ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>1]); 
                        return "*Kuota:*\n1. Telkomsel\n2. Indosat\n3. XL\n4. Tri\n5. Axis\n6. Bolt\n".$awal;
                    }
                }
                elseif($queryExisted['lastPosition']==3){
                    /////////////KUOTA
                    //kembali
                    if ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>2]); 
                        //last operator
                        $lastOperatorId = UserQuery::where([['sender', $contact],['saved',0]])->select('lastOperator')->value('lastOperator');
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
                            $content.="\n🎁Promo🎁\n";
                            foreach ($promo as $key => $pro) {
                                $umum = (($pro->gb3g<1&&$pro->gb4g==0)?(($pro->gb3g)*1000)."MB":(($pro->gb3g+$pro->gb4g)."GB"));
                                $content.=$pos.". Rp".number_format($pro->hargaJual, 0, ',', '.')." (".$umum.")\n";
                                $codes.="#".$pos.".".$pro->kode;
                                $pos++;
                            }
                        }
                        //Set Max pilihan kuota
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['maxOption'=>($pos-1)]); 
                        //simpan codes
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['codes'=>$codes]);
                        //simpan last operator
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastOperator'=>$operator['id']]);
                        return $content.$kembali.$awal;
                    }
                    //beli
                    elseif($message==1){
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>4]); 
                        //last operator
                        $lastOperatorId = UserQuery::where([['sender', $contact],['saved',0]])->select('lastOperator')->value('lastOperator');
                        //ambil data operator
                        $operator = Operator::where('id', $lastOperatorId)->select('name', 'cekNomor')->first();
                        return "*Masukkan nomor hp tujuan*\ncontoh: 082311897547\n\n(cek nomor ".$operator['name'].": ".$operator['cekNomor'].")\n".$kembali.$awal;
                    }
                }
                elseif($queryExisted['lastPosition']==4){
                    ////////////////KUOTA
                    //kembali
                    if ($message==99) {
                        //last code selected
                        $lastCodeSelected = UserQuery::where([['sender', $contact],['saved',0]])->select('codeSelected')->value('codeSelected');
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
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>3]); 
                        $content = "📄".$kuota->name."\n*Kuota*\nUmum: ".$umum."\nKhusus 4G: ".$k4g."\n\n*Harga*\nRp".number_format($kuota->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$operator."\nMasa aktif: ".$aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n1. Beli\n".$kembali.$awal;
                        return $content;
                    }
                    //isi nomor
                    elseif(preg_match("/^0\d{8,15}/i", $message)){
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>5]); 
                        //Simpan nomor
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['tujuan'=>$message]); 
                        return "*Metode pembayaran:*\n1. Transfer ATM/Bank\n2. COD (bayar langsung)\n".$kembali.$awal;
                    }
                }
                elseif($queryExisted['lastPosition']==5){
                    //////////////////////KUOTA
                    //kembali
                    if ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>4]); 
                        //last operator
                        $lastOperatorId = UserQuery::where([['sender', $contact],['saved',0]])->select('lastOperator')->value('lastOperator');
                        //ambil data operator
                        $operator = Operator::where('id', $lastOperatorId)->select('name', 'cekNomor')->first();
                        return "*Masukkan nomor hp tujuan*\ncontoh: 082311897547\n\n(cek nomor ".$operator['name'].": ".$operator['cekNomor'].")\n".$kembali.$awal;
                    }
                    //cod/atm
                    elseif(preg_match("/[12]/", $message)){
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>6]);
                        return $this->afterATMCOD($message, $contact, 0);                    
                    }
                }
                //Last posisi = info pesanan
                elseif($queryExisted['lastPosition']==6){
                    ////////////////////KUOTA
                    //kembali
                    if ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>5]);
                        return "*Metode pembayaran:*\n1. Transfer ATM/Bank\n2. COD (bayar langsung)\n".$kembali.$awal;
                    }                
                    //konfirmasi
                    elseif ($message==1) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>7]);

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
                        $mail->Subject = "Konfirmasi ".$contact;
                        $message = preg_replace("/^\./", "",$message);
                        $transaksi = Transaksi::where([['id', $queryExisted['activeTransaksiId']]])->select('hargaBayar', 'tujuan', 'kode')->first();
                        $message = "#ID Pesanan: ".$queryExisted['activeTransaksiId']."\nTotal transfer: Rp".number_format($transaksi['hargaBayar'], 0, ',', '.')."\nKode: ".$transaksi['kode']."\nNomor tujuan: ".$transaksi['tujuan'];
                        $mail->Body = $message;
                        $mail->AddAddress("13.7741@stis.ac.id");
                        //jika gagal kirim email
                        if (!$mail->Send()) {
                            return "Maaf, konfirmasi gagal dikirim. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547\n".$kembali.$awal;
                        }
                        Transaksi::where([['id', $queryExisted['activeTransaksiId']]])->update(['confirmed'=>1]);
                        return "✅ Konfirmasi telah dikirim. Kami akan segera mengecek transfer Anda. Mohon tunggu beberapa saat.\n".$kembali.$awal;
                    }
                    //edit
                    elseif ($message==2) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>7]);
                        return "*Ubah:*\n1. Nomor hp tujuan\n2. Metode pembayaran\n3. Kuota\n".$kembali.$awal;
                    }
                    //batal
                }
                //Last posisi = Konfirmasi/Edit/Batal
                elseif($queryExisted['lastPosition']==7){
                    ////////////////////KUOTA
                    //kembali
                    if ($message==99) {
                        //Update last position
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['lastPosition'=>6]);
                        $message = Transaksi::where([['id', $queryExisted['activeTransaksiId']]])->select('pmethod')->value('pmethod');
                        return $this->afterATMCOD($message, $contact, 1);                    
                    }
                }
            } 
            elseif($queryExisted['menuActive']==2){
                if($queryExisted['lastPosition']==1){
                    if($message=="98"){
                        Transaksi::where([['sender',$contact], ['status', 0]])->update(['status'=>2]);  
                        return "Semua telah dibatalkan.\n\n*Keranjang belanja:*\n\n(kosong)\n".$awal;
                    }
                    elseif((int)$message!=0){ 
                        if($message<=$queryExisted['maxCart'] && $message>0){ 
                            UserQuery::where([['sender', $contact],['saved',0]])->delete();
                            preg_match_all("/(?<=#".$message."\.)\d+/i", $queryExisted['transaksiIds'], $tId);
                            UserQuery::where([['activeTransaksiId', $tId[0][0]]])->update(['saved'=>0, 'menuActive'=>2,'lastPosition'=>2, 'maxCart'=>$queryExisted['maxCart']]);

                            $activeTransaksi = Transaksi::where([['id',$tId[0][0]]])->first();

                            $kuo = Kuota::where('kode', $activeTransaksi['kode'])->select('name', 'hargaJual', 'isAvailable','gb3g', 'gb4g', 'days')->first();
                            $umum = (($kuo->gb3g)>=1?($kuo->gb3g)."GB":(($kuo->gb3g)*1000)."MB");
                            if(preg_match("/^sd/i", $message)) $umum.=" (wilayah Jakarta)";
                            $k4g = (($kuo->gb4g)==0?"tidak ada":($kuo->gb4g)."GB");
                            $aktif="";
                            if(($kuo->days)!=0){
                                $aktif = ($kuo->days)." hari";
                            } else{
                                $aktif = "Mengikuti kartu";
                            }
                            if($activeTransaksi['pmethod']==1){
                                //pembayaran atm
                                $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\n*Batas transfer: ".$activeTransaksi['batasPembayaran']."*\n*Setelah transfer, mohon pilih 1 untuk konfirmasi.*\n\n1. Konfirmasi\n2. Ubah\n3. Batal\n4. Tambah Pesanan";
                            } 
                            //output jika cod
                            else{
                                //pembayaran cod
                                $pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.\n\n1. Batal\n2. Ubah";
                            }
                            return "1⃣Informasi Pemesanan\nID pesanan: ".$tId[0][0]."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$activeTransaksi['tujuan']."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($activeTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
                        }
                    }
                }
                elseif($queryExisted['lastPosition']==2){
                    if($message=="99"){
                        $cart = Transaksi::where([['sender', $contact],['status',0]])->get();
                        if($cart=="[]"){
                            return "*Keranjang belanja:*\n\n(kosong)\n".$awal;
                        }
                        $list = "";
                        $pos=1;
                        $tIds="";
                        foreach ($cart as $key => $item) {
                            $list.= ($key+1).". ".$item->tujuan." (Rp".number_format($item->hargaBayar, 0, ',', '.').")\n";
                            $tIds.="#".$pos.".".$item->id;
                            $pos++;
                        }
                        UserQuery::where([['sender', $contact],['saved',0]])->update(['menuActive'=>2,'lastPosition'=>1, 'maxCart'=>sizeof($cart), 'transaksiIds'=>$tIds]);
                        return "*Keranjang belanja:*\ntujuan (harga)\n".$list."\n98. Batalkan semua".$awal;
                    }
                }
            }
        }

        return $message;
    }

    function afterATMCOD($message, $contact, $noEmail){
        //template menu awal
        $awal = "\n0. Menu awal";
        //template menu sebelumnya
        $kembali = "\n99. Menu sebelumnya";
        //Last posisi = menu awal
        //Ambil kode & tujuan sebelumnya
        $kode = UserQuery::where([['sender', $contact],['saved',0]])->select('codeSelected')->value('codeSelected');
        $tujuan = UserQuery::where([['sender', $contact],['saved',0]])->select('tujuan')->value('tujuan');

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
        //angka unik
        $sand = 0;
        if($pm==1){
            $sand = rand(1,99);
        }

        //cek jika sdh ada
        $isExist = Transaksi::where([['kode', $kode],['tujuan',$tujuan], ['status', 0]])->select('id','hargaBayar','pmethod', 'confirmed')->first();
        //jika ada
        if($isExist!=""){           
            //jika ganti metode 
            if($pm!=$isExist['pmethod']){
                Transaksi::where([['kode', $kode],['tujuan',$tujuan], ['status', 0]])->update(['pmethod'=>$pm]);  
                if($pm == 1){
                    $isExist['hargaBayar'] -= $sand;
                    Transaksi::where([['kode', $kode],['tujuan',$tujuan], ['status', 0]])->update(['hargaBayar'=>$isExist['hargaBayar']]);
                }                
            }    
            //output jika atm
            if($pm==1){
                $confirm = "Konfirmasi";
                if($isExist['confirmed']==1) $confirm = "Konfirmasi ulang";
                //pembayaran atm
                $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\n*Batas transfer: ".$batasPembayaran."*\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n1. ".$confirm."\n2. Ubah\n3. Batal\n4. Tambah Pesanan";
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

                $message = "Dari: ".$contact."\nTujuan: ".$tujuan."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nID pesanan: ".$isExist['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($kuo['hargaJual'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;

                $mail->Body = $message;
                $mail->AddAddress("13.7741@stis.ac.id");

                //jika gagal kirim email
                if(!$noEmail){
                    if (!$mail->Send()) {
                        return "Maaf, pesanan gagal dibuat. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547\n".$kembali.$awal;
                    }
                }
                //reset total pembayaran ke bulat jika convert ke cod & sebaliknya
                $isExist['hargaBayar'] = $kuo['hargaJual'];
            }                  
            //return versi sdh pernah
            return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nID pesanan: ".$isExist['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($isExist['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
        }

        //persiapan transaksi baru
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
        UserQuery::where([['sender', $contact],['saved',0]])->update(['activeTransaksiId'=>$transaksi['id']]); 

        //jika atm
        if($pm==1){
            $pembayaran="Mohon transfer sesuai total yang tertera (termasuk tiga angka terakhir) ke rekening berikut:\n*Norek: 1257-01-004085-50-9*\n*a.n.: MUH. SHAMAD*\n*Batas transfer: ".$batasPembayaran."*\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n1. Konfirmasi\n2. Ubah\n3. Batal";
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

            $message = "Dari: ".$contact."\nTujuan: ".$tujuan."\nResponse: ✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nID pesanan: ".$transaksi['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
            $mail->Body = $message;
            $mail->AddAddress("13.7741@stis.ac.id");
            //jika gagal kirim email
            if(!$noEmail){
                if (!$mail->Send()) {
                    return "Maaf, pesanan gagal dibuat. Sistem dalam gangguan. Mohon hubungi wa kami langsung: 082311897547\n".$kembali.$awal;
                }
            }
        } 
        return "✅ Pemesanan berhasil\n\n1⃣Informasi Pemesanan\nID pesanan: ".$transaksi['id']."\nNama paket: ".$kuo['name']."\nKuota umum: ".$umum."\nKhusus 4G: ".$k4g."\nMasa aktif: ".$aktif."\n*Nomor hp tujuan: ".$tujuan."*\n\n2⃣Informasi Pembayaran\n*Total pembayaran: Rp".number_format($userTransaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n".$kembali.$awal;
    }
}