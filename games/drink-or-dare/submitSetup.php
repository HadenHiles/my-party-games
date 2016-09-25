<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");

require_once(ROOT."/includes/database.php");
require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/games/drink-or-or-dare/class.DrinkOrDare.php");

//THIS SCRIPT IS NO LONGER NEEDED -- justin
exit();

try {
    //get game field values from the session
    $game_field_values = $_SESSION['game_field_values'];
    unset($_SESSION['game_field_values']);

    $thisUser = $_SESSION['user'];

    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);
    $drinkOrDare = new DrinkOrDare($thisUser['game_id'], $thisUser['id'], $game_field_values['rounds'], 1, $game_field_values['drinks_to_win']);

    if (!$drinkOrDare->isStarted($thisUser['game_id'])) {
        //game doesn't exist so we can start game here and it will be created with our init variables
        $drinkOrDare->start(false);
    } else {
        //game already exists so we need to update it with new variables
        $drinkOrDare->update($thisUser['game_id'], 1, $game_field_values['rounds'], 1, $game_field_values['drinks_to_win']);
    }

} catch (Exception $e) {
    echo $e->getMessage();
}

header("location: ../../lobby/");
exit();