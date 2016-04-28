<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 3/25/16
 * Time: 4:42 PM
 */

class FSMService {

    private $bot_id;
    private $data;
    private $config;
    private $user;
    private $owner;


    function __construct($bot) {
        $this->bot=$bot;
        $this->owner=Users::findFirst($this->bot->owner_id);
    }


    function run($user, $input) {

        $this->config=BotConfig::find("bot_id=".$this->bot->bot_id);
        $this->user=$user;



        $user_state=BotStates::findFirst("user_id=".$user->user_id." AND bot_id=".$this->bot->bot_id);
        if (!$user_state) {
            $user_state=new BotStates();
            $user_state->bot_id=$this->bot->bot_id;
            $user_state->user_id=$user->user_id;
            $user_state->save();
        }

        $possibleState=$this->getStateByName($input);


        $currentState = null;

        if ($possibleState) {
            $currentState=$possibleState;
        } else {
            if ($user_state->state) {
                $currentState=$this->getStateByName($user_state->state);
                $this->data=json_decode($user_state->data, 1);
            }
        }

        if (!$currentState) {
            $currentState=$this->getStateByName('error');
            $this->data['errormessage']='Нет такой команды';
        }

        $counter=0;
        do {
            $this->log($currentState, $input);
            $continue=false;
            if ($currentState->wait==0) $continue=true;

            $counter++;
            $currentState=$this->step($currentState, $input);

            if ($currentState) {
                $user_state->state = $currentState->name;
                $user_state->data = json_encode($this->data);
                $user_state->save();
            }

            $input='';

        } while (($continue) AND ($currentState) AND ($counter<10));

    }
    function step($state, $input) {
        $state_data=json_decode($state->params);
        $nextState=null;


        $botService=new BotService($this->bot->token);


        switch($state->type) {
            case 'MESSAGE':
                $message = $this->processString($state_data->message);
                $botService->sendMessage($this->user->user_id, $message);
                if ($state->default) $nextState = $this->getStateByName($state->default);

                break;
            case 'CHECK':
                if ($state->input) $this->data[$state->input] = $input;
                $action = 'default';
                foreach ($state_data AS $row) {
                    $cond = false;
                    $condition = '$cond=(' . $this->processString($row->condition) . ');';
                    eval($condition);
                    if ($cond == 1) {
                        if ($row->yes->action) {
                            $action = $row->yes->action;
                            $message = $row->yes->message;
                        }
                    } else {
                        if ($row->no->action) {
                            $action = $row->no->action;
                            $message = $row->no->message;
                        }
                    }
                    if ($action != 'default') break;
                }
                if ($action == 'default') {
                    if ($state->default) $nextState = $this->getStateByName($state->default);
                } else {
                    $nextState=$this->getStateByName($action);
                    $botService->sendMessage($this->user->user_id, $message);
                }
                break;

            case 'FUNCTION':
                $botService->sendChatAction($this->user->user_id, 'typing');

                if ($state->input) $this->data[$state->input] = $input;
                $action = 'default';

                if (class_exists($this->bot->name)) {
                    $tiny = new $this->bot->name;
                    if (method_exists($tiny, $state->name)) {
                        $params=array('input'=>$input, 'user'=>$this->user);
                        $vector=call_user_func(array($tiny, $state->name), $params);
                        $action=$vector['state'];
                        $message=$vector['message'];
                    }
                }
                if ($action == 'default') {
                    if ($state->default) $nextState = $this->getStateByName($state->default);
                } else {
                    $nextState=$this->getStateByName($action);
                }
                if ($message) $botService->sendHTMLmessage($this->user->user_id, $message);

                break;
        }


        return $nextState;
    }

    function getStateByName($name) {
        foreach($this->config AS $state) {
            if ($state->name==$name) return $state;
        }
        return null;
    }

    function log($state, $input) {
        $log = new BotLog();
        $log->bot_id=$this->bot->bot_id;
        $log->user_id=$this->user->user_id;
        $log->state=$state->name;
        $log->data=json_encode($this->data);
        $log->input=$input;
        $log->save();

    }

    function processString($string) {
        $result=$string;
        if ($this->data) {
            foreach($this->data as $key=>$value)
            $result=str_replace('%'.$key.'%',$value, $result);
        }
        $result=str_replace('%time()%',time(), $result);
        return $result;
    }

}