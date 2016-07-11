<?php
ini_set("display_errors", 1);
require_once("../includes/class.GameSession.php");
require_once("../includes/common.php");
require_once("../includes/database.php");
require_once("header.php");

try {
    //check for user in session
    if (empty($_SESSION['user'])) {
        header ("Location: go-to-lobby.php");
    }
    $user = $_SESSION['user'];

    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    //load the current game details
    if (!$game = $mySession->loadUsers($user['code'])) {
        //game was not found
    } else {
        //game was found
//        echo "<pre>";
//        var_dump($game);
//        echo "</pre>";
    }
    
} catch (Exception $e) {
    //show any errors
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100" style="justify-content: initial;">
        <div style="color: #6ab344;">
            <h2 style="float: left;"><?php echo $game['id']; ?></h2>
        </div>
        <?php
        foreach($game['users'] as $user) {
            ?>
            <div class="mdl-cell mdl-cell--5-col">
                <div class="mdl-card mdl-shadow--6dp player">
                    <div class="mdl-card__supporting-text">
                        <img src="http://graph.facebook.com/<?php echo $user['fb_user_id']; ?>/picture?type=large" border="0" alt="" />
                        <h5><?php echo $user['display_name']; ?></h5>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
<?php
require_once("footer.php");
?>