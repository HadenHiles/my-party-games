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

try {
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        //game was not found
        $msg[] = array("msg" => "game-not-found", "popup" => "dialog");
        exit();
    }
//    var_dump($game);

    //get game state
    $state =  $dod->getState();

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
    $msg[] = array("msg" => "game-not-found", "popup" => "dialog");
}
?>
<div class="mdl-layout mdl-js-layout" id="game-content">
    <header class="mdl-layout__header mdl-layout__header--transparent">
        <div class="mdl-layout__header-row">
            <!-- Add spacer, to align navigation to the right -->
            <div class="mdl-layout-spacer"></div>
            <!-- Navigation -->
            <nav class="mdl-navigation">
                <h6 style="margin: 0 5px;"><?php echo $mySession->getCode(SESSION_ID); ?></h6>
                <button id="settings" class="mdl-button mdl-js-button mdl-button--icon">
                    <i class="fa fa-cog fade"></i>
                </button>

                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="settings">
                    <li class="mdl-menu__item" id="leave-game" onclick="window.location.href = '../../../lobby/leave.php';">Leave Game</li>
                    <?php
                    if($user->isHost("get", $thisUser['userid'])) {
                        ?>
                        <li class="mdl-menu__item" id="delete-game" style="color: #CE0000" onclick="if(confirm('Are you sure you want to stop the game?')){window.location.href = '../stop.php'}">Stop Game</li>
                        <?php
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </header>
    <?php
    require_once("../../../leaderboard/leaderboard.php");
    ?>
    <main class="mdl-layout__content">
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 1 && !$dod->getHasCurrentDare() ? : 'style="display:none"'); ?> id="game-stage-1">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text">What's Your Dare?</h2>
            </div>
            <div class="mdl-card__supporting-text">
                <form action="" method="post">
                    <div class="mdl-textfield mdl-js-textfield">
                        <textarea class="mdl-textfield__input" type="text" rows= "6" id="dare-text"></textarea>
                        <label class="mdl-textfield__label" for="dare-text">Enter it here..</label>
                    </div>
                </form>
            </div>
            <div class="mdl-card__actions" style="text-align: center; margin-top: -25px;">
                <button class="mdl-button mdl-js-button mdl-js-ripple-effect" onclick="setDare();" style="width: 100%;">Done</button>
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 1 && $dod->getHasCurrentDare() ? : 'style="display:none"'); ?> id="game-stage-1-waiting">
            <div class="mdl-card__supporting-text">
                <p>Waiting for other players to enter dares..</p>
            </div>
        </div>
        <div class="mdl-cell mdl-cell--8-col dares center" id="game-stage-2" <?php echo ($state == 2 ? : 'style="display:none"'); ?>>
            <?php
            foreach($game['users'] as $key => $user) {
                $key = $key + 1;
                ?>
                <div class="mdl-card mdl-shadow--6dp square paper dare pickCard" data-cardnum="<?php echo $key; ?>">
                </div>
                <?php
            }
            ?>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 3 ? : 'style="display:none"'); ?> id="game-stage-3">
            <?php
            if ($state == 3) {
                //show players screen because it's their turn
                if ($dod->getWhoseTurn()) {

                    echo '<div class="mdl-card mdl-shadow--6dp square paper dare showCard" id="myCard">';

                        echo ($dod->checkHasPeeked() ? $dod->getDare() : "hidden");

                    echo '</div>';
                    echo '<div>';
                        echo '<input type="button" value="Skip">';
                        echo '<input type="button" disabled value="Done">';
                    echo '</div>';

                } else {
                    //show waiters screen because its not their turn
                    echo '<div class="mdl-card mdl-shadow--6dp square paper dare showCard" id="myCard">';

                        echo ($dod->checkHasPeeked(true) ? $dod->getDare(true) : "hidden");

                    echo '</div>';
                    echo '<div>';
                        echo '<input type="button" value="Drink">';
                        echo '<input type="button" value="Skip">';
                        echo '<input type="button" disabled value="Give Drinks">';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 4 ? : 'style="display:none"'); ?> id="game-stage-4">
            <div class="mdl-card__supporting-text">
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 5 ? : 'style="display:none"'); ?> id="game-stage-5">
            <div class="mdl-card__supporting-text">
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 6 ? : 'style="display:none"'); ?> id="game-stage-6">
            <div class="mdl-card__supporting-text">
            </div>
        </div>
    </main>
</div>

<?php
require_once("footer.php");
?>

