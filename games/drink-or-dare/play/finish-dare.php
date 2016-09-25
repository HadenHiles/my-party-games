<?php
require_once('../../../includes/common.php');
require_once('../../../includes/database.php');
require_once('../../../includes/class.GameSession.php');
require_once('../../../includes/class.User.php');
require_once('../class.DrinkOrDare.php');

//get user session information
$thisUser = $_SESSION['user'];

//init the new game session and user class
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);
$gameState = array();

//update and check the state of the current game
try {
    //check that the game is currently still alive
    if (!$game = $mySession->loadUsers($thisUser['code'], 0)) {
        $gameState["error"] = "Game could not be loaded";
    }

    //load the drink or dare class and get game values from database
    $dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
    $dod->start();

    //check for entered dares to be completed
    $dod->checkNextState();

    //store the state in JSON return object
    $state = $dod->getState();

    $gameState["status"] = $dod->finishCurrentDare();

    if ($gameState["status"]) {
        $gameState["votes"] = $dod->getVotes();

        $good = 0;
        $bad = 0;
        $skip = 0;
        $count = count($gameState["votes"]);

        foreach ($gameState["votes"] as $vote) {

            if ($vote["vote"] == 3) {
                $good++;
            } else if ($vote["vote"] == 2) {
                $skip++;
            } else if ($vote["vote"] == 1) {
                $bad++;
            }
        }

        if($bad > $good && $bad > $skip) {
            $verdict = "bad";
            $newScore = $dod->getScore($thisUser['id']) - $dod->getDrinksWorth(true);
            $dod->updateScore($thisUser['id'], $newScore);
        } else if($skip >= $bad && $skip > $good) {
            $verdict = "skip";
        } else {
            $verdict = "good";
            $newScore = $dod->getScore($thisUser['id']) + $dod->getDrinksWorth(true);
            $dod->updateScore($thisUser['id'], $newScore);
        }
    }

    $gameState["verdict"] = $verdict;
    $gameState["state"] = $state;
    $gameState["drinksWorth"] = $dod->getDrinksWorth(true);

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

//echo JSON data
echo json_encode($gameState);
?>