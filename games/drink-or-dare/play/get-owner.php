<?php
require_once('../../../includes/common.php');
require_once('../../../includes/database.php');
require_once('../../../includes/class.GameSession.php');
require_once('../../../includes/class.User.php');
require_once('../class.DrinkOrDare.php');

$cardNum = $_REQUEST['card_num'];

//get user session information
$thisUser = $_SESSION['user'];

$dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
$owner = array();

try {
    $owner = $dod->getOwner(true, $cardNum);
    var_dump($owner);
} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $owner["error"] = $msg;
}

//echo JSON data
echo json_encode($owner);
?>