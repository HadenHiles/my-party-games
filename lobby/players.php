<?php
/**
 * Created by handshiles on 2016-07-12.
 */
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);

    $user_session = $_SESSION['user'];

    //load the current game details
    if (!$game = $mySession->loadUsers($user_session['code'], 0)) {
        //game was not found
    } else {
        //game was found
        $displayCount = 0;
        foreach($game['users'] as $user) {
            if(!$user['is_display']) {
                if($user['id'] == $user_session['userid']) {
                    ?>
                    <div class="mdl-card mdl-shadow--6dp player me">
                    <?php
                } else {
                    ?>
                    <div class="mdl-card mdl-shadow--6dp player">
                    <?php
                }
                ?>
                    <div class="mdl-card__supporting-text">
                        <img src="<?php echo $user['picture']; ?>" border="0" alt="" />
                        <h5><?php echo $user['display_name']; ?></h5>
                    </div>
                </div>
                <?php
            } else {
                $displayCount++;
            }
        }
        if(count($game['users']) == $displayCount) {
            ?>
            <p class="fade">Waiting for players...</p>
            <?php
        }
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
?>