<?php
    require_once("includes/class.GameSession.php");
    require_once("includes/common.php");
    require_once("includes/database.php");
    require_once("header.php");

    try {
        //init a new game session
        $mySession = new GameSession(SESSION_ID, $_SERVER['REMOTE_ADDR']);

        if (isset($_POST['new-code'])) {

            //requested to make a new code for the current session
            $mySession->newCode();
            header("Location: ".$_SERVER['PHP_SELF']);
        } else if (isset($_POST['new-session'])) {

            //requested to make a new session
            session_regenerate_id();
            $mySession->removeSession(SESSION_ID);
            header("Location: ".$_SERVER['PHP_SELF']);
        }

        //get the current game code
        $code = $mySession->getCode();

    } catch (Exception $e) {
        //show any errors
        echo "Caught Exception: " . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile();
    }

    ?>
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">

        <div class="android-header mdl-layout__header mdl-layout__header--waterfall">
            <div class="mdl-layout__header-row">
              <span class="android-title mdl-layout-title" style="color: #8bc34a;">
                Party Games
              </span>
                <!-- Add spacer, to align navigation to the right in desktop -->
                <div class="android-header-spacer mdl-layout-spacer"></div>
                <div class="android-search-box mdl-textfield mdl-js-textfield mdl-textfield--expandable mdl-textfield--floating-label mdl-textfield--align-right mdl-textfield--full-width">
                    <label class="mdl-button mdl-js-button mdl-button--icon" for="search-field">
                        <i class="material-icons">search</i>
                    </label>
                    <div class="mdl-textfield__expandable-holder">
                        <input class="mdl-textfield__input" type="text" id="search-field">
                    </div>
                </div>
                <!-- Navigation -->
                <div class="android-navigation-container">
                    <nav class="android-navigation mdl-navigation">
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">Phones</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">Tablets</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">Wear</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">TV</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">Auto</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">One</a>
                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="">Play</a>
                    </nav>
                </div>
                <button class="android-more-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" id="more-button">
                    <i class="material-icons">more_vert</i>
                </button>
                <ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right mdl-js-ripple-effect" for="more-button">
                    <li class="mdl-menu__item">5.0 Lollipop</li>
                    <li class="mdl-menu__item">4.4 KitKat</li>
                    <li disabled class="mdl-menu__item">4.3 Jelly Bean</li>
                    <li class="mdl-menu__item">Android History</li>
                </ul>
            </div>
        </div>

        <div class="android-drawer mdl-layout__drawer">
            <span class="mdl-layout-title" style="color: #fff; height: auto; padding-top: 60px;">
              Party Games
            </span>
            <nav class="mdl-navigation">
                <a class="mdl-navigation__link" href="">Phones</a>
                <a class="mdl-navigation__link" href="">Tablets</a>
                <a class="mdl-navigation__link" href="">Wear</a>
                <a class="mdl-navigation__link" href="">TV</a>
                <a class="mdl-navigation__link" href="">Auto</a>
                <a class="mdl-navigation__link" href="">One</a>
                <a class="mdl-navigation__link" href="">Play</a>
                <div class="android-drawer-separator"></div>
                <span class="mdl-navigation__link" href="">Versions</span>
                <a class="mdl-navigation__link" href="">Lollipop 5.0</a>
                <a class="mdl-navigation__link" href="">KitKat 4.4</a>
                <a class="mdl-navigation__link" href="">Jelly Bean 4.3</a>
                <a class="mdl-navigation__link" href="">Android history</a>
                <div class="android-drawer-separator"></div>
                <span class="mdl-navigation__link" href="">Resources</span>
                <a class="mdl-navigation__link" href="">Official blog</a>
                <a class="mdl-navigation__link" href="">Android on Google+</a>
                <a class="mdl-navigation__link" href="">Android on Twitter</a>
                <div class="android-drawer-separator"></div>
                <span class="mdl-navigation__link" href="">For developers</span>
                <a class="mdl-navigation__link" href="">App developer resources</a>
                <a class="mdl-navigation__link" href="">Android Open Source Project</a>
                <a class="mdl-navigation__link" href="">Android SDK</a>
            </nav>
        </div>

        <div class="mdl-layout__content">

            <form name="game-session-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <button type="submit" name="new-code">New Code</button>
                <button type="submit" name="new-session">New Session</button>
                <div>Random Code: <?php echo $code; ?></div>
                <div>Session ID: <?php echo SESSION_ID; ?></div>
            </form>

            <footer class="android-footer mdl-mega-footer">
                <div class="mdl-mega-footer--middle-section">
                    <p class="mdl-typography--font-light">Copyright © <?=date("Y")?> Haden & Justin</p>
                    <p class="mdl-typography--font-light">We truly believe that your drink is not empty enough.</p>
                </div>
            </footer>
        </div>
    </div>
<?php
require_once("footer.php");
?>