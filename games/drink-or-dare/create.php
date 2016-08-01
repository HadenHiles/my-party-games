<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/class.GameSession.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/class.User.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/header/header.php');

$game = 'drink-or-dare';

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);
    
    $mySession->setup($game);

    //check for form submission to join a game session
    if (isset($_POST['start-game'])) {
        $displayOnly = (isset($_POST['display']));

        if ($displayOnly) {
            header("Location: /lobby/?display=true");
        } else {
            header("Location: /join/?unique-id=".$mySession->getCode(SESSION_ID));
        }
    } else if (isset($_POST['new-code'])) {

        //requested to make a new code for the current session
        $mySession->newCode();
        header("Location: create.php");
    } else if (isset($_POST['new-session'])) {

        //requested to make a new session
        session_regenerate_id();
        $mySession->removeSession(SESSION_ID);
        header("Location: create.php");
    }

    //get the current game code
    $code = $mySession->getCode(SESSION_ID);

} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

//var_dump($code, SESSION_ID);

$pageTitle = "Drink Or Dare";

require_once("../header.php");
?>
<div class="layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">
    <header class="header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
        <div class="mdl-layout__header-row">
            <!-- Icon button -->
            <button class="mdl-button mdl-js-button mdl-button--icon" onclick="window.history.back(); return false;">
                <i class="fa fa-arrow-left"></i>
            </button>
            <div class="mdl-layout-spacer"></div>
        </div>
    </header>
    <div class="ribbon"></div>
    <main class="main mdl-layout__content">
        <div class="container mdl-grid">
            <div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
            <div class="content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col">
                <h2>Drink Or Dare</h2>
                <h4>Game code: <?php echo $code; ?></h4>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <button type="submit" id="start-game" name="start-game" class="off-screen">Start</button>

                    <button type="submit" name="new-code" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">New Code</button>
                    <a href="" onclick="$('button#start-game').trigger('click'); return false;" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-color--primary mdl-color-text--primary-contrast right">Continue</a>
                    <div class="right" style="margin-top: 6px;">
                        <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="display">
                            <input type="checkbox" id="display" name="display" class="mdl-checkbox__input">
                            <span class="mdl-checkbox__label">I am a display</span>
                        </label>
                    </div>
<!--                    <button type="submit" name="new-session">New Session</button>-->
                </form>
            </div>
        </div>
    </main>
</div>
<?php
require_once("../footer.php");
