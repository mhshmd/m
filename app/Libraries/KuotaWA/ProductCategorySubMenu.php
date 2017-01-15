<?php

namespace App\Libraries\KuotaWA;

#DB
use App\Product;

class ProductCategorySubMenu extends MenuAbstract{

	public $id;

	public function __construct($position, $id, $name){

		$this->position = $position;
		
		$this->id = $id;
		
		$this->name = $name;

	}

	public function addSubMenu($subMenu){

		$this->subMenu[$subMenu->getPosition()] = $subMenu;

	}

	public function select($select,$wa){

		return "Your selection is what?";

	}

	public function showMenu(){

		$allProduct = Product::where('category', $this->id)->get();

		$subMenu = "*".$this->name.":*\nkode|nama|harga\n";

		foreach ($allProduct as $key => $product) {
			
			$subMenu .= "- ".$product['kode']."|".$product['name']."|Rp".number_format($product['hargaJual'], 0, ',', '.')."\n\n";

		}

		$subMenu = preg_replace("/\s$/", "",$subMenu);

		$subMenu .= "\n";

		return $subMenu.$this->kembali.$this->awal;

	}

}
