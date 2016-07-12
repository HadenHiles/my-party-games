<?php
/**
 * Created by handshiles on 2016-07-12.
 */
require_once("../includes/class.GameSession.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    $user = $_SESSION['user'];

    //load the current game details
    if (!$game = $mySession->loadUsers($user['code'])) {
        //game was not found
    } else {
        //game was found
        foreach($game['users'] as $user) {
            ?>
            <div class="mdl-card mdl-shadow--6dp player">
                <div class="mdl-card__supporting-text">
                    <img src="http://graph.facebook.com/<?php echo $user['fb_user_id']; ?>/picture?type=large" border="0" alt="" />
                    <h5><?php echo $user['display_name']; ?></h5>
                </div>
            </div>
            <?php
        }
    }

} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}
if(!empty($msg)) {
    ?>
    <dialog class="mdl-dialog">
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
?>