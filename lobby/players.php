<?php
/**
 * Created by handshiles on 2016-07-12.
 */
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");

require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/includes/database.php");

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);

    $user_session = $_SESSION['user'];

    //load the current game details
    if (!$game = $mySession->loadUsers($user_session['game_id'], 0)) {
        /*
         * game was not found
         */
    } else {
        //game was found
        $displayCount = 0;

        //loop through game players
        foreach($game['users'] as $gameUser) {

            //check if current iteration is a display
            if(!$gameUser['is_display']) {

                //check to see if current iteration = current player
                if($gameUser['id'] == $user_session['id']) {
                    ?>
                    <div class="mdl-card mdl-shadow--16dp player me">
                    <?php
                } else {
                    ?>
                    <div class="mdl-card mdl-shadow--6dp player">
                    <?php
                }
                ?>

                    <div class="mdl-card__supporting-text">
                        <img src="<?php echo $gameUser['picture']; ?>" border="0" alt="" />
                        <h5>
                            <?php
                            echo $gameUser['display_name'];

                            if ($user->isHost("get", $gameUser['id'])) {
                                echo ' - HOST';
                            }
                            ?>
                        </h5>
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
?>