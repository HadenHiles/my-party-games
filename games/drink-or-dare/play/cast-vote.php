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

    //store the state in JSON return object
    $state = $dod->getState();

    $gameState["status"] = $dod->castVote($_POST['vote']);
    $gameState["allVotesCast"] = $dod->checkAllVotesCast();
    $gameState["votes"] = $dod->getVotes();
    
    if ($gameState["allVotesCast"]) {
        $good = 0;
        $bad = 0;
        $skip = 0;

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
        } else if($skip >= $bad && $skip > $good) {
            $verdict = "skip";
        } else {
            $verdict = "good";
        }

        $gameState["verdict"] = $verdict;
        $gameState["drinksWorth"] = $dod->getDrinksWorth(true);
    }

    $gameState["state"] = $state;

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

//echo JSON data
echo json_encode($gameState);
?>