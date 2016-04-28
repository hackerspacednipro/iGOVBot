<?php

use Phalcon\Mvc\Controller;

class BotController extends Controller
{

    public function indexAction()
    {

        $this->view->disable();

        $json = file_get_contents('php://input');
        $data = json_decode($json, TRUE);


        $bot_id=intval($_REQUEST['bot_id']);

        if (!$bot_id) $bot_id=2;
        
        $bot = Bots::findfirst($bot_id);
        if (!$bot) die ('No exist bot way');
        if ($bot->password!=md5($_REQUEST['password'])) die ('No password way');

        $log=new Logs();
        $log->text=$json;
        $log->type='MESSAGE';

        if (!($data['update_id']>0)) die('No way!');
        $user_id=$data['message']['from']['id'];

        if ($user_id) {
            $log->user_id=$user_id;
        }

        $log->save();

        $user=Users::findFirst($user_id);
        if (!$user) {
            $user = new Users();
            $user->user_id=$user_id;
            $user->username=$data['message']['from']['username'];
            $user->create();
        } else {
            if ((!$user->username) AND ($data['message']['from']['username'])) {
                $user->username=$data['message']['from']['username'];
                $user->save();
            }
        }

        if (!$user) die('No way for user');

        $text=$data['message']['text'];
        $chat_id=$data['message']['chat']['id'];
        

        $myBot=new FSMService($bot);
        $myBot->run($user, $text);
        
        
        


    }
}