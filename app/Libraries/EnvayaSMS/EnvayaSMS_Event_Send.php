<?php

namespace App\Libraries\EnvayaSMS;

/*
 * PHP server library for EnvayaSMS 3.0
 *
 * For example usage see example/www/gateway.php
 */
class EnvayaSMS_Event_Send extends EnvayaSMS_Event
{    
    public $messages;
    
    function __construct($messages /* array of EnvayaSMS_OutgoingMessage objects */)
    {
        $this->event = EnvayaSMS::EVENT_SEND;
        $this->messages = $messages;
    }
}