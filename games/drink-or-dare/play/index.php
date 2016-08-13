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

$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);

//$dod->getDrinkOrDare();

require_once("header.php");

try {
    if (!$game = $mySession->loadUsers($thisUser['code'], 0)) {
        //game was not found
        $msg[] = array("msg" => "game-not-found", "popup" => "dialog");
        exit();
    }

    //init the new game session
    $dod = new DrinkOrDare($thisUser['code'], $thisUser['userid']);
    $dod->start();

    //get game state
    $state =  $dod->getState();

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    $msg[] = array("msg" => "game-not-found", "popup" => "dialog");
}
?>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header" id="game-content">
    <header class="mdl-layout__header mdl-layout__header--transparent">
        <div class="mdl-layout__header-row">
            <!-- Add spacer, to align navigation to the right -->
            <div class="mdl-layout-spacer"></div>
            <!-- Navigation -->
            <nav class="mdl-navigation">
                <h6 style="margin: 0 5px;"><?php echo $thisUser['code']; ?></h6>
                <button id="settings" class="mdl-button mdl-js-button mdl-button--icon">
                    <i class="fa fa-cog fade"></i>
                </button>

                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="settings">
                    <li class="mdl-menu__item" id="leave-game" onclick="window.location.href = '../../../lobby/leave.php';">Leave Game</li>
                    <?php
                    if($user->isHost("get", $thisUser['userid'])) {
                        ?>
                        <form action="../../../lobby/" method="post" id="delete-game-form">
                            <input type="hidden" name="delete-game" value="true" />
                        </form>
                        <li class="mdl-menu__item" id="delete-game" style="color: #CE0000" onclick="if(confirm('Are you sure you want to stop the game?')){$('#delete-game-form').submit();}">Stop Game</li>
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

        <!-- Stage 1 -->
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
                <p>** turn this select into a slider ** <br />How many drinks is this dare worth?</p>
                <select id="drinksWorth">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <button class="mdl-button mdl-js-button mdl-js-ripple-effect" onclick="setDare();" style="width: 100%;">Done</button>
            </div>
        </div>
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 1 && $dod->getHasCurrentDare() ? : 'style="display:none"'); ?> id="game-stage-1-waiting">
            <div class="mdl-card__supporting-text">
                <p>Waiting for other players to enter dares..</p>
            </div>
        </div>

        <!-- Stage 2 -->
        <div class="mdl-cell mdl-cell--8-col dares center" id="game-stage-2" <?php echo ($state == 2 ? : 'style="display:none"'); ?>>
            <h4 style="color: #fff; margin: -50px 0 10px 0;">Pick a Dare!</h4>
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

        <!-- Stage 3 -->
        <div class="mdl-cell mdl-cell--3-col mdl-cell--6-col-tablet mdl-cell--8-col-phone center" <?php echo ($state == 3 ? : 'style="display:none"'); ?> style="min-width: 300px;" id="game-stage-3">

            <div <?php echo ($dod->getIsMyTurn() ? : 'style="display:none"'); ?> id="game-stage-3-player">
                <div class="mdl-card mdl-shadow--6dp square dare full-width paper showCard" id="myCard">
                    <?php
                    echo $dod->checkHasPeeked() ? $dod->getDare() : "hidden";
                    ?>
                </div>
                <div class="mdl-cell mdl-cell--12-col actions center">
                    <button id="only-skip" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--primary mdl-button--colored left" onclick="freePass();">
                        <i class="fa fa-fast-forward"></i>
                    </button>
                    <div class="mdl-tooltip mdl-tooltip--large" for="only-skip">
                        Use a Free Pass
                    </div>
                    <button id="done-dare" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--green mdl-button--colored right" onclick="finishDare();">
                        <i class="fa fa-check"></i>
                    </button>
                    <div class="mdl-tooltip mdl-tooltip--large" for="done-dare">
                        I'm done the dare!
                    </div>
                </div>
            </div>

            <div <?php echo (!$dod->getIsMyTurn() ? : 'style="display:none"'); ?> id="game-stage-3-viewer">
                <div class="mdl-card mdl-shadow--6dp square dare full-width paper showCard" id="activeDare">
                    <?php
                    echo $dod->checkHasPeeked(true) ? $dod->getDare(true) : "hidden";
                    ?>
                </div>
                <div class="mdl-cell mdl-cell--12-col actions center">
                    <button id="drink" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--red mdl-button--colored left" onclick="castVote(1);">
                        <i class="fa fa-remove"></i>
                    </button>
                    <div class="mdl-tooltip mdl-tooltip--large" for="drink">
                        Dare execution not worthy!
                    </div>
                    <button id="free-skip" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--primary mdl-button--colored middle" onclick="castVote(2);">
                        <i class="fa fa-fast-forward"></i>
                    </button>
                    <div class="mdl-tooltip mdl-tooltip--large" for="free-skip">
                        That dare is unreasonable.
                    </div>
                    <button id="give-drink" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--green mdl-button--colored right" onclick="castVote(3);">
                        <i class="fa fa-check"></i>
                    </button>
                    <div class="mdl-tooltip mdl-tooltip--large" for="give-drink">
                        Well done Jackson!
                    </div>
                </div>
            </div>

            <div id="votes" style="color:white;font-size:16px">
                <div id="voted-bad"></div>
                <div id="voted-skip"></div>
                <div id="voted-good"></div>
            </div>
        </div>

        <!-- Stage 4 -->
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 4 ? : 'style="display:none"'); ?> id="game-stage-4">
            <div class="mdl-card__supporting-text">
            </div>
        </div>

        <!-- Stage 5 -->
        <div class="mdl-card mdl-shadow--6dp center" <?php echo ($state == 5 ? : 'style="display:none"'); ?> id="game-stage-5">
            <div class="mdl-card__supporting-text">
                Game Over
                <input type="button" onclick="restartGame();" value="Restart Game">
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

