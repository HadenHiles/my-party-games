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

    $user_session = $_SESSION['user'];

    $message = $_REQUEST['message'];
    if(!empty($message)) {
        $mySession->addChatMessage($message, $user_session['userid']);
    } else {
        if(!$chatMessages = $mySession->loadChatMessages()) {
        } else {
            ?>
            <div class="chat">
                <?php
                foreach($chatMessages as $message) {
                    if(!empty($message['message'])) {
                        if($message['owner'] == $user_session['userid']) {
                            ?>
                            <div class="mdl-shadow--2dp message me"><pre><?php echo $message['message']; ?></pre></div>
                            <?php
                        } else {
                            ?>
                            <div class="mdl-shadow--2dp message"><pre><?php echo $message['message']; ?></pre></div>
                            <?php
                        }
                        ?>
                        <div class="clear"></div>
                        <?php
                    }
                }
                ?>
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