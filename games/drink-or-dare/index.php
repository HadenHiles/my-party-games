<?php
/**
 * Created by handshiles on 2016-08-01.
 */
$pageTitle = "Drink Or Dare";
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");
require_once(ROOT."/games/header.php");
?>

    <div class="layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">
        <header class="header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
            <div class="mdl-layout__header-row">
                <!-- Icon button -->
                <button class="mdl-button mdl-js-button mdl-button--icon" onclick="window.history.back(); return false;">
                    <i class="fa fa-arrow-left"></i>
                </button>
                <span class="mdl-layout-title"><?php echo $pageTitle; ?></span>
                <div class="mdl-layout-spacer"></div>
            </div>
        </header>
        <div class="ribbon"></div>
        <main class="main mdl-layout__content">
            <div class="container mdl-grid">
                <div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
                <div class="content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col">
                    <div class="crumbs mdl-color-text--grey-500">
                        Games &gt; Drink Or Dare
                    </div>
                    <?php
                    require_once("rules.php");
                    ?>
                </div>
            </div>
            <a href="create.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-color--primary mdl-color-text--primary-contrast right create-button">Create Game</a>
        </main>
    </div>

<?php
require_once(ROOT."/games/footer.php");
?>