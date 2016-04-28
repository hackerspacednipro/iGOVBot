<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 3/25/16
 * Time: 4:42 PM
 */

class BotService {

    private $botkey;

    function __construct($key)
    {
        $this->botkey=$key;
    }

    function sendMessage($chat_id,$text, $markup=null)
    {
        $params = array('chat_id' => $chat_id, 'text' => $text, 'disable_web_page_preview' => 'true');
        if ($markup) {
            $params['reply_markup'] = json_encode($markup);
        }

        $this->run('sendMessage', $params);
    }
    function sendHTMLmessage($chat_id,$text, $markup=null) {
        $params = array('chat_id' => $chat_id, 'text' => $text, 'disable_web_page_preview' => 'true', 'parse_mode'=>'HTML');
        if ($markup) {
            $params['reply_markup'] = json_encode($markup);
        }
        $this->run('sendMessage', $params);
    }

    function sendChatAction($chat_id, $action)
    {
        $this->run('sendChatAction', array('chat_id'=>$chat_id,'action'=>$action));
    }

    function run($function, $params) {

        $link='https://api.telegram.org/bot'.$this->botkey.'/'.$function;

        $data_string = json_encode($params);

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);

        $log=new Logs();
        $log->text=$result;
        $log->type='SENDMESSAGE';

        if($result === FALSE) {
            $log->text=curl_error($ch);
        }

        $log->save();
        curl_close($ch);


    }
}