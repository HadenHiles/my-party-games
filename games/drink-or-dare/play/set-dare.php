<?php
require_once('../../../includes/common.php');
require_once('../../../includes/database.php');
require_once('../../../includes/class.GameSession.php');
require_once('../../../includes/class.User.php');
require_once('../class.DrinkOrDare.php');

$thisUser = $_SESSION['user'];

//init the new game session
$dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);
$gameState = array();

//update and check the state of the current game
try {
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        exit();
    }

    $gameState["status"] = $dod->setDare($_POST['text']);
    $gameState["state"] = $dod->getState();

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

echo json_encode($gameState);
?>