<?php
require_once("../../includes/class.GameSession.php");
require_once("../../includes/class.User.php");
require_once("../../includes/common.php");
require_once("../../includes/database.php");

$game_field_values = $_SESSION['game_field_values'];
unset($_SESSION['game_field_values']);

header("location: ../../lobby/");