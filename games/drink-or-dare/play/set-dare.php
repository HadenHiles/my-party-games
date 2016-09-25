<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');

require_once(ROOT.'/includes/database.php');
require_once(ROOT.'/includes/class.GameSession.php');
require_once(ROOT.'/includes/class.User.php');
require_once(ROOT.'/games/drink-or-dare/class.DrinkOrDare.php');

//get user session information
$thisUser = $_SESSION['user'];

//init the new game session and user class
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);
$gameState = array();

//update and check the state of the current game
try {
    //check that the game is currently still alive
    if (!$game = $mySession->loadUsers($thisUser['game_id'], 0)) {
        $gameState["error"] = "Game could not be loaded";
    }

    //load the drink or dare class and get game values from database
    $dod = new DrinkOrDare($thisUser['game_id'], $thisUser['id']);
    $dod->start();

    $gameState["status"] = $dod->setDare($_POST['text'], $_POST['drinksWorth']);
    $gameState["state"] = $dod->getState();

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

//echo JSON data
echo json_encode($gameState);
?>