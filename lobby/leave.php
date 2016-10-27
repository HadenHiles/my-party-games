<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");

require_once(ROOT."/includes/database.php");
require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/games/drink-or-dare/class.DrinkOrDare.php");

$thisUser = $_SESSION['user'];
$code = $_SESSION['game']['code'];

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP);

if($user->isHost("get", $thisUser['id'])) {
    $dod = new DrinkOrDare($code, $thisUser['id']);

    $mySession->destroy($code);
    $dod->destroy($code);
}

if($mySession->leave()) {
    $_SESSION['user'] = $user->getUser();
    header('Location: /join/?unique-id=' . $code);
    exit();
}