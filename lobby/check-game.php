<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");

require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/includes/database.php");

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP);

$thisUser = $_SESSION['user'];
$exists = false;
$forceReload = false;
$isHost = $user->isHost("get", $thisUser['id']);
$isDisplay = $user->isDisplay("get", $thisUser['id']);

if ($mySession->validateGame($thisUser['game_id'])) {

    $exists = true;

    //check if the game has started
    if ($mySession->isStarted($thisUser['game_id'])) {
        $forceReload = true;
    }

    //check if this user is now host
    if (!$_SESSION['game']['isHost'] && $isHost) {
        //user got host
        $_SESSION['game']['isHost'] = true;

        //reset user session variables and reload
        $_SESSION['user'] = $user->getUser();
        $forceReload = true;
    } else if ($_SESSION['game']['isHost'] && !$isHost) {
        //user lost host, force reload
        $_SESSION['game']['isHost'] = false;

        //reset user session variables and reload
        $_SESSION['user'] = $user->getUser();
        $forceReload = true;
    } else if (!$isHost) {
        //reset other users sessions to not host
        $_SESSION['game']['isHost'] = false;
    }

    //check for correct display privleges
    if (!$_SESSION['game']['isDisplay'] && $isDisplay) {
        //user got host
        $_SESSION['game']['isDisplay'] = true;
        //reset user session variables and reload
        $_SESSION['user'] = $user->getUser();
        $forceReload = true;
    } else if ($_SESSION['game']['isDisplay'] && !$isDisplay) {
        //user lost host, force reload
        $_SESSION['game']['isDisplay'] = false;
        //reset user session variables and reload
        $_SESSION['user'] = $user->getUser();
        $forceReload = true;
    } else if (!$isDisplay) {
        //reset other users sessions to not host
        $_SESSION['game']['isDisplay'] = false;
    }
}

//echo response
header('Content-Type: application/json');
echo json_encode(array("exists" => $exists, "forceReload" => $forceReload));
