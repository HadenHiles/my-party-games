<?php
/**
 * Created by handshiles on 2016-07-10.
 */
require_once('../includes/common.php');
require_once('../includes/database.php');
require_once('../includes/class.GameSession.php');
require_once('header.php');

try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    //check for form submission to join a game session
    if ((isset($_REQUEST['unique-id']) && !empty($_REQUEST['unique-id'])) || (isset($_SESSION['current_game_code']) || !empty($_SESSION['current_game_code']))) {
        //vars
        $code = $_REQUEST['unique-id'];
        $_SESSION['current_game_code'] = $code;

        //Facebook login
        require_once("../vendor/autoload.php");
        require_once("../login/facebook/config.php");

        $fb = new Facebook\Facebook([
            'app_id' => APP_ID,
            'app_secret' => APP_SECRET,
            'default_graph_version' => 'v2.6'
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = []; // Optional permissions
        $loginUrl = $helper->getLoginUrl('http://'.$_SERVER['SERVER_NAME'].'/tests/facebook/login-callback.php', $permissions);
        ?>
        <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
            <main class="mdl-layout__content">
                <div style="color: #cccccc;">
                    <h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4>
                </div>
                <div class="mdl-card mdl-shadow--6dp">
                    <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                        <h2 class="mdl-card__title-text">Who the heck are you?</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="guestForm">
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input class="mdl-textfield__input" type="text" name="display-name" id="display-name" />
                                <label class="mdl-textfield__label" for="display-name">Nickname</label>
                            </div>
                            <input type="hidden" name="unique-id" value="<?php echo $code; ?>" />
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
        <?php
        if(isset($_REQUEST['display-name'])) {
            $name = $_REQUEST['display-name'];
            $fbToken = '';
            $fbUserId = '';

            //basic error handling
            if (empty($name)) {
                $msg = "Please enter a nickname!";
            } else {
                //request to join a session
                $result = $mySession->join($name, $code, $fbToken, $fbUserId);

                //check result and if true then save user in session and redirect to lobby
                if ($result == true && intval($result)) {

                    $_SESSION['user'] = $mySession->getUser();
                    header("Location: index.php");
                    exit();

                } else if ($result == "user-exists") {
                    $msg = "Someone is already using that name!";
                } else {
                    $msg = "Game cannot be found!";
                }
            }
        } else if(isset($_REQUEST['fb-login'])) {
            try {
                // Get the Facebook\GraphNodes\GraphUser object for the current user.
                // If you provided a 'default_access_token', the '{access-token}' is optional.
                $response = $fb->get('/me', $_SESSION['fb_access_token']);
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $me = $response->getGraphUser();

            //request to join a session
            $result = $mySession->join($me['name'], (int)$_SESSION['current_game_code'], $_SESSION['fb_access_token'], $me['id']);

            //check result and if true then save user in session and redirect to lobby
            if ($result == true && intval($result)) {
                $_SESSION['user'] = $mySession->getUser();
                header("Location: index.php");
                exit();
            } else if ($result == "user-exists") {
                $msg = "Someone is already using that name!";
            } else {
                $msg = "Game cannot be found!";
            }
        }
    } else {
        ?>
        <div class="mdl-layout mdl-js-layout mdl-color--grey-100">
            <main class="mdl-layout__content">
                <div style="color: #cccccc;">
                    <h3 style="float: left;"><i class="fa fa-glass"></i></h3 style="float: left;"><h4 style="float: left; position: relative; top: 8px; left: 10px;">Party Games</h4>
                </div>
                <div class="mdl-card mdl-shadow--6dp">
                    <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                        <h2 class="mdl-card__title-text">Join Game</h2>
                    </div>
                    </br>
                    <div class="mdl-card__supporting-text">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="joinForm">
                            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input class="mdl-textfield__input" type="number" name="unique-id" id="unique-id" pattern="-?[0-9]*(\.[0-9]+)?" value="" required />
                                <label class="mdl-textfield__label" for="unique-id">Game Code</label>
                                <span class="mdl-textfield__error">Code must be a number!</span>
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
    }
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}

if(!empty($msg)) {
    ?>
    <dialog class="mdl-dialog">
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