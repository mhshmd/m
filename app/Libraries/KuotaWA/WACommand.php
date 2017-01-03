<?php

namespace App\Libraries\KuotaWA;

class WACommand extends CommandAbstract{

    function __construct($message, $contact) {

        $message = preg_replace("/^\s*/", "",$message);

        #$message kosong = 8

        if($message==""){

            $message = "8";

        }

        $this->command = $message;
        
        $this->from = $contact;
        
        $this->platform = 1;

    }

    function getFrom(){
        
        $this->from = preg_replace("/-/", "",$this->from);
        
        $this->from = preg_replace("/62/", "0",$this->from);
        
        $this->from = preg_replace("/\s/", "",$this->from);
        
        return $this->from;
    
    }
    
    function getCommand(){
    
        return $this->command;
    
    }

}