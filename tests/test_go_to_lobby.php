<?php
/*
 * Author: Justin Searle
 * Date: 7/4/2016
 * File: test_go_to_lobby.php
 * Description: file for testing the process of joining a lobby via unique code
 */

require_once('../includes/common.php');
require_once('../includes/database.php');
require_once('../includes/class.GameSession.php');

try {

    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    if (isset($_POST['join'])) {
        $code = $_POST['unique-id'];
        $name = $_POST['display-name'];

        if (empty($code)) {
            $msg = "You need to enter a game code to join!";
        } else if (empty($name)) {
            $msg = "You need to choose a display name!";
        } else {

            //request to join a session
            $result = $mySession->join($name, $code, DEVICE_IP);

            if ($result == true && intval($result)) {

                $_SESSION['user'] = $mySession->getUser();
                //var_dump($_SESSION['user']);
                header("Location: ../lobby/");

            } else if ($result == "user-exists") {
                $msg = "Display name already created";
            } else {
                $msg = "Game session cannot be found!";
            }
        }
    }

    //get the current game code
    $code = $mySession->getCode();
    echo "code: " . $code. "<br >";
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

//var_dump($code, SESSION_ID);
//var_dump($_SERVER);
echo (!empty($msg) ? '<h1>'.$msg.'</h1>' : '');
?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <input type="text" name="unique-id" placeholder="Join a Game">
    <input type="text" name="display-name" placeholder="Display Name">
    <input type="submit" name="join" value="Join">
</form>
