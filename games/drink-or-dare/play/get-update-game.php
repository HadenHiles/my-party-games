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
    $gameState["userid"] = $dod->getUserId();

    /*
     * some use cases for state 1
     */
    if ($state == 1) {
        $gameState["waiting"] = $dod->getHasCurrentDare();
    }

    /*
     * some user cases for state 2
     */
    if ($state == 2) {
        $gameState["cardInfo"] = $dod->getCardsInfo();
    }

    /*
     * some use cases for state 3: using the cards
     */
    if ($state == 3) {
        //check to see if it's their turn
        $gameState["turn"] = $dod->getIsMyTurn();
        $gameState["allVotesCast"] = $dod->checkAllVotesCast();
        $gameState["activePlayer"] = $dod->getActivePlayer();
        $gameState["numPlayers"] = $dod->getNumPlayers();
        $gameState['hasPeeked'] = $dod->checkHasPeeked(true);
        $gameState["votes"] = $dod->getVotes();
        $gameState["freePasses"] = $dod->getMyFreePasses();

        //check to see if user has looked at their dare or not
        $gameState["dare"] = ($gameState['hasPeeked'] ? $dod->getDare(true, true) : "hidden");

        //check if all votes have been cast
        if ($gameState["allVotesCast"]) {
            //check if this is active user
            if ($gameState["userid"] == $gameState['activePlayer']['id']) {
                $gameState["status"] = $dod->finishCurrentDare($gameState['activePlayer']['id']);
                $score = $dod->getScore($gameState['activePlayer']['id']);
            }

            $gameState["drinksWorth"] = $dod->getDrinksWorth(true);
            $good = 0;
            $bad = 0;
            $skip = 0;

            //tally up votes
            foreach ($gameState["votes"] as $vote) {
                if ($vote["vote"] == 3) {
                    $good++;
                } else if ($vote["vote"] == 2) {
                    $skip++;
                } else if ($vote["vote"] == 1) {
                    $bad++;
                }
            }

            //find verdict based on tally
            if($bad > $good && $bad > $skip) {
                $verdict = "bad";
            } else if($skip >= $bad && $skip > $good) {
                $verdict = "skip";
            } else {
                $verdict = "good";
            }

            //check if this is active user and update score if so
            if ($gameState["userid"] == $gameState['activePlayer']['id'] && $verdict != "skip") {
                if ($verdict == "bad") {
                    $newScore = $score - $gameState["drinksWorth"];
                } else if ($verdict == "good") {
                    $newScore = $score + $gameState["drinksWorth"];
                }
                //update score if its been changed
                if ($score != $newScore) {
                    $dod->updateScore($gameState['activePlayer']['id'], $newScore);
                }
            }

            //add verdict to return array
            $gameState["verdict"] = $verdict;
        }
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

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $gameState["error"] = $msg;
}

//echo JSON data
echo json_encode($gameState);
?>