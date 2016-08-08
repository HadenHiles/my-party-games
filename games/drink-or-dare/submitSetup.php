<?php
require_once("../../includes/class.GameSession.php");
require_once("../../includes/class.User.php");
require_once("class.DrinkOrDare.php");
require_once("../../includes/common.php");
require_once("../../includes/database.php");

$game_field_values = $_SESSION['game_field_values'];
unset($_SESSION['game_field_values']);

$mysession = new GameSession(SESSION_ID, DEVICE_IP);
$drinkOrDare = new DrinkOrDare($_SESSION['user']['code'], $_SESSION['user']['userid'], $game_field_values['rounds'], 1, $game_field_values['drinks_to_win']);
$drinkOrDare->start();

header("location: ../../lobby/");