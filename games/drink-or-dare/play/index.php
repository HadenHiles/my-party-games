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
$dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);

require_once("header.php");
?>

<style>
#game-content {
    background:#eee;
}
</style>

<?php

try {
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        //game was not found
        $msg = "Sorry your game was deleted";
        echo $msg;
        exit();
    }
    var_dump($game);

    //get game state
    $state =  $dod->getState();

    echo "Playing as: " . $thisUser['name'];
    echo "<br />";
    echo "starting status: " . $dod->start();
    echo "<br />";
    echo "current state: " . $state;
    echo "<br />";
    echo "<br />";

    switch ($state) {

        case 1:
            //users are picking dares
            echo "state: users creating dares";
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

echo "<br />";
echo "<br />";
?>

<div id="game-content">
    <div id="game-stage-1" <?php echo ($state == 1 && !$dod->getHasCurrentDare() ? : 'style="display:none"'); ?>>
        <h1>enter a dare</h1>
        <form action="" method="post">
            <label>What's your dare?</label>
            <textarea id="dare-text"></textarea>
            <input type="button" id="set-dare" onclick="setDare();" value="Set Dare">
        </form>
    </div>
    <div id="game-stage-1-waiting" <?php echo ($dod->getHasCurrentDare() ? : 'style="display:none"'); ?>>
        <h1>Waiting for other players to enter dares..</h1>
    </div>
    <div id="game-stage-2" <?php echo ($state == 2 ? : 'style="display:none"'); ?>>
        <h1>choose a card</h1>
    </div>
    <div id="game-stage-3" <?php echo ($state == 3 ? : 'style="display:none"'); ?>>

    </div>
    <div id="game-stage-4" <?php echo ($state == 4 ? : 'style="display:none"'); ?>>

    </div>
    <div id="game-stage-5" <?php echo ($state == 5 ? : 'style="display:none"'); ?>>

    </div>
    <div id="game-stage-6" <?php echo ($state == 6 ? : 'style="display:none"'); ?>>

    </div>
</div>

<?php
require_once("footer.php");
?>

