<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $user_id;

    public $token;

    public $email;

    public $username;

    public $user_state;

    function getHash($suffix=null) {
        return md5('4fjnqeflqekmflwkemfw'.$this->user_id.$suffix);
    }
}