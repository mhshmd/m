<?php

namespace App\Libraries\KuotaWA;

class SMSCommand extends CommandAbstract{

    function __construct($message, $contact) {

        $this->command = $message;
        
        $this->from = $contact;
        
        $this->platform = 2;

    }

    function getFrom(){
        
        // $this->from = preg_replace("/^(\+62)/", "0",$this->from);

        $this->from = preg_replace("/[^a-zA-Z0-9]/", "",$this->from);
        
        return $this->from;
    
    }
    
    function getCommand(){
    
        return $this->command;
    
    }

}