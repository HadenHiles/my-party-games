<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

$thisUser = $_SESSION['user'];

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);

$result = array("getGame" => false);
if($mySession->getGame($thisUser['code'])) {
    $result["getGame"] = true;
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    $result["getGame"] = false;
    header('Content-Type: application/json');
    echo json_encode($result);
}
