<?php

use Phalcon\Mvc\Model;

class BotLog extends Model
{
    public $bot_log_id;

    public $bot_id;
    
    public $state;

    public $data;
    
    public $input;
}