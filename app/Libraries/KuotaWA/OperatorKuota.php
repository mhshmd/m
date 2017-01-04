<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;
use App\Kuota;
use App\Operator;

class OperatorKuota extends MenuAbstract{

	public $cekNomor, $from;

	public $subMenuPromo = array();

	public function __construct($position, $name, $cekNomor, $from){

		$this->position = $position;
		
		$this->name = $name;
		
		$this->cekNomor = $cekNomor;
		
		$this->from = $from;

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

		$currentKuotaList = UserQuery::where([['sender', $this->from],['saved',0]])->value('currentKuotaList');

		$currentKuotaList = unserialize($currentKuotaList);

		if(!isset($currentKuotaList[$select[3]])){

			return $this->wrongCommand($select, $wa);
		
		}

		$quo = Kuota::where('kode', $currentKuotaList[$select[3]])->select('name', 'kode', 'isPromo', 'hargaJual', 'gb3g', 'gb4g', 'days', 'operator')->first();

		$selectedOperator = Operator::where('id', $quo->operator)->select('name', 'cekNomor')->first();

		$selectedQuota = new Quota(1, $selectedOperator['name'], $quo->kode, $quo->name, $quo->isPromo, $quo->hargaJual, $quo->gb3g, $quo->gb4g, $quo->days, $selectedOperator['cekNomor']);

        if(count($select)==4){

        	$isSaved = UserQuery::where([['sender', $wa->getFrom()],['saved',0], ['activeTransaksiId', NULL]])->first();

    		if($isSaved == ""){

	            $currentKuotaList = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->value('currentKuotaList');

	            $newUserCommands['currentKuotaList'] = $currentKuotaList;

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

		$currentKuotaList = array();

		$subMenuNames1 = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

		$subMenuNames1Kode = array_map(create_function('$o', 'return $o->kode;'), $this->getSubMenu());

        $response ="";
        
        foreach ($subMenuNames1 as $key => $name) {
        
            $response.=($key).". ".$name."\n";

            $currentKuotaList[$key] = $subMenuNames1Kode[$key];
        
        }

		$subMenuNames2 = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenuPromo());

		$subMenuNames2Kode = array_map(create_function('$o', 'return $o->kode;'), $this->getSubMenuPromo());
		
		if(sizeof($subMenuNames2)>0){
		
			$response.="\n🎁Promo🎁\n";
	    
	        foreach ($subMenuNames2 as $key => $name) {
	    
	            $response.=($key).". ".$name."\n";

            	$currentKuotaList[$key] = $subMenuNames2Kode[$key];
	    
	        }
		
		}

    	UserQuery::where([['sender', $this->from],['saved',0]])->update(['currentKuotaList'=>serialize($currentKuotaList)]);

        return "*Harga kuota ".$this->name.":*\nharga (total kuota)\n".$response.$this->kembali.$this->awal;

	}

	public function getSubMenuPromo(){

		return $this->subMenuPromo;

	}

}
