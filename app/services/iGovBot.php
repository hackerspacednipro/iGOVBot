<?php
use Phalcon\Di;



class iGovBot extends TinyBot{


    function search($params) {
        global $di;

        //TODO PDO only, fuck the ORM

        $connection=$di->get('pdoconnecton');
        $cache=$di->get('modelsCache');


        $input=$params['input'];
        $user_id=$params['user']->user_id;

        $log = new LegalSearches();
        $log->user_id=$user_id;
        $log->term=$input;
        $log->save();

        $input=trim($input);
        $message='Ничего не смогли найти';


        if (mb_strlen($input,'UTF-8')<=2) return array('state'=>'default', 'message'=>'Поиск работает от 3 символов и больше');
        if ((is_numeric($input)) AND (mb_strlen($input,'UTF-8')==8)) {

            $legal =  LegalEntities::findFirst(array('code='.$input, "cache" => array("key"=> md5("code".$input), "lifetime" => 86400 )));
            if ($legal) {
                $message="По коду <b>".$input."</b> найдена компания \n\n".$this->showCompaniesList(array($legal));


            }


        } else {

            $sql="SELECT COUNT(*) AS cs FROM legal_entities WHERE full_name LIKE '%".$input."%'";
            $msql=md5($sql);
            $lc=$cache->get($msql);
            if ($lc===null) {
                $res = $connection->query($sql);
                $r=$res->fetch();
                $cache->save($msql, $r['cs']);
                $lc=$r['cs'];
            }


            if ($lc>0) {

                $legals=LegalEntities::find(array("full_name LIKE '%".$input."%'", "cache" => array("key"=> md5("legal".$input), "lifetime" => 86400),'limit'=>10));
                $count=$legals->count();

                $message="По названию <b>".$input."</b> найдено компаний — ". $lc."\n\n";
                if ($lc>10) {
                    $message.="Показываем первые 10 \n\n";
                }

                $message.=$this->showCompaniesList($legals);
            } else {
                //ищем по имени директора



                $sql="SELECT COUNT(*) AS cs FROM legal_entities WHERE ceo_name  LIKE '".$input."%'";
                $msql=md5($sql);
                $lc=$cache->get($msql);
                if ($lc===null) {
                    $res = $connection->query($sql);
                    $r=$res->fetch();
                    $cache->save($msql, $r['cs']);
                    $lc=$r['cs'];
                }


                if ($lc>0) {

                    $legals=LegalEntities::find(array("ceo_name LIKE '".$input."%'", "cache" => array("key"=> md5("ceo".$input), "lifetime" => 86400), 'limit'=>10 ));
                    $count=$legals->count();

                    $message = "По имени директора <b>".$input."</b> найдено компаний — " . $lc . "\n\n";
                    if ($lc > 10) {
                        $message .= "Показываем первые 10 \n\n";
                    }
                    $message.=$this->showCompaniesList($legals);
                }


            }






        }



        return array('state'=>'default', 'message'=>$message);
    }

    function showCompaniesList($list, $maxcount=10)
    {

        $result = array();
        $count = (count($list) > $maxcount) ? $maxcount : count($list);

        for ($i = 0; $i < $count; $i++) {

            $result[] = '<b>' . $list[$i]->full_name . '</b>' . "\n" .
                '<i>Код:</i> ' . $list[$i]->code . "\n" .
                '<i>Адрес:</i> ' . $list[$i]->location . "\n" .
                '<i>Директор:</i> ' . $list[$i]->ceo_name . "\n" .
                '<i>Вид деятельности:</i> ' . $list[$i]->activities . "\n" .
                '<i>Состояние:</i> ' . $list[$i]->status . "\n";

        }
        $text = implode("\n\n", $result);

        return $text;

    }


    function subscription($params) {
        $input=$params['input'];
        $input=trim($input);
        $message='Не смогли подписаться на уведомления по компании с кодом '.$input;
        $state='subscription';


        if ((is_numeric($input)) AND (mb_strlen($input,'UTF-8')==8)) {
            $entity_id=intval($input);
            $legal =  LegalEntities::findFirst('code='.$entity_id);
            if ($legal) {
                $user_id=$params['user']->user_id;
                $subs=LegalUsers::findFirst('entity_id='.$entity_id.' AND user_id='.$user_id);
                if ($subs) {
                    if ($subs->status==1) {
                        $message="Вы уже подписаны на уведомления по компании с кодом ".$entity_id;
                    } else  {
                        $subs->status=1;
                        $subs->save();
                        $message="Вы снова подписаны на уведомления по компании с кодом ".$entity_id;
                    }

                } else {
                    $subs = new LegalUsers();
                    $subs->user_id=$user_id;
                    $subs->entity_id=$entity_id;
                    $subs->status=1;
                    $subs->save();
                    $message="Вы подписаны на уведомления по компании с кодом ".$entity_id;
                }
                $state='search';

            } else {
                $message="Компании с кодом ".$entity_id.' не найдено, проверьте код и введите снова';
            }
        } else {
            $message="Для подписки нужен восьмизначный код компании, проверьте код и введите снова";
        }

        return array('state'=>$state, 'message'=>$message);
    }

    function unsubscription($params) {
        $input=$params['input'];
        $input=trim($input);
        $state='unsubscription';


        if ((is_numeric($input)) AND (mb_strlen($input,'UTF-8')==8)) {
            $entity_id=intval($input);
            $user_id=$params['user']->user_id;
            $subs=LegalUsers::findFirst('entity_id='.$entity_id.' AND user_id='.$user_id);
            if ($subs) {
                if ($subs->status == 1) {
                    $message = "Вы больше не получите уведомления по компании с кодом " . $entity_id;
                    $subs->status = 0;
                    $subs->save();
                } else {
                    $message = "Вы не были подписаны на уведомления по компании с кодом " . $entity_id;
                }
            } else {
                $message = "Вы не были подписаны на уведомления по компании с кодом " . $entity_id;
            }

            $state='search';
        } else {
            $message="Для отписки нужен восьмизначный код компании, проверьте код и введите снова";
        }

        return array('state'=>$state, 'message'=>$message);
    }
}
