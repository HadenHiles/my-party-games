/**
 * Created by handshiles on 2016-08-10.
 */
$(function() {
    setTimeout(function() {
        //change the leaderboard icon
        $('.mdl-layout__drawer-button i').removeAttr("class");
        $('.mdl-layout__drawer-button i').html("");
        $('.mdl-layout__drawer-button i').addClass("fa").addClass("fa-trophy");
        $('i.fa.fa-trophy').css({"color": "#FFD700"});
    }, 1000);
});