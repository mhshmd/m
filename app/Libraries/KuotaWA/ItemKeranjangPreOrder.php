<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;
use App\PreOrderData;
use App\Kelas;

class ItemKeranjangPreOrder extends MenuAbstract{

	public $id, $customer, $kelas, $pesanan, $harga, $pmethod, $statusPembayaran;

	public function __construct($position, $id, $pesanan, $harga, $customer, $kelas, $pmethod, $statusPembayaran){

		$this->position = $position;
		
		$this->id = $id;
		
		$this->pesanan = $pesanan;
		
		$this->harga = $harga;
		
		$this->customer = $customer;
		
		$this->kelas = $kelas;
		
		$this->pmethod = $pmethod;
		
		$this->statusPembayaran = $statusPembayaran;
		
		$this->name = $pesanan." (Rp".number_format($harga, 0, ',', '.').")";

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		if($select[3] == 98){

			PreOrderData::where([['id', $this->id]])->update(['showMe'=>0]);

			array_splice($select, 3);

	    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

			return "Berhasil dihapus\n\n99. Keranjang belanja".$this->awal;

		}		

		if($this->pmethod==1){

				if(preg_match("/[^12]/", $select[3])){

					array_pop($select);

			    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

					return "Mohon masukkan pilihan Kamu lagi.";
					
				} elseif($select[3] == 1){

					PreOrderData::where([['id', $this->id]])->update(['statusPembayaran'=>3]);

					return "Konfirmasi berhasil dikirim. Kami akan mengecek pembayaran Kamu. Pengambilan barang tgl 19-23 Januari 2017 di kelas Kamu (".$this->kelas.") atau (untuk barang tertentu seperti printer) di sekitar kampus. Terima kasih.\n".$this->kembali.$this->awal;

				} else{

					PreOrderData::where([['id', $this->id]])->update(['statusPembayaran'=>2]);

					array_splice($select, 3);

					UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

					return "Pesanan telah dibatalkan.\n\n99. Keranjang belanja".$this->awal;

				}

			} elseif($this->pmethod==2){

				if(preg_match("/[^1]/", $select[3])){

					array_pop($select);

			    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

					return "Mohon masukkan pilihan Kamu lagi.";
					
				} else{

					PreOrderData::where([['id', $this->id]])->update(['statusPembayaran'=>2]);

					array_splice($select, 3);

					UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

					return "Pesanan telah dibatalkan.\n\n99. Keranjang belanja".$this->awal;

				}

			} 

	}

	public function showMenu(){

		$caraBayar = "";

		if($this->pmethod == 1){

			$konfirmasi = "*1. Konfirmasi*";

			if($this->statusPembayaran == 3) $konfirmasi = "1. Konfirmasi ulang";

			$caraBayar = "Mohon transfer sesuai total harga yang tertera (termasuk tiga angka terakhir) ke rek. BRI *1257-01-004085-50-9* a.n. Muh. Shamad selama masa pre order (16-18 Januari 2017).\n\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n".$konfirmasi."\n2. Batal";

		} else{

			$pjName = Kelas::where('kelas', $this->kelas)->value('pj');

			$caraBayar = "Silahkan hubungi ".$pjName." (PJ ".$this->kelas.") untuk pembayaran. Terima kasih.\n\n1. Batal";

		}

		if($this->statusPembayaran == 1) $caraBayar = "✅ Pembayaran telah dicek dan berhasil diterima. Kami akan mengantarkan pesanan Kamu tgl 19-23 Januari. Terima kasih.";

		return "📝 *Info Pesanan*\nNama : ".$this->customer."\nKelas : ".$this->kelas."\nTotal harga : Rp".number_format($this->harga, 0, ',', '.')."\n\n".$caraBayar."\n\n98. Hapus dari keranjang".$this->kembali.$this->awal;

	}

}
