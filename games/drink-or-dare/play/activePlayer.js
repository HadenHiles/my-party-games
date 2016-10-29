$(function() {
    if($("#activePlayer").length == 1) {
        setInterval(function() {
            $.ajax({
                url:"/games/drink-or-dare/play/get-active-player.php",
                method:"GET"
            }).done(function(result) {
                $("#activePlayer").html(result);
            });
        }, 1500);
    }
});