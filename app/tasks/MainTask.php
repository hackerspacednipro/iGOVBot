<?php

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo "\nThis is the default task and the default action \n";
    }

    public function updateAction()
    {
        $di = $this->di;
        $connection = $di->get('pdoconnecton');


        $sql = "SELECT * FROM legal_updates WHERE status IS NULL ORDER BY date LIMIT 1";
        $res = $connection->query($sql);
        if ($current = $res->fetch()) {

            $sql = "SELECT * FROM legal_updates WHERE status=1 ORDER BY date DESC LIMIT 1";
            $res = $connection->query($sql);
            if ($previous = $res->fetch()) {
                $table = "legal_entities_update_" . str_replace("-", "_", $current['date']);

                //сначала пометим всех, кто не изменился
                $sql = "UPDATE " . $table . " ";
                $sql .= "LEFT JOIN legal_entities ON legal_entities.code=" . $table . ".code ";
                $sql .= "SET process_status=1 ";
                $sql .= "WHERE legal_entities.full_name=" . $table . ".full_name ";
                $sql .= "AND legal_entities.short_name=" . $table . ".short_name ";
                $sql .= "AND legal_entities.location=" . $table . ".location ";
                $sql .= "AND legal_entities.ceo_name=" . $table . ".ceo_name ";
                $sql .= "AND legal_entities.activities=" . $table . ".activities ";
                $sql .= "AND legal_entities.status=" . $table . ".status ";
                $res = $connection->query($sql);


                $sql = "INSERT IGNORE INTO legal_history (code, full_name, short_name, location, ceo_name, activities, status, date) ";
                $sql .= "SELECT legal_entities.code, legal_entities.full_name, legal_entities.short_name, legal_entities.location, legal_entities.ceo_name, legal_entities.activities, legal_entities.status,'" . $previous['date'] . "' FROM legal_entities ";
                $sql .= "LEFT JOIN " . $table . " ON " . $table . ".code=legal_entities.code ";
                $sql .= "WHERE " . $table . ".`process_status` IS NULL ";
                $res = $connection->query($sql);


                $sql = "UPDATE legal_history LEFT JOIN " . $table . " ON " . $table . ".code=legal_history.code ";
                $sql .= "SET legal_history.`full_name`=" . $table . ".`full_name`, ";
                $sql .= "legal_history.`short_name`=" . $table . ".`short_name`, ";
                $sql .= "legal_history.`location`=" . $table . ".`location`, ";
                $sql .= "legal_history.`ceo_name`=" . $table . ".`ceo_name`, ";
                $sql .= "legal_history.`activities`=" . $table . ".`activities`, ";
                $sql .= "legal_history.`status`=" . $table . ".`status` ";
                $sql .= "WHERE " . $table . ".`process_status` IS NULL;";
//                    $res=$connection->query($sql);


            }


        }

    }



}
