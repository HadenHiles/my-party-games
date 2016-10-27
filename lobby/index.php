<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");
require_once(ROOT."/includes/class.GameSession.php");
require_once(ROOT."/includes/class.User.php");
require_once(ROOT."/includes/database.php");

//check for user in session and we have a game code
if (empty($_SESSION['user']) || empty($_SESSION['game']['code'])) {
    header ("Location: ../join/");
    exit();
}

//var_dump($_SESSION);
//var_dump($_POST);

//get header information
require_once(ROOT."/lobby/header.php");

try {
    //load user data from session
    $thisUser = $_SESSION['user'];
    $code = $_SESSION['game']['code'];

    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP, $thisUser['name'], $code);

    //other variables
    $isHost = $user->isHost("get", $thisUser['id']);
    $isDisplay = $user->isDisplay("get", $thisUser['id']);

    //make sure code is valid and game exists
    if (!$mySession->validateGame($code)) {
        header ("Location: ../join/");
        exit();
    }

    // Redirect user to specific game based on game name
    if($game = $mySession->loadUsers($code)) {
        //var_dump($game);

        //check for correct host privleges
        if (!$_SESSION['game']['isHost'] && $isHost) {
            //user got host
            $_SESSION['game']['isHost'] = true;
            //reset user session variables and reload
            $_SESSION['user'] = $user->getUser();
            header("Location: /lobby");
            exit();
        } else if ($_SESSION['game']['isHost'] && !$isHost) {
            //user lost host, force reload
            $_SESSION['game']['isHost'] = false;
            //reset user session variables and reload
            $_SESSION['user'] = $user->getUser();
            header("Location: /lobby");
            exit();
        } else if (!$isHost) {
            //reset other users sessions to not host
            $_SESSION['game']['isHost'] = false;
        }

        //check for correct display privleges
        if (!$_SESSION['game']['isDisplay'] && $isDisplay) {
            //user got host
            $_SESSION['game']['isDisplay'] = true;
            //reset user session variables and reload
            $_SESSION['user'] = $user->getUser();
            header("Location: /lobby");
            exit();
        } else if ($_SESSION['game']['isDisplay'] && !$isDisplay) {
            //user lost host, force reload
            $_SESSION['game']['isDisplay'] = false;
            //reset user session variables and reload
            $_SESSION['user'] = $user->getUser();
            header("Location: /lobby");
            exit();
        } else if (!$isDisplay) {
            //reset other users sessions to not host
            $_SESSION['game']['isDisplay'] = false;
        }

        /*
         * switch code based on current game
         */
        switch ($game['game_name']) {

            //required steps to init drink or dare
            case "drink-or-dare":
                require_once(ROOT."/games/drink-or-dare/class.DrinkOrDare.php");

                $dod = new DrinkOrDare($code, $thisUser['id']);

                //check if host decided to delete game
                if($_POST['delete-game'] || $_REQUEST['leave'] == true) {

                    //delete game if host
                    if($isHost) {
                        $mySession->destroy($code);
                        $dod->destroy($code);
                    }

                    //leave game if not host
                    if($mySession->leave()) {
                        $_SESSION['user'] = $user->getUser();
                        header('Location: /join/?unique-code=' . $code);
                        exit();
                    }
                }

                //check for settings update
                if(isset($_POST['settings']) && $_POST['settings'] && $isHost) {
                    //var_dump($_POST);

                    //get post variables
                    $displays = $_POST['displays'];
                    $host = $_POST['host'];

                    //set new host
                    $user->isHost("set", $host);
                    if ($host == $thisUser['id']) {
                        $_SESSION['game']['isHost'] = true;
                    } else {
                        $_SESSION['game']['isHost'] = false;
                    }

                    //reset all displays
                    foreach ($game['users'] as $gameUser) {
                        $user->isDisplay("set", $gameUser['id'], false);
                    }

                    //toggle displays
                    if (!empty($displays)) {
                        //set all displays from post
                        foreach ($displays as $userDisplay) {

                            $user->isDisplay("set", $userDisplay, true);
                            if ($userDisplay == $thisUser['id']) {
                                $_SESSION['game']['isDisplay'] = true;
                            } else {
                                $_SESSION['game']['isDisplay'] = false;
                            }
                        }
                    }

                    //get posted fields
                    $game_fields = explode(",", $_POST['fields']);

                    $key_value_array = array();
                    foreach($game_fields as $field) {
                        $key_value_array[$field] = $_POST[$field];
                    }
                    $game_field_values = $key_value_array;

                    //update the game
                    if (!$dod->isStarted($thisUser['game_id'])) {
                        //game doesn't exist so we can start game here and it will be created with our init variables
                        $dod->start(false);
                    } else {
                        //game already exists so we need to update it with new variables
                        $dod->update($thisUser['game_id'], 1, $game_field_values['rounds'], 1, $game_field_values['drinks_to_win']);
                    }

                    //reset user session before refreshing
                    $_SESSION['user'] = $user->getUser();

                    //refresh without post data
                    header("location: /lobby/");
                    exit();
                }

                //check if game has started
                if($dod->isStarted($code, true)) {
                    header('location: /games/drink-or-dare/play/');
                    exit();
                }
                break;

            /*
             * add more games that need the lobby functionality here
             */

            //default case
            default:
                break;
        }
    } else if ($isHost) {
        header("location: /");
        exit();
    } else {
        header("location: /join/");
        exit();
    }

    /*
     * start html output here
     */
    ?>
    <div class="layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">
        <header class="header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
            <div class="mdl-layout__header-row">
                <?php

                /*
                 * Show leave button only if user not display
                 */
                if(!$isDisplay) {
                    ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?leave=true" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"><i class="fa fa-times" style="position: relative; left: -5px; top: -1px;"></i> Leave</a>
                    <?php
                }
                ?>
                <span class="android-title mdl-layout-title" style="margin-left: 15px;">
                <i class="fa fa-glass" aria-hidden="true" style="color: #8bc34a;"></i> Party Games
            </span>
                <div class="mdl-layout-spacer"></div>
                <div class="mdl-cell mdl-cell--1-col right" style="text-align: right; min-width: 80px;">
                    <?php

                    /*
                     * show host controls if this user is the host of the game
                     */
                    if($isHost) {
                        echo '<p style="float:left; margin:4px 0 0 0;font-weight:700;">HOST</p>';
                        ?>

                        <!-- Right aligned menu below button -->
                        <button id="settings" class="mdl-button mdl-js-button mdl-button--icon">
                            <i class="fa fa-cog fade"></i>
                        </button>

                        <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="settings">
                            <li class="mdl-menu__item" id="show-settings">Settings</li>
                            <li class="mdl-menu__item" id="delete-game" style="color: #CE0000" onclick="$('#delete-game-form').submit();">Delete Game</li>
                        </ul>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="delete-game-form">
                            <input type="hidden" name="delete-game" value="true" />
                        </form>

                        <?php
                    } else {

                        /*
                         * normal user, wait for host to start game
                         */
                        ?>
                        <p class="fade" style="width: 175px; margin: 4px 0 0 -100px;">Waiting for game to start...</p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </header>
        <div class="ribbon">
            <div class="game_code" style="text-align: center; margin-top: 5vh;">
                <p class="fade" style="width: 100%; float: left; font-size: 36px; color: #fff;">#<?php echo $code; ?></p>
            </div>
        </div>
        <main class="main mdl-layout__content">
            <div class="container mdl-grid">
                <div class="mdl-cell mdl-cell--4-col">
                    <div id="players"></div>
                </div>
                <div class="content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col">
                    <div class="crumbs mdl-color-text--grey-500" style="color: #6ab344; float: left; font-size: 30px; text-transform: capitalize; margin-bottom: 0;">
                        <?php echo str_replace("-", " ", $game['game_name']); ?>
                    </div>

                    <?php
                    //check if this user is a display
                    if($isDisplay) {
                        ?>
                        <!--                            <button class="mdl-button mdl-js-button mdl-button--icon" id="show-rules" style="float: left; color: #777; margin: -5px 0 0 5px;">-->
                        <!--                                <i class="fa fa-question"></i>-->
                        <!--                            </button>-->
                        <!--                            <div class="mdl-tooltip" for="show-rules">Rules</div>-->
                        <?php
                    }
                    ?>

                    <div class="clear"></div>
                    <p class="fade" style="width: 100%; float: left; font-size: 20px; color: #000;">#<?php echo $code; ?></p>

                    <?php
                    /*
                     * Showing rules for the game here
                     */
                    require_once(ROOT."/games/" . $game['game_name'] . "/rules.php");


                    /*
                     * chat currently not working
                     */

                    /*
                    ?>
                    <div id="chatMessages"></div>
                    <?php
                        if(!$user->isHost("get", $thisUser['userid'])) {
                            ?>
                            <form action="chat.php" id="chatMessageForm">
                                <div class="mdl-textfield mdl-js-textfield mdl-shadow--2dp messageArea">
                                    <textarea class="mdl-textfield__input" type="text" rows="2" name="message" maxlength="500" id="messageText"></textarea>
                                    <!--                        <label class="mdl-textfield__label" for="messageText">Enter a Message</label>-->
                                    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--primary send" id="sendMessage">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                            <?php
                        }
                    */
                    ?>

                </div>
            </div>

            <?php
            /*
             * show start game button if this user is a host
             */
            if($isHost) {
                ?>
                <a href="/games/<?php echo $game['game_name']; ?>/play/" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-color--primary mdl-color-text--primary-contrast right start-button">Start Game</a>
                <?php
            }
            ?>

        </main>
    </div>
    <?php

} catch (Exception $e) {
    //show any errors
    $msg[] = array("msg" => "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
}

//output the settings dialog if this user is host
if ($isHost) {
    ?>
    <dialog class="mdl-dialog settings">
        <div class="mdl-dialog__content">
            <form id="settingsForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
                    <div class="mdl-tabs__tab-bar">
                        <a href="#game-setup-panel" class="mdl-tabs__tab is-active">Setup</a>
                        <a href="#advanced-settings-panel" class="mdl-tabs__tab">Advanced Settings</a>
                    </div>
                    <div class="clear" style="margin-bottom: 10px;"></div>
                    <div class="mdl-tabs__panel is-active" id="game-setup-panel">
                        <?php
                        require_once(ROOT . "/games/" . $game['game_name'] . "/settings.php");
                        ?>
                    </div>
                    <div class="mdl-tabs__panel" id="advanced-settings-panel">
                        <div id="settingsContent"></div>
                    </div>
                </div>
                <input type="hidden" name="settings" value="true"/>
            </form>
        </div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button mdl-color--primary save" style="color: #fff;">APPLY</button>
            <button type="button" class="mdl-button close">CLOSE</button>
        </div>
    </dialog>

    <script defer type="text/javascript">
        $(function() {
            $.get("settings.php", function(data) {
                $('#settingsContent').html(data);
            });

            if(dialogPolyfill !== undefined) {
                var settingsDialog = document.querySelector('dialog.settings');
                if(settingsDialog != null) {
                    if (!settingsDialog.showModal) {
                        dialogPolyfill.registerDialog(settingsDialog);
                    }

                    document.querySelector('#show-settings').addEventListener('click', function() {
                        $.get("settings.php", function(data) {
                            $('#settingsContent').html(data);
                        });
                        $('dialog.settings').css({"display": "block"});
                        settingsDialog.showModal();
                    });

                    settingsDialog.querySelector('.close').addEventListener('click', function() {
                        settingsDialog.close();
                        $('dialog.settings').css({"display": "none"});
                    });

                    settingsDialog.querySelector('.save').addEventListener('click', function() {
                        settingsDialog.close();
                        $('dialog.settings').css({"display": "none"});
                        $('#settingsForm').submit();
                    });
                }
            }
        });
    </script>

    <?php
}

require_once(ROOT."/lobby/footer.php");
?>