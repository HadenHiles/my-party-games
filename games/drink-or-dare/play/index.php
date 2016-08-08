<?php
require_once('../../../includes/common.php');
require_once('../../../includes/database.php');
require_once('../../../includes/class.GameSession.php');
require_once('../../../includes/class.User.php');
require_once('../class.DrinkOrDare.php');

//check for user in session
if (empty($_SESSION['user'])) {
    header("Location: /join/");
    exit();
}

$pageTitle = 'Playing Drink Or Dare';
$thisUser = $_SESSION['user'];
var_dump($thisUser);

//init the new game session
$dod = new DrinkOrDare($thisUser['code']);
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);

require_once("../../header.php");

try {
    echo "Playing as: " . $thisUser['name'];
    echo "<br />";
    echo "starting status: " . $dod->start();
    echo "<br />";
    echo "current state: " . $dod->getState();

    switch ($dod->getState()) {

        case 1:
            //users are picking dares

            break;

        case 2:
            //shuffling and assigning dares

            break;

        case 3:
            //looping users carrying out dares

            break;

        case 4:
            //incrementing rounds and check for game completion

            break;

        case 5:
            //game completed show stats

            break;

        case 6:
            //special case -- veto dare

            break;

        case 6:
            //special case -- free pass

            break;
    }
} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    echo $msg;
}
?>

<!-- HTML -->

<?php
require_once("../../footer.php");
?>


