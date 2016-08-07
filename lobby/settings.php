<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

$thisUser = $_SESSION['user'];

//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);
$user = new User(SESSION_ID, DEVICE_IP, $thisUser['name']);

if(!$game = $mySession->loadUsers($thisUser['code'])) {
    //game not found
} else {
    ?>
    <fieldset>Displays</fieldset>
    <?php
    foreach($game['users'] as $user) {
        if($user['is_display']) {
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="display<?php echo $user['userid']; ?>">
                <input type="checkbox" name="displays[]" id="display<?php echo $user['userid']; ?>" class="mdl-checkbox__input" checked>
                <span class="mdl-checkbox__label"><?php echo $user['display_name']; ?></span>
            </label>
            <div class="clear"></div>
            <?php
        } else {
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="display<?php echo $user['userid']; ?>">
                <input type="checkbox" name="displays[]" id="display<?php echo $user['userid']; ?>" class="mdl-checkbox__input">
                <span class="mdl-checkbox__label"><?php echo $user['display_name']; ?></span>
            </label>
            <div class="clear"></div>
            <?php
        }
    }
    ?>
    <hr>
    <fieldset>Host</fieldset>
    <?php
    foreach($game['users'] as $user) {
        if($user['is_host']) {
            ?>
            <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="host<?php echo $user['userid']; ?>">
                <input type="radio" id="host<?php echo $user['userid']; ?>" class="mdl-radio__button" name="host" value="<?php echo $user['userid']; ?>" checked />
                <span class="mdl-radio__label"><?php echo $user['display_name']; ?></span>
            </label>
            <div class="clear"></div>
            <?php
        } else {
            ?>
            <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="host<?php echo $user['userid']; ?>">
                <input type="radio" id="host<?php echo $user['userid']; ?>" class="mdl-radio__button" name="host" value="<?php echo $user['userid']; ?>" />
                <span class="mdl-radio__label"><?php echo $user['display_name']; ?></span>
            </label>
            <div class="clear"></div>
            <?php
        }
    }
}
?>
<script>
    // Expand all new MDL elements
    componentHandler.upgradeDom();
</script>
