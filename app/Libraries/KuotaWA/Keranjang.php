<?php

namespace App\Libraries\KuotaWA;

# DB
use App\Transaksi;
use App\PreOrderData;

class Keranjang extends MenuAbstract{

	public $subMenuProses = array();
	public $subMenuSukses = array();
	public $subMenuDibatalkan = array();
	public $subMenuPreOrder = array();

	public function __construct($position, $name, $from){

		$this->position = $position;

		$this->name = $name;

		$transaksiCurrentUser = Transaksi::where([['sender', $from], ['status', 0], ['showMe', 1]])->select('id', 'pmethod', 'kode', 'tujuan', 'hargaBayar', 'status')->get();

		$pos = 0;

		foreach ($transaksiCurrentUser as $key => $transaksi) {
			
			$this->addSubMenuDalamProses(new ItemKeranjang(($key+1), $transaksi->id, $transaksi->pmethod, $transaksi->kode, $transaksi->tujuan, $transaksi->hargaBayar, $transaksi->status));

			$pos++;

		}

		$transaksiCurrentUser = Transaksi::where([['sender', $from], ['status', 1], ['showMe', 1]])->select('id', 'pmethod', 'kode', 'tujuan', 'hargaBayar', 'status')->get();

		$pos2 = $pos;

		foreach ($transaksiCurrentUser as $key => $transaksi) {
			
			$this->addSubMenuSukses(new ItemKeranjang(($pos + $key + 1), $transaksi->id, $transaksi->pmethod, $transaksi->kode, $transaksi->tujuan, $transaksi->hargaBayar, $transaksi->status));

			$pos2++;

		}

		$transaksiCurrentUser = Transaksi::where([['sender', $from], ['status', 2], ['showMe', 1]])->select('id', 'pmethod', 'kode', 'tujuan', 'hargaBayar', 'status')->get();

		$pos3 = $pos2;

		foreach ($transaksiCurrentUser as $key => $transaksi) {
			
			$this->addSubMenuDibatalkan(new ItemKeranjang(($pos2 + $key + 1), $transaksi->id, $transaksi->pmethod, $transaksi->kode, $transaksi->tujuan, $transaksi->hargaBayar, $transaksi->status));

			$pos3++;

		}

		$preOrderCurrentUser = PreOrderData::where([['sender', $from], ['statusPembayaran',"!=", 2], ['showMe', 1]])->get();

		foreach ($preOrderCurrentUser as $key => $transaksi) {
			
			$this->addSubMenuPreOrder(new ItemKeranjangPreOrder(($pos3 + $key + 1), $transaksi->id, $transaksi->pesanan, $transaksi->totalHarga, $transaksi->name, $transaksi->kelas, $transaksi->pmethod, $transaksi->statusPembayaran));

		}

	}

	public function addSubMenuDalamProses($subMenu){

		$this->subMenuProses[$subMenu->getPosition()] = $subMenu;

	}

	public function addSubMenuSukses($subMenu){

		$this->subMenuSukses[$subMenu->getPosition()] = $subMenu;

	}

	public function addSubMenuDibatalkan($subMenu){

		$this->subMenuDibatalkan[$subMenu->getPosition()] = $subMenu;

	}

	public function addSubMenuPreOrder($subMenu){

		$this->subMenuPreOrder[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select, $wa){

		$this->subMenu = $this->subMenuProses + $this->subMenuSukses + $this->subMenuDibatalkan + $this->subMenuPreOrder;

		if(!$this->checkOption($select[2])&&$select[2]<>"98"){

			return $this->wrongCommand($select, $wa);
		
		}

		if($select[2]=="98"){

			Transaksi::where([['sender', $wa->getFrom()], ['showMe', 1]])->update(['showMe'=>0]);

			PreOrderData::where([['sender', $wa->getFrom()], ['showMe', 1]])->update(['showMe'=>0]);

			return "Berhasil dikosongkan.\n".$this->kembali.$this->awal;

		}

		$selectedTransaction = $this->subMenu[$select[2]];

		if(count($select)==3){

			return $selectedTransaction->showMenu();

		} else{

			return $selectedTransaction->select($select,$wa);

		}

	}

	public function showMenu(){

		$subMenuProses = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuProses());

		$subMenuSukses = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuSukses());

		$subMenuDibatalkan = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuDibatalkan());

		$subMenuPreOrder = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuPreOrder());

        $response ="";

        if(sizeof($subMenuProses)>0){
		
			$response.="\n*Dalam proses*\n";
	    
	        foreach ($subMenuProses as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

		if(sizeof($subMenuSukses)>0){
		
			$response.="\n*Sukses*\n";
	    
	        foreach ($subMenuSukses as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

		if(sizeof($subMenuDibatalkan)>0){
		
			$response.="\n*Dibatalkan*\n";
	    
	        foreach ($subMenuDibatalkan as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

		if(sizeof($subMenuPreOrder)>0){
		
			$response.="\n*Pre Order*\n";
	    
	        foreach ($subMenuPreOrder as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

		if($response!=""){

        	return "*Keranjang belanja:*\n".$response."\n98. Kosongkan keranjang".$this->awal;

		} else {

			return "*Keranjang belanja:*\n\n(kosong)\n".$this->awal;

		}

	}

	public function getSubMenuProses() {

		return $this->subMenuProses;

	}

	public function getSubMenuSukses() {

		return $this->subMenuSukses;

	}

	public function getSubMenuDibatalkan() {

		return $this->subMenuDibatalkan;

	}

	public function getSubMenuPreOrder() {

		return $this->subMenuPreOrder;

	}

	public function addSubMenu($subMenu) {



	}

}
