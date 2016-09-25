<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");

require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/includes/database.php");

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP);

$thisUser = $_SESSION['user'];
$started = false;
$exists = false;
$forceReload = false;
$isHost = $user->isHost("get", $thisUser['id']);

if ($mySession->validateGame($thisUser['game_id'])) {

    $exists = true;

    //check if the game has started
    if ($mySession->isStarted()) {
        $started = true;
    }

    //check if this user is now host
    if (!$_SESSION['game']['isHost'] && $isHost) {
        //user got host
        $forceReload = true;
        $_SESSION['game']['isHost'] = true;

        //reset user session variables
        $_SESSION['user'] = $user->getUser();
    } else if ($_SESSION['game']['isHost'] && !$isHost) {
        //user lost host, force reload
        $forceReload = true;
        $_SESSION['game']['isHost'] = false;

        //reset user session variables
        $_SESSION['user'] = $user->getUser();
    } else if (!$isHost) {
        //reset other users sessions to not host
        $_SESSION['game']['isHost'] = false;
    }
}

//echo response
header('Content-Type: application/json');
echo json_encode(array("exists" => $exists, "started" => $started, "forceReload" => $forceReload));
