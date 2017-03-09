<?php

namespace App\Libraries\KuotaWA;

#DB
use App\UserQuery;
use App\Transaksi;

class MenuAwal extends MenuAbstract{

	public function __construct($position, $name){

		$this->position = $position;
		
		$this->name = $name;

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select, $wa){

		if(!$this->checkOption($select[1])){

			return $this->wrongCommand($select, $wa);
		
		}

		$selectedMenuUtama = $this->subMenu[$select[1]];

		if(count($select)==2){

			return $selectedMenuUtama->showMenu();

		} else{

			return $selectedMenuUtama->select($select,$wa);

		}
		
	}

	public function showMenu(){

		$subMenuNames = array_map(create_function('$o', 'return $o->name;'), $this->getSubMenu());

        $response ="";
        
        foreach ($subMenuNames as $key => $name) {
        
            $response.=($key).". ".$name."\n";
        
        }

        $response = preg_replace("/\n$/", "",$response);

        $ttl = Transaksi::where('status',1)->count();

        return "*Menu:*\n".$response."\n\nTotal trx sukses:".($ttl+30);

	}

	public function run(CommandAbstract $wa){

		# Cek Query

        $commandsDB = UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->first();

        if($commandsDB==""){

        	$commandArray = array("0");

            $newUserCommands['commandArray'] = serialize($commandArray);

            $newUserCommands['sender'] = $wa->getFrom();

            $newUserCommands['platform'] = $wa->getPlatform();

            UserQuery::Create($newUserCommands);
        
            return $this->showMenu();

        } else{

        	if($wa->getCommand()=="0"){

        		$isSaved = UserQuery::where([['sender', $wa->getFrom()],['saved',0], ['activeTransaksiId', NULL]])->first();

        		if($isSaved != ""){

	        		$commandArray = array("0");

	        		UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($commandArray)]);
	        
	            	return $this->showMenu();

        		} else {

	        		UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['saved'=>1]);

		        	$commandArray = array("0");

		            $newUserCommands['commandArray'] = serialize($commandArray);

		            $newUserCommands['sender'] = $wa->getFrom();

		            $newUserCommands['platform'] = $wa->getPlatform();

		            UserQuery::Create($newUserCommands);
		        
		            return $this->showMenu();

        		}

        	} else{

	        	$commandArray = unserialize($commandsDB['commandArray']);

        		if($wa->getCommand()!="99"|count($commandArray)==1){

	        		array_push($commandArray, $wa->getCommand());

	        	} else{

	        		array_pop($commandArray);

					if(count($commandArray)==1){

        				UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($commandArray)]);

						return $this->showMenu();

					}
	        	}

        		UserQuery::where([['sender', $wa->getFrom()],['saved',0]])->update(['commandArray'=>serialize($commandArray)]);

        	}

			// return $commandArray;

        	return $this->select($commandArray, $wa);
        
        }

	}

}
