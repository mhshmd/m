<?php

namespace App\Libraries\KuotaWA;

#DB
use App\Kelas;
use App\UserQuery;

class PreOrder extends MenuAbstract{

	public function __construct($position, $name){

		$this->position = $position;

		$this->name = $name;

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		if(count($select)==3){

			if($select[2] == 8){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan nama Kamu lagi.";

			}

			return "*Tingkat:*\n1. I\n2. II\n3. III\n4. IV\n\n99. Ubah nama".$this->awal;

		} elseif(count($select)==4){

			if($select[3]>4||$select[3]<1){

				array_pop($select);

		    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

				return "Mohon masukkan pilihan Kamu lagi.";
				
			}

			$kelasByTk = Kelas::where('tingkat', $select[3])->select('kelas')->orderBy('kelas', 'asc')->get();

			foreach ($kelasByTk as $key => $kelas) {
				
				$kelas = new KelasPreOrder(($key + 1), $kelas->kelas);

				$this->addSubMenu($kelas);  

			}

			$subMenuNames = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

	        $response ="";
	        
	        foreach ($subMenuNames as $key => $name) {

	            $response.=($key).". ".$name."\n";

	        }

	        return "*Kelas:*\n".$response."\n99. Ubah tingkat".$this->awal;

		}

		$kelasByTk = Kelas::where('tingkat', $select[3])->select('kelas')->orderBy('kelas', 'asc')->get();

		foreach ($kelasByTk as $key => $kelas) {
			
			$kelas = new KelasPreOrder(($key + 1), $kelas->kelas);

			$this->addSubMenu($kelas);  

		}

		if($select[4]>count($this->subMenu)||$select[4]<1){

			array_pop($select);

	    	UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($select)]);

			return "Mohon masukkan pilihan Kamu lagi.";
			
		}

		return $this->subMenu[$select[4]]->select($select, $wa);

	}

	public function showMenu(){		

        return "*Masukkan nama Kamu:*\n(cth: Muh. Shamad)\n".$this->awal;

	}

}
