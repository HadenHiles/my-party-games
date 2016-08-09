<?php
/**
 * Created by handshiles on 2016-07-10.
 */
require_once('../includes/common.php');
require_once('../includes/database.php');
require_once('../includes/class.GameSession.php');
require_once('../includes/class.User.php');

//Facebook login
require_once("../vendor/autoload.php");
require_once("../login/facebook/config.php");

$fb = new Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.6'
]);

//requests
$isDisplay = $_REQUEST['display'];

$formToDisplay = "joinGame";

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);

    //check for form submission to join a game session
    if ((!empty($_REQUEST['unique-id']) || !empty($_SESSION['current_game_code']))) {
        //vars
        $code = $_REQUEST['unique-id'];
        
        if(empty($code) && !empty($_SESSION['current_game_code'])) {
            $code = $_SESSION['current_game_code'];
        } else if($_SESSION['current_game_code'] != intval($code)) {
            $_SESSION['current_game_code'] = intval($code);
        }

        $isGame = $mySession->getGame($code);
        if(!$isGame) {
            $msg = "Game could not be found.";
            $formToDisplay = "join";
        } else {
            $formToDisplay = "nickname";
            
            //For users who just left a game and we still have all of their information except game_id
            $mySession->switchGame($code);

            if($mySession->isJoined()) {
                header("Location: ../lobby/");
            }
            
            if(isset($_REQUEST['display-name'])) {
                $name = $_REQUEST['display-name'];
                $picture = $_REQUEST['picture'];
                $fbToken = '';
                $fbUserId = '';

                //basic error handling
                if (empty($name)) {
                    $msg = "Please enter a nickname!";
                } else {
                    //request to join a session
                    $result = $mySession->join($name, $fbToken, $fbUserId, $picture);

                    //check result and if true then save user in session and redirect to lobby
                    if ($result == true && intval($result)) {
                        $_SESSION['user'] = $user->getUser();

                        if($_SESSION['isHost']) {
                            $user->isHost("set", $_SESSION['user']['userid']);
                            unset($_SESSION['isHost']);
                        }

                        header("Location: ../lobby/");
                    } else if ($result == "user-exists") {
                        $msg = "Someone is already using that name!";
                    } else {
                        $msg = "Game cannot be found!";
                    }
                }
            } else if(isset($_REQUEST['fb-login'])) {
                $formToDisplay = "nickname";
                try {
                    // Get the Facebook\GraphNodes\GraphUser object for the current user.
                    // If you provided a 'default_access_token', the '{access-token}' is optional.
                    $response = $fb->get('/me', $_SESSION['fb_access_token']);

                    $me = $response->getGraphUser();

                    //request to join a session
                    $picture = "http://graph.facebook.com/". $me['id']. "/picture?type=large";
                    $result = $mySession->join($me['name'], $_SESSION['fb_access_token'], $me['id'], $picture);

                    //check result and if true then save user in session and redirect to lobby
                    if ($result == true && intval($result)) {

                        $_SESSION['user'] = $user->getUser();

                        if($_SESSION['isHost']) {
                            $user->isHost("set", $_SESSION['user']['userid']);
                            unset($_SESSION['isHost']);
                        }

                        header("Location: ../lobby/");
                        exit();
                    } else if ($result == "user-exists") {
                        //override with new information
                        $result = $mySession->updateUser($me['name'], $code, $_SESSION['fb_access_token'], $me['id'], $picture);
                        
                        if ($result == true) {
                            $_SESSION['user'] = $user->getUser();

                            if($_SESSION['isHost']) {
                                $user->isHost("set", $_SESSION['user']['userid']);
                                unset($_SESSION['isHost']);
                            }

                            header("Location: ../lobby/");
                            exit();
                        } else if (intval($result)) {
                        } else {
                            $msg = "Game cannot be found!";
                        }
                    } else {
                        $msg = "Game cannot be found!";
                    }
                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    $msg = 'Graph returned an error: ' . $e->getMessage();
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    $msg = 'Facebook SDK returned an error: ' . $e->getMessage();
                }
            } else if($isDisplay) {
                $formToDisplay = "display";

                $name = $_REQUEST['screen-name'];
                $picture = '';
                $fbToken = '';
                $fbUserId = '';

                //basic error handling
                if (empty($name)) {
                    if(!isset($_SESSION['isHost'])) {
                        $msg = "Please enter a name for the display!";
                    }
                } else {
                    //request to join a session
                    $result = $mySession->join($name, $fbToken, $fbUserId, $picture);

                    //check result and if true then save user in session and redirect to lobby
                    if ($result == true && intval($result)) {
                        $_SESSION['user'] = $user->getUser();

                        //If user is a host
                        if($_SESSION['isHost']) {
                            $user->isHost("set",$_SESSION['user']['userid']);
                            unset($_SESSION['isHost']);
                        }
                        $user->isDisplay("set", $_SESSION['user']['userid'], 1);
                        header("Location: ../lobby/");
                    } else if ($result == "user-exists") {
                        $msg = "That name is already in use!";
                    } else {
                        $msg = "Game cannot be found!";
                        $msg_code = 1;
                    }
                }
            }
        }
    } else {
        if(!$isDisplay) {
            $formToDisplay = "join";
        } else {
            $formToDisplay = "display";
        }
    }
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}



if($formToDisplay == "nickname" && !isset($_REQUEST['fb-login'])) {
    require_once('header.php');
    $helper = $fb->getRedirectLoginHelper();

    $permissions = ["public_profile"]; // Optional permissions
    $loginUrl = $helper->getLoginUrl('http://'.$_SERVER['SERVER_NAME'].'/login/facebook/login-callback.php', $permissions);
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4>
            </div>
            <div class="mdl-card mdl-shadow--6dp">
                <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                    <h2 class="mdl-card__title-text">Who the heck are you?</h2>
                </div>
                <div class="mdl-card__supporting-text select-avatar">
                    <div class="avatar">
                        <a id="show-avatars" type="button" class="mdl-button" style="height: 64px; width: 64px; padding: 5px;">
                            <img src="pictures/person.png" alt="Avatar" class="responsive" />
                        </a>
                    </div>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="guestForm">
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input class="mdl-textfield__input" type="text" name="display-name" id="display-name" />
                            <label class="mdl-textfield__label" for="display-name">Nickname</label>
                        </div>
                        <input type="hidden" name="unique-id" value="<?php echo $code; ?>" />
                        <input type="text" class="off-screen" id="avatar-picture" name="picture" value="/join/pictures/person.png" />
                    </form>
                </div>
                <div class="mdl-card__actions" style="text-align: center; margin-top: -25px;">
                    <button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" onclick="$('#guestForm').submit();" style="width: 100%;">Continue As Guest</button>
                </div>
                <div class="mdl-card__actions mdl-card--border" style="text-align: center; padding: 25px;">
                    <span style="font-weight: bold;">OR</span>
                </div>
                <div class="mdl-card__actions mdl-card--border facebook">
                    <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="btn btn-block btn-social btn-facebook">
                        <span class="fa fa-facebook"></span> Sign in with Facebook
                    </a>
                </div>
            </div>
        </main>
    </div>
    <dialog class="mdl-dialog avatars">
        <h4 class="mdl-dialog__title">Choose An Avatar</h4>
        <div class="mdl-dialog__content">
            <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
                <div class="mdl-tabs__tab-bar">
                    <a href="#characters-panel" class="mdl-tabs__tab">Characters</a>
                    <a href="#animals-panel" class="mdl-tabs__tab">Animals</a>
                    <a href="#party-panel" class="mdl-tabs__tab">Party</a>
                </div>
                <div class="mdl-tabs__panel is-active" id="characters-panel">
                    <?php
                    $dirname = "pictures/characters/";
                    $images = glob($dirname."*.png");
                    foreach($images as $image) {
                        echo '<div class="avatar-image"><img src="http://'.$_SERVER['HTTP_HOST'] . '/join/' . $image.'" class="responsive" /></div>';
                    }
                    ?>
                </div>
                <div class="mdl-tabs__panel" id="animals-panel">
                    <?php
                    $dirname = "pictures/animals/";
                    $images = glob($dirname."*.png");
                    foreach($images as $image) {
                        echo '<div class="avatar-image"><img src="http://'.$_SERVER['HTTP_HOST'] . '/join/' . $image.'" class="responsive" /></div>';
                    }
                    ?>
                </div>
                <div class="mdl-tabs__panel" id="party-panel">
                    <?php
                    $dirname = "pictures/party/";
                    $images = glob($dirname."*.png");
                    foreach($images as $image) {
                        echo '<div class="avatar-image"><img src="http://'.$_SERVER['HTTP_HOST'] . '/join/' . $image.'" class="responsive" /></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button close">Continue</button>
        </div>
    </dialog>
    <?php
} else if($formToDisplay == "join") {
    require_once('header.php');
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4>
            </div>
            <div class="mdl-card mdl-shadow--6dp">
                <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                    <h2 class="mdl-card__title-text">Join Game</h2>
                </div>
                </br>
                <div class="mdl-card__supporting-text">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="joinForm">
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input class="mdl-textfield__input" type="number" name="unique-id" id="unique-id" pattern="-?[0-9]*(\.[0-9]+)?" value="<?php echo $_REQUEST['last-game-code']; ?>" required />
                            <label class="mdl-textfield__label" for="unique-id">Game Code</label>
                            <span class="mdl-textfield__error">Please enter a valid code!</span>
                        </div>
                    </form>
                </div>
                <div class="mdl-card__actions" style="text-align: center;">
                    <button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" onclick="$('#joinForm').submit();" style="width: 100%;">Join</button>
                </div>
            </div>
        </main>
    </div>
    <?php
} else if($formToDisplay == "display") {
    require_once('header.php');
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4>
            </div>
            <div class="mdl-card mdl-shadow--6dp">
                <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                    <h2 class="mdl-card__title-text">Display Settings</h2>
                </div>
                </br>
                <div class="mdl-card__supporting-text">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="displayForm">
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input class="mdl-textfield__input" type="text" name="screen-name" id="screen-name" />
                            <label class="mdl-textfield__label" for="screen-name">Name</label>
                            <span class="mdl-textfield__error">Please enter a name for the display!</span>
                        </div>
                        <input type="hidden" name="unique-id" value="<?php echo $_REQUEST['unique-id']; ?>" />
                        <input type="hidden" name="display" value="true" />
                    </form>
                </div>
                <div class="mdl-card__actions" style="text-align: center;">
                    <button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" onclick="$('#displayForm').submit();" style="width: 100%;">Continue</button>
                </div>
            </div>
        </main>
    </div>
    <?php
}

if(!empty($msg)) {
    ?>
    <dialog class="mdl-dialog error" code="<?php echo $msg_code; ?>">
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

require_once('footer.php');
?>