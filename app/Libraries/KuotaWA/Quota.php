<?php

namespace App\Libraries\KuotaWA;

#DB
use App\Kuota;
use App\UserQuery;
use App\Transaksi;
use App\Operator;

#Library
use App\Libraries\KuotaWA\Email;

class Quota extends MenuAbstract{
	public $isPromo, $kode, $operatorName, $hargaJual, $gb3g, $gb4g, $days, $cekNomor, $paketName, $umum, $k4g;

	public function __construct($position, $operatorName, $kode, $paketName, $isPromo, $hargaJual, $gb3g, $gb4g, $days, $cekNomor){

		$this->position = $position;
		$this->operatorName = $operatorName;
		$this->kode = $kode;
		$this->paketName = $paketName;
		$this->isPromo = $isPromo;
		$this->hargaJual = $hargaJual;
		$this->gb3g = $gb3g;
		$this->gb4g = $gb4g;
		$this->days = $days;
		$this->cekNomor = $cekNomor;

        $this->umum = (($this->gb3g)>=1?($this->gb3g)."GB":(($this->gb3g)*1000)."MB");

        if(preg_match("/^SD/", $kode)) $this->umum.=" (wilayah Jakarta)";

        $this->k4g = (($this->gb4g)==0?"tidak ada":($this->gb4g)."GB");

        if(($days)!=0){

            $this->aktif = ($days)." hari";

        } else{

            $this->aktif = "Mengikuti kartu";

        }

		$totalKuota = (($gb3g<1&&$gb4g==0)?(($gb3g)*1000)."MB":(($gb3g+$gb4g)."GB"));
		$this->name = "Rp".number_format($hargaJual, 0, ',', '.')." (".$totalKuota.")";

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select, $wa){

		$optionAvailable = array(1=>"1");

		if(!isset($optionAvailable[$select[4]])){

			return $this->wrongCommand($select, $wa);

		}

		$isAvailable = Kuota::where([['kode', $this->kode]])->value('isAvailable');

		if($isAvailable==0&&count($select)<8){

			array_splice($select, 4);

			UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

			return "Maaf, paket ini baru saja tidak tersedia. Jika Anda berhasil memesan paket ini sebelumnya, pesanan dapat dilihat di menu Keranjang belanja dan tetap akan diproses saat tersedia. Terima kasih.\n\n(hal ini terjadi biasanya karena operator sedang terganggu atau terjadi pada paket promo. Untuk bantuan, wa/sms: 082311897547)\n\n99. Menu kuota ".$this->operatorName.$this->awal;

		}


		if(count($select)==5) {

			return $this->beli();

		} elseif (count($select)==6) {

			if(!$this->numberValidation($this->operatorName,$select[5])){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Nomor yang Anda masukkan bukan nomor ".$this->operatorName."\n\n*Mohon masukkan nomor Anda lagi:*\n".$this->kembali.$this->awal;

			} elseif(strlen($select[5])<10|strlen($select[5])>13){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan 10 - 13 digit.\n\n*Mohon masukkan nomor Anda lagi:*\n".$this->kembali.$this->awal;

			}

			return "*Pilih cara pembayaran:*\n1. Transfer ATM/Bank\n2. COD (bayar langsung)\n\n99. Ubah nomor hp tujuan".$this->awal;

		} elseif(count($select)==7 && preg_match("/[12]/", $select[6])){

			$sand = 0;

	        if($select[6]==1){

	            $sand = rand(1,99);

	        }

			$batasPembayaran = date("H:i", strtotime('+5 hours'))." WIB, tanggal ".date("d-m-Y", strtotime('+5 hours'));

	        $activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

	        $transaksi = "";

			$confirm = "*1. Konfirmasi*";

	        if($activeTransaksiId != ""){

	        	$transaksi = Transaksi::where([['id', $activeTransaksiId]])->first();

                if($transaksi['confirmed']==1) $confirm = "1. Konfirmasi ulang";

	        } 

			if($select[6]==1){

	            $pembayaran="Mohon transfer sesuai total harga yang tertera (termasuk tiga angka terakhir) ke rek. BRI *1257-01-004085-50-9* a.n. Muh. Shamad sebelum jam ".$batasPembayaran.".\n\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n".$confirm."\n2. Batal";
	        } else{

	        	$pembayaran="Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.\n\n1. Batal";

	        }

	        //persiapan transaksi baru
	        $userTransaksi['hargaBayar'] = $this->hargaJual-$sand;
	        $userTransaksi['harga'] = $this->hargaJual;
	        $userTransaksi['batasPembayaran'] = $batasPembayaran;
	        $userTransaksi['pmethod'] = $select[6];
	        $userTransaksi['kode'] = $this->kode; 
	        $userTransaksi['tujuan'] = $select[5];
	        $userTransaksi['sender'] = $wa->getFrom();
	        $userTransaksi['platform'] = $wa->getPlatform();

	        if($activeTransaksiId == ""){

	        	if($select[6] == 1){

	        		while (true) {

			            $searchUniqueSand = Transaksi::where([['hargaBayar', $userTransaksi['hargaBayar']], ['status', 0]])->first();

			            if($searchUniqueSand==""){

			                break;

			            }

			            $userTransaksi['hargaBayar'] += $sand;

			            $sand = rand(1,99);

			            $userTransaksi['hargaBayar'] -= $sand;

			        }

	        	}

	        	$transaksi = Transaksi::Create($userTransaksi);

				UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['activeTransaksiId'=>$transaksi['id']]);

	        } else {

		        	$status = Transaksi::where([['id', $activeTransaksiId]])->select('status')->value('status');

		        	if($status == 1) {

					array_splice($select, 4);

					UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

	        		$cekKuota = Operator::where('name', $this->operatorName)->value('cekKuota');

	        		return "Konfirmasi telah diterima dan kuota sudah dikirim. Terima kasih.\nCek kuota ".$this->operatorName." : ".$cekKuota."\n\n99. Menu kuota ".$this->operatorName.$this->awal;

	        	}

	        	if($transaksi['pmethod']!=$select[6]){

	        		if($select[6]==2){

	        			Transaksi::where([['id', $activeTransaksiId]])->update(['pmethod'=>$select[6]]);

	        			$transaksi['hargaBayar'] = $this->hargaJual;

	        		} else {

	        			$hargaActiveTransaksi = Transaksi::where([['id', $activeTransaksiId]])->select('hargaBayar', 'harga')->first();

	        			if($hargaActiveTransaksi['hargaBayar'] == $hargaActiveTransaksi['harga']){

	        				while (true) {

					            $searchUniqueSand = Transaksi::where([['hargaBayar', $userTransaksi['hargaBayar']], ['status', 0]])->first();

					            if($searchUniqueSand==""){

					                break;

					            }

					            $userTransaksi['hargaBayar'] += $sand;

					            $sand = rand(1,99);

					            $userTransaksi['hargaBayar'] -= $sand;

					        }

	        				Transaksi::where([['id', $activeTransaksiId]])->update(['pmethod'=>$select[6], 'hargaBayar'=>$userTransaksi['hargaBayar']]);

	        				$transaksi['hargaBayar'] = $userTransaksi['hargaBayar'];

	        			} else {

	        				Transaksi::where([['id', $activeTransaksiId]])->update(['pmethod'=>$select[6]]);

	        				$transaksi['hargaBayar'] = $hargaActiveTransaksi['hargaBayar'];

	        			}

	        		}

	        	}

	        }

	        if($select[6]==2){

	        	$mail = new Email("COD ".$wa->getFrom(),"ID Pesanan : ".$transaksi['id']."\nKode : ".$this->kode."\nNama paket : ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\nNomor tujuan : ".$select[5]."\nHarga bayar : ".number_format(($this->hargaJual), 0, ',', '.'));

                if (!$mail->send()) {

                    return "Pesan COD ke Muh. Shamad gagal, sistem dalam gangguan. Mohon hubungi kami via wa/sms : 082311897547. Terima kasih.\n\n99. Ubah cara pembayaran".$this->awal;

                }

	        }

	        return "✅ Pemesanan berhasil\n\n📝 *Info Pesanan*\nID pesanan: ".$transaksi['id']."\nNama paket: ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\n\nNomor tujuan: *".$userTransaksi['tujuan']."*\nTotal harga: *Rp".number_format($transaksi['hargaBayar'], 0, ',', '.')."*\n\n".$pembayaran."\n\n99. Ubah cara bayar".$this->awal;

			//return "✅ Pemesanan berhasil\n\n1⃣ Informasi Pemesanan\nID pesanan: ".$transaksi['id']."\nNama paket: ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\n*Nomor hp tujuan: ".$userTransaksi['tujuan']."*\n\n2⃣ Informasi Pembayaran\n*Total pembayaran: Rp".number_format($transaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n99. Ubah cara pembayaran".$this->awal;

		} elseif(count($select)==8 && preg_match("/[123]/", $select[7])){

			if($select[6]==2){ //COD

				if(preg_match("/[1]/", $select[7])){

					if($select[7]==1){

						$mail = new Email("COD ".$wa->getFrom(),"Batal gan...\n\nKode : ".$this->kode."\nNama paket : ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\nNomor tujuan : ".$select[5]);

		                $mail->send();

						$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

						Transaksi::where([['id', $activeTransaksiId]])->update(['status'=>2]);

						array_splice($select, 4);

						UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

						return "Pesanan telah dibatalkan.\n"."\n99. Menu kuota ".$this->operatorName.$this->awal;

					} //else {

						// return "*Ubah:*\n1. Kuota\n2. Nomor hp tujuan\n3. Cara pembayaran\n".$this->kembali.$this->awal;

					//}

				} else {

					$this->wrongCommand($select, $wa);

				}

			} else{ //Transfer

				if(preg_match("/[12]/", $select[7])){

					if($select[7]==1){

						$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

						$hargaBayar = Transaksi::where([['id', $activeTransaksiId]])->select('hargaBayar')->value('hargaBayar');

						$mail = new Email("Konfirmasi ".$wa->getFrom(),"Harga bayar : ".number_format($hargaBayar, 0, ',', '.')."\nID Pesanan : ".$activeTransaksiId."\nKode : ".$this->kode."\nNama paket : ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\nNomor tujuan : ".$select[5]);

		                if(!$mail->send()){

		                	return "Konfirmasi gagal dikirim, sistem dalam gangguan. Mohon hubungi kami via wa/sms : 082311897547. Terima kasih.";

		                }

		                $activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

						Transaksi::where([['id', $activeTransaksiId]])->update(['confirmed'=>1]);

						return "Konfirmasi berhasil dikirim. Kami akan mengecek pembayaran Anda secepatnya. Mohon tunggu maksimal 1 x 24 jam. Terima kasih.\n".$this->kembali.$this->awal;

					} //elseif($select[7]==2){

						// return "You Edit the transaction";

					// } 
					else{

						$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

						Transaksi::where([['id', $activeTransaksiId]])->update(['status'=>2]);

						array_splice($select, 4);

						UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

						return "Pesanan telah dibatalkan.\n"."\n99. Menu kuota ".$this->operatorName.$this->awal;

					}

				} else {

					$this->wrongCommand($select, $wa);

				}

			}

		} else{

			return $this->wrongCommand($select, $wa);

		}
		
	}

	public function beli(){

		return "*Masukkan nomor tujuan:*\n(contoh: 082311234567)\n\n(cek nomor ".$this->operatorName.": ".$this->cekNomor.")\n".$this->kembali.$this->awal;
		
	}

	public function numberValidation($operator, $number) {

		if(preg_match("/telkomsel|tsel/i", $operator)) {

			if(preg_match("/^(0811|0812|0813|0821|0822|0823|0852|0853|0851)/i", $number)){

				return true;

			}

		} elseif(preg_match("/indosat|isat/i", $operator)) {

			if(preg_match("/^(0856|0857|0814|0815|0816|0855|0858)/i", $number)){

				return true;

			}

		} elseif(preg_match("/xl/i", $operator)) {

			if(preg_match("/^(0817|0818|0819|0859|0877|0878)/i", $number)){

				return true;

			}

		} elseif(preg_match("/tri|three/i", $operator)) {

			if(preg_match("/^(0895|0896|0897|0898|0899)/i", $number)){

				return true;

			}

		} elseif(preg_match("/axis/i", $operator)) {

			if(preg_match("/^(0831|0832|0838)/i", $number)){

				return true;

			}

		} elseif(preg_match("/bolt/i", $operator)) {

			if(preg_match("/^(0998|0999)/i", $number)){

				return true;

			}

		} 

		return false;

	} //sumber : http://www.kios-pulsa.com/article/daftar-prefix-nomor-operator-seluler-indonesia/

	public function showMenu(){

		$kuota = Kuota::where([['kode', $this->kode]])->select('name', 'operator', 'isAvailable', 'isPromo', 'deskripsi', 'days', 'is24jam', 'expired')->first();

        if(($kuota->is24jam)==0) $this->umum.=" (berbagi waktu, lihat deskripsi)";

        $status = "";

        $beli = "1. Beli\n";

        if($kuota->isAvailable==1){

        	if(is_null($kuota->expired)){

            	$status = "Tersedia";

        	} else {

        		$status = "Tersedia sampai jam ".$kuota->expired." (WIB)";

        	}

        }elseif($kuota->isAvailable==0){

            $status = "Kosong";

            $beli = "";

        } else{

            $status = "Gangguan";

            $beli = "";

        }

        return "📋 Paket ".$kuota->name."\n*Kuota*\nUmum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\n\n*Harga*\nRp".number_format($this->hargaJual, 0, ',', '.')."\n\n*Info tambahan*\nStatus: ".$status."\nOperator: ".$this->operatorName."\nMasa aktif: ".$this->aktif."\nDeskripsi:\n".$kuota->deskripsi."\n\n".$beli.$this->kembali.$this->awal;

	}

	public function showInKeranjang($transaksiId){

        $transaksi = Transaksi::where([['id', $transaksiId]])->first();

        $pembayaran = "";

		if($transaksi['pmethod']==1){

			$menu = "";

			if($transaksi['status'] == 1) {

				// $menu = "\n\n1. Beli lagi";

			} elseif ($transaksi['status'] == 2) {

	        	// $menu = "\n\n1. Ulangi pembelian";

			}  elseif ($transaksi['confirmed']==0) {

	        	$menu = "\n\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n*1. Konfirmasi*\n2. Batal";

			} else {

				$menu = "\n\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n1. Konfirmasi ulang\n2. Batal";

			}

            $pembayaran="Mohon transfer sesuai total harga yang tertera (termasuk tiga angka terakhir) ke rek. BRI *1257-01-004085-50-9* a.n. Muh. Shamad sebelum jam ".$transaksi['batasPembayaran'].".".$menu;
        } else{

			$menu = "";

			if($transaksi['status'] == 0) {

				$menu = "Mohon tunggu wa dari kami (Muh. Shamad, 4KS2) untuk COD. Terima kasih.\n\n1. Batal";

			} elseif ($transaksi['status'] == 1) {

	        	// $menu = "\n1. Beli lagi";

			} else {

				// $menu = "\n1. Ulangi pembelian";

			}

        	$pembayaran.=$menu;

        }

		return "📝 *Info Pesanan*\nID pesanan: ".$transaksiId."\nNama paket: ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\n\nNomor tujuan: *".$transaksi['tujuan']."*\nTotal harga: *Rp".number_format($transaksi['hargaBayar'], 0, ',', '.')."*\n\n".$pembayaran."\n\n98. Hapus dari keranjang".$this->kembali.$this->awal;


		// return "1⃣  Informasi Pemesanan\nID pesanan: ".$transaksiId."\nNama paket: ".$this->paketName."\nKuota umum: ".$this->umum."\nKhusus 4G: ".$this->k4g."\nMasa aktif: ".$this->aktif."\n*Nomor hp tujuan: ".$transaksi['tujuan']."*\n\n2⃣  Informasi Pembayaran\n*Total pembayaran: Rp".number_format($transaksi['hargaBayar'], 0, ',', '.')."*\n".$pembayaran."\n\n98. Hapus dari keranjang".$this->kembali.$this->awal;

	}

}
