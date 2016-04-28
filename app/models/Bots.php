<?php

use Phalcon\Mvc\Model;

class Bots extends Model
{

    public $bot_id;

    public $token;

    public $name;
    
    public $payment_account;

    public $payment_domain;

    public $payment_signature;

    public $owner_id;
}