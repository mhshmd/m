<?php

namespace App\Libraries\KuotaWA;

abstract class CommandAbstract {

    var $command;
    var $from;
    var $platform;

    abstract function getFrom();
    
    abstract function getCommand();
    
    function getPlatform(){
    
        return $this->platform;
    
    }

}