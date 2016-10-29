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
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['display_name']);

$gameState = array();

//update and check the state of the current game
try {
    //check that the game is currently still alive
    if (!$game = $mySession->loadUsers($thisUser['game_id'])) {
        $gameState["error"] = "Game could not be loaded";
    }

    //load the drink or dare class and get game values from database
    $dod = new DrinkOrDare($thisUser['game_id'], $thisUser['id']);
    $dod->start();

    //check for entered dares to be completed
    $dod->checkNextState($thisUser['is_host']);

    //store the state in JSON return object
    $state = $dod->getState();


    /*
     * some use cases for state 1
     */
    if ($state == 1) {
        $gameState["waiting"] = $dod->getHasCurrentDare();
    }

    if ($state == 2) {
        $gameState["cardInfo"] = $dod->getCardsInfo();
    }

    /*
     * some use cases for state 3: using the cards
     */
    if ($state == 3) {
        //check to see if user has looked at their dare or not
        if ($dod->getIsMyTurn() && $dod->checkHasPeeked()) {
            //this users turn and they have looked
            $gameState["dare"] = $dod->getDare(true, true);
        } else if ($dod->checkHasPeeked(true)) {
            //not current users turn and they have looked
            $gameState["dare"] = $dod->getDare(true, true);
        } else {
            //active player 
            $gameState["dare"] = "hidden";
        }

        //check to see if it's their turn
        $gameState["turn"] = $dod->getIsMyTurn();
        $gameState["votes"] = $dod->getVotes();
        $gameState["allVotesCast"] = $dod->checkAllVotesCast();
        $gameState["activePlayer"] = $dod->getActivePlayer();
        $gameState["numPlayers"] = $dod->getNumPlayers();
        //$gameState["dare"] = $dod->getDare(true, true);
        $gameState['hasPeeked'] = $dod->checkHasPeeked(true);
    }

    //some use cases for state 4: incrementing round / checking if game completed
    if ($state == 4) {
//        $dod->checkNextRound($thisUser['is_host']);
    }

    //some use cases for state 5
    if ($state == 5) {
        $gameState["endResults"] = $dod->getEndResults();
    }

    //variables always used in the javascript
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