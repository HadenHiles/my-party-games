<?php
/**
 * Created by handshiles on 2016-07-10.
 */
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");
require_once(ROOT.'/includes/database.php');
require_once(ROOT.'/includes/class.GameSession.php');
require_once(ROOT.'/includes/class.User.php');

//Facebook login
require_once(ROOT."/vendor/autoload.php");
require_once(ROOT."/login/facebook/config.php");

$fb = new Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.6'
]);

//requests
$formToDisplay = "join";

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP);

    $gameName = $_SESSION['game']['gameName'];
    $isHost = (isset($_SESSION['game']['isHost']) && $_SESSION['game']['isHost']);
    $isDisplay = (isset($_SESSION['game']['isDisplay']) && $_SESSION['game']['isDisplay']);

    //set default host in session if not already set
    if (empty($_SESSION['game']['isHost'])) {
        $_SESSION['game']['isHost'] = $isHost;
    }

    //set default display in session if not already set
    if (empty($_SESSION['game']['isDisplay'])) {
        $_SESSION['game']['isDisplay'] = $isDisplay;
    }

    //set game name
    if (!empty($gameName)) {
        $mySession->setGameName($gameName);

        //check for request to leave game
        if (isset($_REQUEST['leave']) && $_REQUEST['leave']) {
            $mySession->clearSessionVars();
            header("Location: /join/");
            exit();
        }
    }

    //check for form submission to join a game session
    if ((!empty($_REQUEST['unique-id']) || !empty($_SESSION['game']['code']))) {

        //get the game code from url or session
        $code = $_REQUEST['unique-id'];
        if(empty($code) && !empty($_SESSION['game']['code'])) {
            $code = $_SESSION['game']['code'];
        }

        //get game name now that we have a code if we dont already have it
        if (empty($gameName)) {
            $_SESSION['game']['gameName'] = $mySession->getGameName($code);
            $gameName = $_SESSION['game']['gameName'];
        }

        //valudate that the code given exists
        if(!$mySession->validateGame($code)) {
            $msg[] = array("msg" => "game-not-found");
        } else {
            //check for update to code in session
            if($_SESSION['game']['code'] != intval($code)) {
                $_SESSION['game']['code']  = $code;
            }

            //display nickname form
            $formToDisplay = "nickname";

            //check to see if a user is already in a game
            if($user->isJoined()) {
                //For users who just left a game and we still have all of their information except game_id
                $mySession->switchGame($code);
                header("Location: ../lobby/");
                exit();
            }

            $fbToken = '';
            $fbUserId = '';
            $gamePicture = '';
            $name = '';

            //check for user submitted profile and validate
            if(isset($_REQUEST['display-name'])) {

                $name = $_REQUEST['display-name'];
                $gamePicture = $_REQUEST['picture'];
            }
            //user chose to login via facebook
            else if (isset($_REQUEST['fb-login'])) {

                try {
                    // Get the Facebook\GraphNodes\GraphUser object for the current user.
                    // If you provided a 'default_access_token', the '{access-token}' is optional.
                    $response = $fb->get('/me', $_SESSION['fb_access_token']);
                    $me = $response->getGraphUser();

                    //request to join a session
                    $fbToken =  $_SESSION['fb_access_token'];
                    $fbUserId = $me['id'];
                    $name = $me['name'];
                    $gamePicture = "http://graph.facebook.com/". $me['id']. "/picture?type=large";

                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    $msg = array('msg' => 'Graph returned an error: ' . $e->getMessage());
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    $msg = array('msg' => 'Facebook SDK returned an error: ' . $e->getMessage());
                }
            } else if ($isDisplay) {

                $formToDisplay = "display";
                $name = $_REQUEST['screen-name'];
            }

            //join a game here
            if (empty($name)) {
                $msg[] = array("msg" => "user-enter-nickname");
            } else if (!$isDisplay) {
                //request to join a session
                $result = $mySession->join($name, $fbToken, $fbUserId, $gamePicture);

                //check result and if true then save user in session and redirect to lobby
                if ($result == true && intval($result)) {
                    $_SESSION['user'] = $user->getUser();

                    if($isHost) {
                        $user->isHost("set", $_SESSION['user']['id']);
                        //reset user session values
                        $_SESSION['user'] = $user->getUser();
                    }

                    header("Location: ../lobby/");
                    exit();
                } else if ($result == "user-exists") {

                    //override information if user using facebook otherwise they will have to choose another name
                    if (!empty($fbUserId)) {
                        //override with new information
                        if ($user->updateUser($name, $code, $fbToken, $fbUserId, $gamePicture)) {
                            $_SESSION['user'] = $user->getUser();

                            if($isHost) {
                                $user->isHost("set", $_SESSION['user']['id']);

                                //reset user session values
                                $_SESSION['user'] = $user->getUser();
                            }
                            if($isDisplay) {
                                $user->isDisplay("set", $_SESSION['user']['id'], 1);

                                //reset user session values
                                $_SESSION['user'] = $user->getUser();
                            }

                            header("Location: ../lobby/");
                            exit();
                        } else {
                            $msg[] = array("msg" => "game-not-found");
                        }
                    } else {
                        $msg[] = array("msg" => "game-name-in-use");
                    }
                } else {
                    $msg[] = array("msg" => "game-not-found");
                }
            }
        }
    } else if(!$isDisplay) {
        $formToDisplay = "join";
    } else {
        $formToDisplay = "display";
    }
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

require_once(ROOT.'/join/header.php');
//var_dump($formToDisplay);

if($formToDisplay == "nickname" && !isset($_REQUEST['fb-login'])) {

    $helper = $fb->getRedirectLoginHelper();

    $permissions = ["public_profile"]; // Optional permissions
    $loginUrl = $helper->getLoginUrl('http://'.$_SERVER['SERVER_NAME'].'/login/facebook/login-callback.php', $permissions);
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <a href="/" class="party-games-title" style="color: #ccc;"><h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4></a>
            </div>
            <div class="mdl-card mdl-shadow--6dp">
                <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                    <h2 class="mdl-card__title-text">Who the heck are you?</h2>
                </div>
                <div class="mdl-card__supporting-text select-avatar">
                    <div class="joining-game">
                        <p style="text-align:center;">Currently Joining Game <?php echo $code; ?> <a href="/join/?leave=true">Leave</a></p>
                    </div>
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
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <a href="/" class="party-games-title" style="color: #ccc;"><h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4></a>
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
    ?>
    <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
        <main class="mdl-layout__content main-form">
            <div style="color: #cccccc;">
                <a href="/" class="party-games-title" style="color: #ccc;"><h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4></a>
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

require_once(ROOT.'/join/footer.php');
?>