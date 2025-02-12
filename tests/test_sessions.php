<?php
/*
 * Author: Justin Searle
 * Date: 7/4/2016
 * Description: testing the sessions functions
 */

require_once('../includes/common.php');
require_once('../includes/database.php');
require_once('../includes/class.GameSession.php');

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $mySession->setup(true);

    if (isset($_POST['new-code'])) {

        //requested to make a new code for the current session
        $mySession->newCode();
        header("Location: test_sessions.php");
    } else if (isset($_POST['new-session'])) {

        //requested to make a new session
        session_regenerate_id();
        $mySession->removeSession(SESSION_ID);
        header("Location: test_sessions.php");
    }

    //get the current game code
    $code = $mySession->getCode();

} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

//var_dump($code, SESSION_ID);
//var_dump($_SERVER);
?>

<form action="" method="POST">
    <button type="submit" name="new-code">New Code</button>
    <button type="submit" name="new-session">New Session</button>
    <div>Random Code: <?php echo $code; ?></div>
    <div>Session ID: <?php echo SESSION_ID; ?></div>
</form>
