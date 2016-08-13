<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

$thisUser = $_SESSION['user'];

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);

global $db;

$sql = 'SELECT * FROM drink_or_dare WHERE game_id = :gameid';

$result = $db->prepare($sql);
$result->bindValue(":gameid", $thisUser['code']);

$started = false;

if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
    $started = true;
}

$result = array("getGame" => false, "started" => $started);
if($mySession->getGame($thisUser['code'])) {
    $result["getGame"] = true;
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    $result["getGame"] = false;
    header('Content-Type: application/json');
    echo json_encode($result);
}
