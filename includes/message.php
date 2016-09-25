<?php
//this is a test on how php messages should be displayed
//$msg[] = array("msg" => "game-deleted", "title" => "my title", "popup" => "snackbar");
?>

<!--<button id="demo-show-snackbar" class="mdl-button mdl-js-button mdl-button--raised" type="button">Show Snackbar</button>-->
<div id="snackbar-message" class="mdl-js-snackbar mdl-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button class="mdl-snackbar__action" type="button"></button>
</div>

<dialog class="mdl-dialog msg">
    <h4 class="mdl-dialog__title" id="dialog-title">Oops!</h4>
    <div class="mdl-dialog__content">
        <p id="dialog-text">
            Allowing us to collect data will let us get you the information you want faster.
        </p>
    </div>
    <div class="mdl-dialog__actions">
        <button type="button" class="mdl-button close">Okay</button>
    </div>
</dialog>

<script type="text/javascript">
    var srvmsg = '<?php echo (!empty($msg) && is_array($msg) ? json_encode($msg) : ''); ?>';
</script>

<script src="/includes/message.js"></script>