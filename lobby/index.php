<?php
require_once("../includes/class.GameSession.php");
require_once("../includes/class.User.php");
require_once("../includes/common.php");
require_once("../includes/database.php");

//check for user in session
if (empty($_SESSION['user'])) {
    header ("Location: ../join/");
} else {
    require_once("header.php");
}

try {
    $thisUser = $_SESSION['user'];

    //init a new game session
    $mySession = new GameSession(SESSION_ID, DEVICE_IP);
    $user = new User(SESSION_ID, DEVICE_IP, $thisUser['name'], $thisUser['code']);
    
    if($_REQUEST['leave'] == true) {
        if($mySession->leave()) {
            $code = $_SESSION['current_game_code'];
            unset($_SESSION['current_game_code']);
            header('Location: /join/?last-game-code=' . $code);
        }
        $msg = "You can't leave!";
    }
    
    if($_POST['delete-game']) {
        $mySession->destroy($thisUser['code']);
    }

    //load the current game details
    if (!$game = $mySession->loadUsers($thisUser['code'])) {
        //game was not found
        if($user->isHost("get", $thisUser['userid'])) {
            header("location: /");
        } else {
            header("location: /join");
        }
    } else {
        //game was found
        if(isset($_POST['settings']) && $_POST['settings']) {
            foreach($game['users'] as $gameUser) {
                $user->isDisplay("set", $gameUser['id'], 0);
            }
            $displays = $_POST['displays'];
            $host = $_POST['host'];
            foreach($displays as $userDisplay) {
                $user->isDisplay("set", $userDisplay, 1);
            }
            $user->isHost("set", $host);

            $game_fields = explode(",", $_POST['fields']);

            $key_value_array = array();
            foreach($game_fields as $field) {
                $key_value_array[$field] = $_POST[$field];
            }
            $_SESSION['game_field_values'] = $key_value_array;

            header("location: ../games/" . $game['game_name'] . "/submitSetup.php");
        }
        ?>
        <div class="layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">
            <header class="header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
                <div class="mdl-layout__header-row">
                    <?php
                    if(!$user->isDisplay("get", $thisUser['userid'], 1)) {
                        ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?leave=true" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"><i class="fa fa-times" style="position: relative; left: -5px; top: -1px;"></i> Leave</a>
                        <?php
                    }
                    ?>
                    <span class="android-title mdl-layout-title" style="color: #757575; margin-left: 15px;">
                    <i class="fa fa-glass" aria-hidden="true" style="color: #8bc34a;"></i> Party Games
                </span>
                    <div class="mdl-layout-spacer"></div>
                    <div class="mdl-cell mdl-cell--1-col right" style="text-align: right;">
                        <?php
                        if($user->isHost("get", $thisUser['userid'])) {
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
                    <p class="fade" style="width: 100%; float: left; font-size: 36px; color: #fff;">#<?php echo $game['unique_code']; ?></p>
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
                        if($user->isDisplay("get", $thisUser['userid'], 1)) {
                            ?>
                            <!--                            <button class="mdl-button mdl-js-button mdl-button--icon" id="show-rules" style="float: left; color: #777; margin: -5px 0 0 5px;">-->
                            <!--                                <i class="fa fa-question"></i>-->
                            <!--                            </button>-->
                            <!--                            <div class="mdl-tooltip" for="show-rules">Rules</div>-->
                            <?php
                        }
                        ?>
                        <div class="clear"></div>
                        <p class="fade" style="width: 100%; float: left; font-size: 20px; color: #000;">#<?php echo $game['unique_code']; ?></p>
                        <?php
                        $showRules = true;
                        require_once("../games/" . $game['game_name'] . "/rules.php");
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
                if($user->isHost("get", $thisUser['userid'])) {
                    ?>
                    <a href="" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-color--primary mdl-color-text--primary-contrast right start-button">Start Game</a>
                    <?php
                }
                ?>
            </main>
        </div>
        <?php
    }

} catch (Exception $e) {
    //show any errors
    $msg =
    $msg[] = array("msg" => "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
}
if(!empty($msg)) {
    ?>
    <dialog class="mdl-dialog error">
        <h4 class="mdl-dialog__title"><?php echo $msg_title; ?></h4>
        <div class="mdl-dialog__content">
            <p style="color: #ccc; font-size: 8px;">Something happened.</p>
            <p><?php echo $msg; ?></p>
        </div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button close">OK</button>
        </div>
    </dialog>
    <script>
        (function() {
            var dialog = document.querySelector('dialog.error');
            if (dialog != null) {
                if (!dialog.showModal) {
                    dialogPolyfill.registerDialog(dialog);
                }
                dialog.showModal();
                dialog.querySelector('.close').addEventListener('click', function () {
                    dialog.close();
                });
            }
        })();
    </script>
    <?php
}

/*
if($showRules) {
    ?>
    <dialog class="mdl-dialog rules" style="width: 90%;">
        <div class="mdl-dialog__content">
            <?php require_once("../games/" . $game['game_name'] . "/rules.php"); ?>
        </div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button close">CLOSE</button>
        </div>
    </dialog>
    <script>
        (function() {
            var rulesDialog = document.querySelector('dialog.rules');
            if(rulesDialog != null) {
                if (!rulesDialog.showModal) {
                    dialogPolyfill.registerDialog(rulesDialog);
                }

                document.querySelector('#show-rules').addEventListener('click', function() {
                    rulesDialog.showModal();
                });

                rulesDialog.querySelector('.close').addEventListener('click', function() {
                    rulesDialog.close();
                });
            }
        })();
    </script>
    <?php
}
*/
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
                    require_once("../games/" . $game['game_name'] . "/settings.php");
                    ?>
                </div>
                <div class="mdl-tabs__panel" id="advanced-settings-panel">
                    <div id="settingsContent"></div>
                </div>
            </div>
            <input type="hidden" name="settings" value="true" />
        </form>
    </div>
    <div class="mdl-dialog__actions">
        <button type="button" class="mdl-button mdl-color--primary save" style="color: #fff;">APPLY</button>
        <button type="button" class="mdl-button close">CLOSE</button>
    </div>
</dialog>
<?php
if($user->isHost("get", $thisUser['userid'])) {
    ?>
    <script>
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

require_once("footer.php");
?>