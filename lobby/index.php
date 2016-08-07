<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

//check for user in session
if (empty($_SESSION['user'])) {
    header ("Location: ../join/");
} else {
    require_once("header.php");
}

try {
    $thisUser = $_SESSION['user'];

    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);
    
    if($_REQUEST['leave'] == true) {
        if($mySession->leave()) {
            $code = $_SESSION['current_game_code'];
            unset($_SESSION['current_game_code']);
            header('Location: /join/?last-game-code=' . $code);
        }
        $msg = "You can't leave!";
    }

    //load the current game details
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        //game was not found
        $msg = "Sorry your game was deleted";
    } else {
        //game was found
        ?>
        <div class="mdl-layout mdl-js-layout mdl-color--grey-100" style="justify-content: initial;">
            <div class="icon_bar">
                <div class="mdl-cell mdl-cell--1-col left">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?leave=true" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"><i class="fa fa-times" style="position: relative; left: -5px; top: -1px;"></i> Leave</a>
                </div>
            </div>
            <div>
                <h2 style="color: #6ab344; float: left; font-size: 36px; text-transform: capitalize;"><?php echo str_replace("-", " ", $game['game_name']); ?></h2>
                <button class="mdl-button mdl-js-button mdl-button--icon" id="show-rules" style="float: left; margin: 34px 10px 10px 10px; color: #777;">
                    <i class="fa fa-question"></i>
                </button>
                <div class="mdl-tooltip" for="show-rules">Rules</div>
                <dialog class="mdl-dialog rules" style="width: 90%;">
                    <div class="mdl-dialog__content">
                        <?php require_once("../games/" . $game['game_name'] . "/rules.php"); ?>
                    </div>
                    <div class="mdl-dialog__actions">
                        <button type="button" class="mdl-button close">CLOSE</button>
                    </div>
                </dialog>
                <script>
                    (function() {
                        var rulesDialog = document.querySelector('dialog.rules');
                        if(rulesDialog != null) {
                            if (!rulesDialog.showModal) {
                                dialogPolyfill.registerDialog(rulesDialog);
                            }

                            document.querySelector('#show-rules').addEventListener('click', function() {
                                rulesDialog.showModal();
                            });

                            rulesDialog.querySelector('.close').addEventListener('click', function() {
                                rulesDialog.close();
                            });
                        }
                    })();
                </script>
            </div>
            <div class="mdl-cell mdl-cell--5-col">
                <div id="players"></div>
                <div id="chatMessages"></div>
                <form action="chat.php" id="chatMessageForm">
                    <div class="mdl-textfield mdl-js-textfield mdl-shadow--2dp messageArea">
                        <textarea class="mdl-textfield__input" type="text" rows="2" name="message" maxlength="500" id="messageText"></textarea>
<!--                        <label class="mdl-textfield__label" for="messageText">Enter a Message</label>-->
                        <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--primary send" id="sendMessage">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
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