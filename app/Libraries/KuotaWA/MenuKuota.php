<?php

namespace App\Libraries\KuotaWA;

#DB
use App\Operator;
use App\Kuota;
use App\UserQuery;

class MenuKuota extends MenuAbstract{

	public function __construct($position, $name, $from){

		$this->position = $position;
		$this->name = $name;

		$operators = Operator::where('isKuotaSupported', 1)->select('name', 'cekNomor')->get();
        foreach ($operators as $key => $operator) {

            $this->subMenu[$key+1] = new OperatorKuota(($key+1), $operator->name, $operator->cekNomor, $from);            

        }

	}

	public function addSubMenu($subMenu){



	}

	public function select($select,$wa){

		if(!$this->checkOption($select[2])){

			return $this->wrongCommand($select, $wa);

		}

		$selectedOperator = $this->subMenu[$select[2]];

		$quotasByOp = Kuota::where([['operator', $select[2]], ['isPromo',0], ['isAvailable',1]])->select('name', 'kode', 'isPromo', 'hargaJual', 'gb3g', 'gb4g', 'days')->orderBy('hargaJual', 'asc')->get();

		$pos = 0;

		foreach ($quotasByOp as $key => $quo) {
			
			$quota = new Quota(($key+1), $selectedOperator->name, $quo->kode, $quo->name, $quo->isPromo, $quo->hargaJual, $quo->gb3g, $quo->gb4g, $quo->days, $selectedOperator->cekNomor);

            $selectedOperator->addSubMenu($quota);   

            $pos++;         

        }

		$quotasByOp = Kuota::where([['operator', $select[2]], ['isPromo',1], ['isAvailable',1]])->select('name', 'kode', 'isPromo', 'hargaJual', 'gb3g', 'gb4g', 'days')->orderBy('hargaJual', 'asc')->get();

		foreach ($quotasByOp as $key => $quo) {
			
			$quota = new Quota(($pos + $key + 1), $selectedOperator->name, $quo->kode, $quo->name, $quo->isPromo, $quo->hargaJual, $quo->gb3g, $quo->gb4g, $quo->days, $selectedOperator->cekNomor);

            $selectedOperator->addSubMenu($quota);            

        }

        if(count($select)==3){

			return $selectedOperator->showMenu();

		} else{

			return $selectedOperator->select($select, $wa);

		}

	}

	public function showMenu(){

		$subMenuNames = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

        $response ="";
        
        foreach ($subMenuNames as $key => $name) {

            $response.=($key).". ".$name."\n";

        }

        return "*Operator:*\n".$response.$this->awal;

	}

}
