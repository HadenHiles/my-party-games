$(document).ready(function() {
    updateLeaderboard();
});

setInterval(function() {
    updateLeaderboard();
}, 3000);

function updateLeaderboard() {
    $.ajax({
        url:"/leaderboard/leaderboard.php",
        method:"get"
    }).done(function(result) {
        $('.loadLeaderboard').each(function() {
            $(this).html(result);
        });
    });
}
