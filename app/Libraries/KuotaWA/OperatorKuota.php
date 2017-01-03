<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;

class OperatorKuota extends MenuAbstract{

	public $cekNomor;

	public $subMenuPromo = array();

	public function __construct($position, $name, $cekNomor){

		$this->position = $position;
		
		$this->name = $name;
		
		$this->cekNomor = $cekNomor;

	}

	public function addSubMenu($subMenu){

		if(!($subMenu->isPromo)){
		
			$this->subMenu[$subMenu->getPosition()] = $subMenu;
		
		} else{
		
			$this->subMenuPromo[$subMenu->getPosition()] = $subMenu;
		
		}

	}

	public function addSubMenuPromo($subMenu){

		$this->subMenuPromo[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		$all = $this->subMenu + $this->subMenuPromo;

		if(!isset($all[$select[3]])){

			return $this->wrongCommand($select, $wa);
		
		}

		$selectedQuota = $all[$select[3]];

        if(count($select)==4){

        	$isSaved = UserQuery::where([['sender', $wa->getFrom()],['saved',0], ['activeTransaksiId', NULL]])->first();

    		if($isSaved == ""){

    			UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['saved'=>1]);

	            $newUserCommands['commandArray'] = serialize($select);

	            $newUserCommands['sender'] = $wa->getFrom();

	            $newUserCommands['platform'] = $wa->getPlatform();

	            UserQuery::Create($newUserCommands);
            }

			return $selectedQuota->showMenu();

		} else{

			return $selectedQuota->select($select,$wa);

		}

	}

	public function showMenu(){

		$subMenuNames1 = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

        $response ="";
        
        foreach ($subMenuNames1 as $key => $name) {
        
            $response.=($key).". ".$name."\n";
        
        }

		$subMenuNames2 = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuPromo());
		
		if(sizeof($subMenuNames2)>0){
		
			$response.="\n🎁Promo🎁\n";
	    
	        foreach ($subMenuNames2 as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";
	    
	        }
		
		}

        return "*Harga kuota ".$this->name.":*\nharga (total kuota)\n".$response.$this->kembali.$this->awal;

	}

	public function getSubMenuPromo(){

		return $this->subMenuPromo;

	}

}
