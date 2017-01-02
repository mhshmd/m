<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;
use App\Kuota;
use App\Operator;
use App\Transaksi;

class ItemKeranjang extends MenuAbstract{

	public $id, $pmethod, $tujuan, $hargaBayar, $status, $kode;

	public $subMenuPromo = array();

	public function __construct($position, $id, $pmethod, $kode, $tujuan, $hargaBayar, $status){

		$this->position = $position;
		
		$this->id = $id;
		
		$this->pmethod = $pmethod;
		
		$this->kode = $kode;
		
		$this->name = $tujuan." (Rp".number_format($hargaBayar, 0, ',', '.').")";
		
		$this->tujuan = $tujuan;
		
		$this->hargaBayar = $hargaBayar;
		
		$this->status = $status;

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		if ($select[3]=="98") {

			Transaksi::where([['sender', $wa->getFrom()], ['showMe', 1], ['id', $this->id]])->update(['showMe'=>0]);

			array_splice($select, 3);

			UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

			return "Berhasil dihapus\n\n99. Keranjang belanja".$this->awal;

		} elseif($this->pmethod == 1) {

			if(preg_match("/[12]/", $this->status)) {

				if(preg_match("/[1]/", $select[3])) {

					// $activeQuota = Kuota::where('kode', $this->kode)->first();

					// $operatorName = Operator::where('id', $activeQuota['operator'])->select('name', 'cekNomor')->first();

					// $kuota = new Quota(1, $operatorName['name'], $this->kode, $activeQuota->name, $activeQuota->isPromo, $activeQuota->hargaJual, $activeQuota->gb3g, $activeQuota->gb4g, $activeQuota->days, $operatorName['cekNomor']);

					// return $kuota->beli();

					return $this->wrongCommand($select, $wa);

				} else {

					return $this->wrongCommand($select, $wa);

				}

			} else {

				if(preg_match("/[12]/", $select[3])) {

					if($select[3] == 2) {

						// $activeQuota = Kuota::where('kode', $this->kode)->first();

						// $operatorName = Operator::where('id', $activeQuota['operator'])->select('name', 'cekNomor')->first();

						// $kuota = new Quota(1, $operatorName['name'], $this->kode, $activeQuota->name, $activeQuota->isPromo, $activeQuota->hargaJual, $activeQuota->gb3g, $activeQuota->gb4g, $activeQuota->days, $operatorName['cekNomor']);

						// $mail = new Email("COD ".$wa->getFrom(),"Batal gan...\n\nKode : ".$kuota->kode."\nNama paket : ".$kuota->paketName."\nKuota umum: ".$kuota->umum."\nKhusus 4G: ".$kuota->k4g."\nMasa aktif: ".$kuota->aktif."\nNomor tujuan : ".$this->tujuan);

		    //             $mail->send();

						Transaksi::where([['id', $this->id]])->update(['status'=>2]);

						array_splice($select, 3);

						UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

						return "Pesanan telah dibatalkan.\n"."\n99. Keranjang belanja ".$this->awal;

					} else {

						$activeQuota = Kuota::where('kode', $this->kode)->first();

						$operatorName = Operator::where('id', $activeQuota['operator'])->select('name', 'cekNomor')->first();

						$kuota = new Quota(1, $operatorName['name'], $this->kode, $activeQuota->name, $activeQuota->isPromo, $activeQuota->hargaJual, $activeQuota->gb3g, $activeQuota->gb4g, $activeQuota->days, $operatorName['cekNomor']);

						$mail = new Email("Konfirmasi ".$wa->getFrom(),"Harga bayar : ".number_format($this->hargaBayar, 0, ',', '.')."\nID pesanan : ".$this->id."\nKode : ".$this->kode."\nNama paket : ".$activeQuota->paketName."\nKuota umum: ".$kuota->umum."\nKhusus 4G: ".$kuota->k4g."\nMasa aktif: ".$kuota->aktif."\nNomor tujuan : ".$this->tujuan);

		                if(!$mail->send()){

		                	return "Konfirmasi gagal dikirim, sistem dalam gangguan. Mohon hubungi kami via wa/sms : 082311897547. Terima kasih.";

		                }

						Transaksi::where([['id', $this->id]])->update(['confirmed'=>1]);

						return "Konfirmasi berhasil dikirim. Kami akan mengecek pembayaran Anda secepatnya. Mohon tunggu maksimal 1 x 24 jam. Terima kasih.\n".$this->kembali.$this->awal;

					}

				} else {

					return $this->wrongCommand($select, $wa);

				}

			}

		} else {

			if(preg_match("/[1]/", $select[3])) {

				$activeQuota = Kuota::where('kode', $this->kode)->first();

				$operatorName = Operator::where('id', $activeQuota['operator'])->select('name', 'cekNomor')->first();

				$kuota = new Quota(1, $operatorName['name'], $this->kode, $activeQuota->name, $activeQuota->isPromo, $activeQuota->hargaJual, $activeQuota->gb3g, $activeQuota->gb4g, $activeQuota->days, $operatorName['cekNomor']);

				$mail = new Email("COD ".$wa->getFrom(),"Batal gan...\n\nKode : ".$kuota->kode."\nNama paket : ".$kuota->paketName."\nKuota umum: ".$kuota->umum."\nKhusus 4G: ".$kuota->k4g."\nMasa aktif: ".$kuota->aktif."\nNomor tujuan : ".$this->tujuan);

                $mail->send();

				Transaksi::where([['id', $this->id]])->update(['status'=>2]);

				array_splice($select, 3);

				UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

				return "Pesanan telah dibatalkan.\n"."\n99. Keranjang belanja ".$this->awal;

			} else {

				return $this->wrongCommand($select, $wa);

			}

		}

		return "Your selection is what?";

	}

	public function showMenu(){

		$activeQuota = Kuota::where('kode', $this->kode)->first();

		$operatorName = Operator::where('id', $activeQuota['operator'])->select('name', 'cekNomor')->first();

		$kuota = new Quota(1, $operatorName['name'], $this->kode, $activeQuota->name, $activeQuota->isPromo, $activeQuota->hargaJual, $activeQuota->gb3g, $activeQuota->gb4g, $activeQuota->days, $operatorName['cekNomor']);

		return $kuota->showInKeranjang($this->id);

	}

}
