<?php 
    require_once(ROOT."/splash/header.php");
    ?>
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">

        <div class="android-header mdl-layout__header mdl-layout__header--waterfall">
            <div class="mdl-layout__header-row">
              <span class="android-title mdl-layout-title" style="color: #757575;">
                <i class="fa fa-glass" aria-hidden="true" style="color: #8bc34a;"></i> Party Games
              </span>
                <!-- Add spacer, to align navigation to the right in desktop -->
                <div class="android-header-spacer mdl-layout-spacer"></div>
                <div class="android-search-box mdl-textfield mdl-js-textfield mdl-textfield--expandable mdl-textfield--floating-label mdl-textfield--align-right mdl-textfield--full-width">
                    <label class="mdl-button mdl-js-button mdl-button--icon" for="search-field">
                        <i class="material-icons">search</i>
                    </label>
                    <div class="mdl-textfield__expandable-holder">
                        <input class="mdl-textfield__input" type="text" id="search-field" placeholder="Find A Game">
                    </div>
                    <div id="search-results"></div>
                </div>


                <!-- Navigation -->
<!--                <div class="android-navigation-container">-->
<!--                    <nav class="android-navigation mdl-navigation">-->
<!--                        <a class="mdl-navigation__link mdl-typography--text-uppercase" href="/games">Games</a>-->
<!--                    </nav>-->
<!--                </div>-->
                <button class="android-more-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" id="more-button">
                    <i class="material-icons">more_vert</i>
                </button>
                <ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right mdl-js-ripple-effect" for="more-button">
                    <li class="mdl-menu__item"><a href="../lobby/"><i class="fa fa-right-arrow"></i> Current Game</a></li>
                </ul>
            </div>
        </div>

        <div class="android-drawer mdl-layout__drawer">
            <span class="mdl-layout-title" style="color: #fff; height: auto; padding-top: 60px;">
              <i class="fa fa-glass" aria-hidden="true"></i> Party Games
            </span>
            <nav class="mdl-navigation">
                <span class="mdl-navigation__link">Games</span>
                <div class="android-drawer-separator"></div>
                <a class="mdl-navigation__link" href="/games/drink-or-dare">Drink or Dare</a>
            </nav>
        </div>

        <div class="mdl-layout__content">
            <div class="mdl-grid mdl-grid--no-spacing" style="height: 900px; width: 100%;">
                <div class="mdl-cell mdl-cell--12-col banner imagefill">
                    <img src="splash/images/beerpong.jpg" alt="" class="bannerImage" />
                    <div class="banner-overlay"></div>
                    <div class=" mdl-cell mdl-cell--12-col banner-content">
                        <h1>Fun Games for Every Party</h1>
                        <a href="join/" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored">
                            Join A Game
                        </a>
                    </div>
                </div>
            </div>
            <footer class="mdl-mini-footer">
                <div class="md-mini-footer_left-section">
                    <h4 class="mdl-logo" style="margin: 0 0 15px 0;" style="text-align: right;">
                        <i class="fa fa-glass" aria-hidden="true" style="font-size: 20px; color: #8bc34a; position: relative; top: -2px;"></i> Party Games
                    </h4>
                    <div class="clear"></div>
                    <a href="join/" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
                        Join A Game
                    </a>
<!--                    <ul class="mdl-mini-footer__link-list" style="text-align: right;">-->
<!--                        <li><a href="games/">All Games</a></li>-->
<!--                    </ul>-->
                </div>
                <div class="mdl-mini-footer__right-section" style="text-align: left;">
                    <div class="clear"></div>
                    <br>
                    <div class="mdl-logo" style="float: right;">We truly believe that your drink is not empty enough.</div>
                    <div class="clear"></div>
                    <br>
                    <ul class="mdl-mini-footer__link-list" style="float: right; width: 100%;">
                        <li class="mdl-typography--font-light">Copyright © <?=date("Y")?> Haden & Justin</li>
                    </ul>
                </div>
            </footer>
        </div>
    </div>
<?php
require_once(ROOT."/splash/footer.php");
?>