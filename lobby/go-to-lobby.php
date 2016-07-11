<?php
/**
 * Created by handshiles on 2016-07-10.
 */
require_once('../includes/common.php');
require_once('../includes/database.php');
require_once('../includes/class.GameSession.php');
try {
    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);

    //check for form submission to join a game session
    if (isset($_REQUEST['join'])) {
        //vars
        $code = $_REQUEST['unique-id'];

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
                <div class="mdl-card mdl-shadow--6dp">
                    <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                        <h2 class="mdl-card__title-text">Who da hell are you?</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <div class="mdl-textfield mdl-js-textfield">
                                <input class="mdl-textfield__input" type="text" name="display-name" id="display-name" placeholder="Enter Nickname" />
                                <label class="mdl-textfield__label" for="display-name">Nickname</label>
                            </div>
                        </form>
                    </div>
                    <div class="mdl-card__actions">
                        <button class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">Continue</button>
                    </div>
                    <div class="mdl-card__supporting-text">
                        OR
                    </div>
                    <div class="mdl-card__actions mdl-card--border">
                        <a href="<? echo htmlspecialchars($loginUrl); ?>" class="btn btn-block btn-social btn-facebook">
                            <span class="fa fa-twitter"></span> Sign in with Facebook
                        </a>
                    </div>
                </div>
            </main>
        </div>
        <?
        if(isset($_REQUEST['display-name']) && !isset($_REQUEST['fb-login'])) {
            $name = $_REQUEST['display-name'];
            $fbToken = '';

            //basic error handling
            if (empty($code)) {
                $msg = "Please enter a game code to join!";
            } else if (empty($name)) {
                $msg = "Please choose a nickname!";
            } else {
                //request to join a session
                $result = $mySession->join($name, $code, $fbToken);

                //check result and if true then save user in session and redirect to lobby
                if ($result == true && intval($result)) {

                    $_SESSION['user'] = $mySession->getUser();
                    header("Location: index.php");
                    exit();

                } else if ($result == "user-exists") {
                    $msg = "Display name already created";
                } else {
                    $msg = "Game session cannot be found!";
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
            $result = $mySession->join($me['name'], $code, $_SESSION['fb_access_token']);

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
        //get the current game code
        $code = $mySession->getCode(SESSION_ID);
        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input type="text" name="unique-id" placeholder="Enter Game Code" value="<?php echo $code; ?>" />
            <input type="submit" name="join" value="Join" />
        </form>
        <?
    }
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
}
if(!empty($msg)) {
    echo $msg;
}