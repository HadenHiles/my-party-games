<?php
ini_set("display_errors", 1);
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');

require_once(ROOT.'/includes/database.php');
require_once(ROOT.'/includes/class.GameSession.php');
require_once(ROOT.'/includes/class.User.php');
require_once(ROOT.'/games/drink-or-dare/class.DrinkOrDare.php');

$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP);

//check that the game is currently still alive
if (!$game = $mySession->loadUsers($thisUser['game_id'])) {
    $gameState["error"] = "Game could not be loaded";
}

$thisUser = $_SESSION['user'];
$code = $_SESSION['game']['code'];

$dod = new DrinkOrDare($code, $thisUser['id']);
$dod->start();

$activePlayer = $dod->getActivePlayer();

$dod->checkNextState($thisUser['is_host']);
?>
<h4 style="color: #fff"><?php echo $activePlayer['display_name']; ?>'s Turn</h4>