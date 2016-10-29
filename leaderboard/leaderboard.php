<?php
ini_set("display_errors",1);
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');

require_once(ROOT.'/includes/database.php');
require_once(ROOT.'/includes/class.GameSession.php');
require_once(ROOT.'/includes/class.User.php');
require_once(ROOT.'/games/drink-or-dare/class.DrinkOrDare.php');

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);

    $user_session = $_SESSION['user'];

    //load the current game details
    if (!$game = $mySession->loadUsers($user_session['game_id'], 1)) {
        //game was not found
    } else {
        //game was found
        $displayCount = 0;
        $place = 0;
        foreach($game['users'] as $usr) {
            $place++;
            if(!$usr['is_display']) {
                if($usr['id'] == $user_session['id']) {
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
                        <h5 class="place <?php if($usr['score'] < 0) { echo 'negative'; } ?>"><?php echo $usr['score']; ?></h5>
                        <img src="<?php echo $usr['picture']; ?>" border="0" alt="" />
                        <h5><?php echo $usr['display_name']; ?></h5>
                    </div>
                </div>
                <?php
            } else {
                $displayCount++;
            }
        }

        if(count($game['users']) == $displayCount) {
            ?>
            <p class="fade">No players...</p>
            <?php
        }
    }
} catch (Exception $e) {
    //show any errors
    $msg = "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}
?>