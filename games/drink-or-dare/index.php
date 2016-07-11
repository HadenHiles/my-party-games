<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/class.GameSession.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/header/header.php');

$game = 'drink-or-dare';

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $mySession->setup(true, $game);

    //check for form submission to join a game session
    if (isset($_POST['new-code'])) {

        //requested to make a new code for the current session
        $mySession->newCode();
        header("Location: index.php");
    } else if (isset($_POST['new-session'])) {

        //requested to make a new session
        session_regenerate_id();
        $mySession->removeSession(SESSION_ID);
        header("Location: index.php");
    }

    //get the current game code
    $code = $mySession->getCode(SESSION_ID);

} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

//var_dump($code, SESSION_ID);
?>

<h2>Drink Or Dare</h2>
<h4>Here is your game code: <?php echo $code; ?></h4>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <button type="submit" name="new-code">New Code</button>
    <button type="submit" name="new-session">New Session</button>

    <h4>Need to join a game?</h4>
    <div class="mdl-cell mdl-cell--6-col mdl-cell--3-offset">
        <a href="/join/">Join a Game</a>
    </div>
</form>
