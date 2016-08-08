<?php
require_once("../../includes/class.GameSession.php");
require_once("../../includes/class.User.php");
require_once("class.DrinkOrDare.php");
require_once("../../includes/common.php");
require_once("../../includes/database.php");

$game_field_values = $_SESSION['game_field_values'];

unset($_SESSION['game_field_values']);

$mysession = new GameSession(SESSION_ID, DEVICE_IP);
$drinkOrDare = new DrinkOrDare(intval($_SESSION['user']['code']), intval($_SESSION['user']['userid']), intval($game_field_values['rounds']), 1, intval($game_field_values['drinks_to_win']));
if(!$drinkOrDare->isStarted(intval($_SESSION['user']['code']))) {
    $drinkOrDare->start();
} else {
    $drinkOrDare->update(intval($_SESSION['user']['code']), 1, intval($game_field_values['rounds']), 1, intval($game_field_values['drinks_to_win']));
}

header("location: ../../lobby/");