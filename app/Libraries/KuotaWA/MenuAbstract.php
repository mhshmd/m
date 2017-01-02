<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;

abstract class MenuAbstract {

	public $name;
	public $position;
	public $subMenu = array();
	public $kembali = "\n99. Menu sebelumnya";
	public $awal = "\n0. Menu awal";

	abstract public function showMenu();

	abstract public function addSubMenu($subMenu);

	abstract public function select($select, $wa);

	public function checkOption($select){

		if(isset($this->subMenu[$select])){
		
			return true;
		
		} else return false;

	}

	public function wrongCommand($select, $wa){

		array_pop($select);

    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

		return "Mohon masukkan pilihan Anda lagi.";

	}

	public function getName(){

		return $this->name;

	}

	public function getPosition(){

		return $this->position;

	}

	public function getSubMenu(){

		return $this->subMenu;

	}

	public function getWA(){

		return $this->wa;

	}

}