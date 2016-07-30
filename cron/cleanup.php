<?php
/**
 * Created by justin to be run in a cron job that will clean up the database everynight
 */
require_once('../includes/common.php');
require_once('../includes/database.php');

define("DELETE_THRESHOLD", 2);

//delete games that are more then x number of days old OR simply put: un-active
$date = time() - (DELETE_THRESHOLD * 24 * 60 * 60); // days; hours; mins; secs

$dt = new DateTime();
$dt->setTimestamp($date); //<--- Pass a UNIX TimeStamp
$date = $dt->format('Y-m-d');

echo 'DELETE THRESHOLD = ' . DELETE_THRESHOLD . ' days ago<br />deleteing all connections, un-verified users and messages < ' . $date . '<br /><br />';

//delete game connections
$sql = 'DELETE FROM game_connections WHERE date < DATE(:date)';

$result = $db->prepare($sql);
$result->bindValue(":date", $date);

if ($result->execute() && $result->errorCode() == 0) {
    echo 'deleted ' . $result->rowCount() . ' game_connections <Br />';
}

//delete messages
$sql = 'DELETE FROM messages WHERE DATE(time) < DATE(:date)';

$result = $db->prepare($sql);
$result->bindValue(":date", $date);

if ($result->execute() && $result->errorCode() == 0) {
    echo 'deleted ' . $result->rowCount() . ' messages <br />';
}

//delete users
$sql = 'DELETE FROM users WHERE last_active_date < DATE(:date) AND verified_user = 0';

$result = $db->prepare($sql);
$result->bindValue(":date", $date);

if ($result->execute() && $result->errorCode() == 0) {
    echo 'deleted ' . $result->rowCount() . ' users <br />';
}

?>