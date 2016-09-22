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
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        $gameState["error"] = "Game could not be loaded";
    }

    //load the drink or dare class and get game values from database
    $dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
    $dod->start();

    //check for entered dares to be completed
    $dod->checkNextState();

    //store the state in JSON return object
    $state = $dod->getState();


    //some use cases for state 1
    if ($state == 1) {
        $gameState["waiting"] = $dod->getHasCurrentDare();
    }

    if ($state == 2) {
        $gameState["cardInfo"] = $dod->getCardsInfo();
    }

    //some use cases for state 3: using the cards
    if ($state == 3) {
        //check to see if user has looked at their dare or not
        if ($dod->getIsMyTurn() && $dod->checkHasPeeked()) {

            $gameState["dare"] = $dod->getDare();

        } else if ($dod->checkHasPeeked(true)) {

            $gameState["dare"] = $dod->getDare(true);

        } else {

            $gameState["dare"] = "hidden";
        }

        //check to see if it's their turn
        $gameState["turn"] = $dod->getIsMyTurn();
        $gameState["votes"] = $dod->getVotes();
        $gameState["allVotesCast"] = $dod->checkAllVotesCast();
        $gameState["activePlayer"] = $dod->getActivePlayer();
        $gameState["numPlayers"] = $dod->getNumPlayers();
    }

    //some use cases for state 4: incrementing round / checking if game completed
    if ($state == 4) {

    }

    //some use cases for state 5
    if ($state == 5) {

        $gameState["endResults"] = $dod->getEndResults();
    }

    $gameState["state"] = $state;
    $gameState["totalRounds"] = $dod->getTotalRounds();
    $gameState["currentRound"] = $dod->getCurrentRound();
    $gameState["userid"] = $dod->getUserId();

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

//echo JSON data
echo json_encode($gameState);
?>