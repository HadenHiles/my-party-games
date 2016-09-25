<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');
require_once(ROOT.'/includes/database.php');
require_once(ROOT.'/includes/class.GameSession.php');
require_once(ROOT.'/includes/class.User.php');
require_once(ROOT.'/games/drink-or-dare/class.DrinkOrDare.php');

$cardNum = $_REQUEST['card_num'];

//get user session information
$thisUser = $_SESSION['user'];

//init the new game session and user class
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);
$owner = array();

//update and check the state of the current game
try {
    //check that the game is currently still alive
    if (!$game = $mySession->loadUsers($thisUser['game_id'], 0)) {
        $owner["error"] = "Game could not be loaded";
    } else {
        //load the drink or dare class and get game values from database
        $dod = new DrinkOrDare($thisUser['game_id'], $thisUser['id']);
        $dod->start();

        $owner = $dod->getOwner(true, $cardNum);
    }
} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $owner["error"] = $msg;
}

//echo JSON data
echo json_encode($owner);
?>