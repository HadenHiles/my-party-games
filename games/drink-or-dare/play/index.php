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
//var_dump($thisUser);

//init the new game session
$dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
$dod->start();

$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);

//$dod->getDrinkOrDare();

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
        $msg[] = array("msg" => "game-not-found", "popup" => "dialog");
        exit();
    }
//    var_dump($game);

    //get game state
    $state =  $dod->getState();
    echo $state . '<Br />';
   // var_dump($dod->getHasCurrentDare());
//    echo "Playing as: " . $thisUser['name'];
//    echo "<br />";
//    echo "starting status: " . $dod->start();
//    echo "<br />";
//    echo "current state: " . $state;
//    echo "<br />";
//    echo "<br />";

    switch ($state) {

        case 1:
            //users are picking dares
            //echo "state: users creating dares";
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
<div class="mdl-layout mdl-js-layout mdl-color--grey-100" id="game-content">
    <main class="mdl-layout__content main-form">
        <div style="color: #cccccc;">
            <a href="/" class="party-games-title" style="color: #ccc;"><h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4></a>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 1 && !$dod->getHasCurrentDare() ? : 'style="display:none"'); ?> id="game-stage-1">
            <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                <h2 class="mdl-card__title-text">Write Your Dare</h2>
            </div>
            <div class="mdl-card__supporting-text select-avatar">
                <form action="" method="post">
                    <div class="mdl-textfield mdl-js-textfield">
                        <textarea class="mdl-textfield__input" type="text" rows= "3" id="dare-text"></textarea>
                        <label class="mdl-textfield__label" for="dare-text">Dare</label>
                    </div>
                </form>
            </div>
            <div class="mdl-card__actions" style="text-align: center; margin-top: -25px;">
                <button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" onclick="setDare();" style="width: 100%;">Done</button>
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($dod->getHasCurrentDare() ? : 'style="display:none"'); ?> id="game-stage-1-waiting">
            <div class="mdl-card__supporting-text select-avatar">
                <p>Waiting for other players to enter dares..</p>
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 2 ? : 'style="display:none"'); ?> id="game-stage-2">
            <div class="mdl-card__supporting-text select-avatar">
                <h1>Choose a Card</h1>
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 3 ? : 'style="display:none"'); ?> id="game-stage-3">
            <div class="mdl-card__supporting-text select-avatar">
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 4 ? : 'style="display:none"'); ?> id="game-stage-4">
            <div class="mdl-card__supporting-text select-avatar">
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 5 ? : 'style="display:none"'); ?> id="game-stage-5">
            <div class="mdl-card__supporting-text select-avatar">
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp" <?php echo ($state == 6 ? : 'style="display:none"'); ?> id="game-stage-6">
            <div class="mdl-card__supporting-text select-avatar">
            </div>
        </div>
    </main>
</div>

<?php
require_once("footer.php");
?>

