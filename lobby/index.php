<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

//check for user in session
if (empty($_SESSION['user'])) {
    header ("Location: ../join/");
} else {
    require_once("header.php");
}

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    $user = $_SESSION['user'];

    //load the current game details
    if (!$game = $mySession->loadUsers($user['code'])) {
        //game was not found
    } else {
        //game was found
        ?>
        <div class="mdl-layout mdl-js-layout mdl-color--grey-100" style="justify-content: initial;">
            <div style="color: #6ab344;">
                <h2 style="float: left; text-transform: capitalize;"><?php echo str_replace("-", " ", $game['game_name']); ?></h2>
            </div>
            <div class="mdl-cell mdl-cell--5-col">
                <div id="players"></div>
            </div>
        </div>
        <?php
    }

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}
if(!empty($msg)) {
    ?>
    <dialog class="mdl-dialog error">
        <h4 class="mdl-dialog__title">Oops!</h4>
        <div class="mdl-dialog__content">
            <p style="color: #ccc; font-size: 8px;">You done did it.</p>
            <p><?php echo $msg; ?></p>
        </div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button close">OK</button>
        </div>
    </dialog>
    <?php
}

require_once("footer.php");
?>