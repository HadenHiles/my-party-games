<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

$thisUser = $_SESSION['user'];

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name'], $thisUser['code']);

if($user->isHost("get", $thisUser['userid'])) {
    $mySession->destroy($thisUser['code']);
}
if($mySession->leave()) {
    $code = $_SESSION['current_game_code'];
    unset($_SESSION['current_game_code']);
    header('Location: /join/?last-game-code=' . $code);
}