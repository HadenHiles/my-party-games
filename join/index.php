<?php
/*
 * Author: Justin Searle
 * Date: 7/4/2016
 * File: join/index.php
 * Description: a form that will allow a user to join a specific game session
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/class.GameSession.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/join/header.php');

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    if (isset($_GET['game'])) {
        $enteredcode = $_GET['game'];
    }

    //check for form submission to join a game session
    if (isset($_POST['join'])) {
        //vars
        $code = $_POST['unique-id'];
        $enteredcode = $code;
        $name = $_POST['display-name'];
        $enteredname = $name;
        $fbToken = '';
        $fbUserid = '';

        //basic error handling
        if (empty($code)) {
            $msg = "You need to enter a game code to join!";
        } else if (empty($name)) {
            $msg = "You need to choose a display name!";
        } else {

            //request to join a session
            $result = $mySession->join($name, $code, $fbToken, $fbUserid);

            //check result and if true then save user in session and redirect to lobby
            if ($result == true && intval($result)) {

                $_SESSION['user'] = $mySession->getUser();
                //var_dump($_SESSION['user']);
                header("Location: ../lobby/");
                exit();

            } else if ($result == "user-exists") {
                $msg = "Display name already created for this game";
            } else {
                $msg = "Game session cannot be found!";
            }
        }
    }

    //get the current game code
    $code = $mySession->getCode(SESSION_ID);

} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

var_dump($code, SESSION_ID);
//var_dump($_SERVER);
?>

<?php echo (!empty($msg) ? '<h2>'.$msg.'</h2>' : ''); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

    <h4>Enter your game code</h4>
    <input type="text" name="unique-id" placeholder="Code" value="<?php echo $enteredcode; ?>">

    <h4>Enter a Display Name User OR Login with Facebook</h4>
    <input type="text" name="display-name" placeholder="Display Name" value="<?php echo $enteredname; ?>">
    <br />
    <a href="#">Login in with facebook</a><br /><br />
    <input type="submit" name="join" value="Join Game">

</form>


<?php
require_once('footer.php');
?>