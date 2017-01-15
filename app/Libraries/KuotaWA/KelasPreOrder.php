<?php

namespace App\Libraries\KuotaWA;

#DB
use App\Product;
use App\ProductCategory;
use App\UserQuery;
use App\Kelas;
use App\PreOrderData;

class KelasPreOrder extends MenuAbstract{

	public function __construct($position, $name){

		$this->position = $position;

		$this->name = $name;

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		if(count($select)==5) {

			return "*Masukkan kode pesanan Kamu:*\n(contoh: m1 atau m1.k1.h2 jika lebih dari satu)\n\n98. Lihat semua kode\n99. Ubah kelas".$this->awal;

		}

		if(count($select)==6) {

			if($select[5] == 98){

				$allProductCategory = ProductCategory::where('id','>',0)->orderBy('name', 'asc')->get();

				foreach ($allProductCategory as $key => $cat) {
					
					$this->addSubMenu(new ProductCategorySubMenu(($key + 1), $cat['id'], $cat['name']));

				}

				return $this->showMenu();

			}

			preg_match_all("/(?<=\.)?\w{1,20}(?=\.)?/", $select[5], $pesanan);

			$response = "*Pesanan :*\n";

			$totalHarga = 0;

			foreach ($pesanan[0] as $key => $value) {

				$order = Product::where('kode', $value)->first();

				if($order != ""){

					$response .= "- ".$order['name']." Rp".number_format($order['hargaJual'], 0, ',', '.')."\n";

					$totalHarga += $order['hargaJual'];

				} else{

					$response .= "- (".$value." tidak ditemukan) Rp 0\n";

				}
				
			}

			$lanjut = "\n1. Lanjutkan\n";

			if($totalHarga == 0) $lanjut = "";

			$response .= "\nTotal = Rp".number_format($totalHarga, 0, ',', '.')."\n".$lanjut;

			UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['currentKuotaList'=>$totalHarga]);

			return $response."\n99. Ubah pesanan".$this->awal;

		} elseif(count($select)==7) {

			if($select[5] == 98){

				$allProductCategory = ProductCategory::where('id','>',0)->orderBy('name', 'asc')->get();

				foreach ($allProductCategory as $key => $cat) {
					
					$this->addSubMenu(new ProductCategorySubMenu(($key + 1), $cat['id'], $cat['name']));

				}

				if(!$this->checkOption($select[6])){

					return $this->wrongCommand($select, $wa);

				}

				return $this->subMenu[$select[6]]->showMenu();

			}

			$totalHarga = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->value('currentKuotaList');

			if($select[6]!=1||$totalHarga == 0){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan pilihan Kamu lagi.";
				
			}

			return "*Pilih cara pembayaran:*\n1. Transfer ATM/Bank\n2. Melalui PJ\n".$this->kembali.$this->awal;

		} elseif(count($select)==8) {

			if($select[5] == 98){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan pilihan Kamu lagi.";
				
			}

			if(preg_match("/[^12]/", $select[7])){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan pilihan Kamu lagi.";
				
			}

			$totalHarga = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->value('currentKuotaList');

			$newOrder['sender'] = $wa->getFrom();

			$newOrder['name'] = $select[2];

			$newOrder['kelas'] = $this->name;

			$newOrder['pesanan'] = $select[5];

			$sand = 0;

			if($select[7]==1){

				$sand = rand(1,99);

			}

			$newOrder['totalHarga'] = $totalHarga - $sand;

			$newOrder['pmethod'] = $select[7];

			$checkId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->first();

			$konfirmasi = "*1. Konfirmasi*";

			if($checkId['activeTransaksiId']==""){

				while (true) {

		            $searchUniqueSand = PreOrderData::where([['totalHarga', $newOrder['totalHarga']], ['statusPembayaran', 0]])->first();

		            if($searchUniqueSand==""){

		                break;

		            }

		            $newOrder['totalHarga'] += $sand;

		            $sand = rand(1,199);

		            $newOrder['totalHarga'] -= $sand;

		        }

				$id = PreOrderData::Create($newOrder);

				UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['activeTransaksiId'=> $id['id']]);

			} else{

				$currentOrder = PreOrderData::where('id', $checkId['activeTransaksiId'])->first();

				if($currentOrder['statusPembayaran'] == 3) $konfirmasi = "1. Konfirmasi ulang";

				if($currentOrder['pesanan'] == $select[5]){

					if($select[7] == 1 && ($select[7] == $currentOrder['pmethod'])){

						$newOrder['totalHarga'] = $currentOrder['totalHarga'];

					}

				}

				PreOrderData::where('id', $checkId['activeTransaksiId'])->update($newOrder);
				
			}

			$caraBayar = "";

			if($select[7] == 1){

				$caraBayar = "Mohon transfer sesuai total harga yang tertera (termasuk tiga angka terakhir) ke rek. BRI *1257-01-004085-50-9* a.n. Muh. Shamad selama masa pre-order (16-18 Januari 2017).\n\nSetelah transfer, mohon pilih 1 untuk konfirmasi.\n\n".$konfirmasi."\n2. Batal";

			} else{

				$pjName = Kelas::where('kelas', $this->name)->value('pj');

				$caraBayar = "Silahkan hubungi ".$pjName." (PJ ".$this->name.") untuk pembayaran. Terima kasih.\n\n1. Batal";

			}

			return "✅ Pesanan berhasil dicatat.\n\n📝 *Info Pesanan*\nNama : ".$select[2]."\nKelas : ".$this->name."\nTotal harga : Rp".number_format($newOrder['totalHarga'], 0, ',', '.')."\n\n".$caraBayar."\n\n99. Ubah cara pembayaran".$this->awal;

		} elseif(count($select)==9){

			if($select[7]==1){

				if(preg_match("/[^12]/", $select[8])){

					array_pop($select);

			    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

					return "Mohon masukkan pilihan Kamu lagi.";
					
				} elseif($select[8] == 1){

					$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

					PreOrderData::where([['id', $activeTransaksiId]])->update(['statusPembayaran'=>3]);

					return "Konfirmasi berhasil dikirim. Kami akan mengecek pembayaran Kamu. Pengambilan barang tgl 19-23 Januari 2017 di kelas Kamu (".$this->name.") atau (untuk barang tertentu seperti printer) di sekitar kampus. Terima kasih.\n".$this->kembali.$this->awal;

				} else{

					$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

					PreOrderData::where([['id', $activeTransaksiId]])->update(['statusPembayaran'=>2]);

					array_splice($select, 6);

					UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

					return "Pesanan telah dibatalkan.\n\n99. Ubah pesanan".$this->awal;

				}

			} elseif($select[7]==2){

				if(preg_match("/[^1]/", $select[8])){

					array_pop($select);

			    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

					return "Mohon masukkan pilihan Kamu lagi.";
					
				} else{

					$activeTransaksiId = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->select('activeTransaksiId')->value('activeTransaksiId');

					PreOrderData::where([['id', $activeTransaksiId]])->update(['statusPembayaran'=>2]);

					array_splice($select, 6);

					UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select), 'activeTransaksiId'=>NULL]);

					return "Pesanan telah dibatalkan.\n\n99. Ubah pesanan".$this->awal;

				}

			} 

		}

	}

	public function showMenu(){		

		$subMenuCategory = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

        $response ="";

        if(sizeof($subMenuCategory)>0){
		
			$response.="*Kategori:*\n";
	    
	        foreach ($subMenuCategory as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

		return $response.$this->kembali.$this->awal;

	}

	public function getSubMenu() {

		return $this->subMenu;

	}

}
