/**
 * Created by handshiles on 2016-07-17.
 */

var checkGameInterval;
var updatePlayersInterval;

/*
 * on document load
 */
$(function() {

    //update players list on document load and on an inerval of 3 seconds
    updatePlayers();
    updatePlayersInterval = window.setInterval(updatePlayers, 3000);

    //currently removed chat
    // updateChat();
    // window.setInterval(updateChat, 2000);

    //set an interval for checking game status every 3 seconds
    checkGameInterval = window.setInterval(checkGame, 3000);


    //RESET CSS FOR SOMETHING
    setTimeout(function() {
        $('.mdl-textfield.is-invalid').each(function(){
            $(this).removeClass('is-invalid')
        });
    }, 400);

    $('#sendMessage').click(function(e) {
        e.preventDefault();
        var $chatMessageForm = $('#chatMessageForm');
        $.ajax({
            type: "POST",
            url: $chatMessageForm.attr('action'),
            data: { "message": $('#messageText').val() },
            success: function(response) {
                $('#messageText').val("");
            },
            error: function(xhr, msg) {
                console.log(msg);
            }
        } );
    });
}); //end of document laod


//function to update players html
function updatePlayers() {
    $.ajax({
        url: "players.php",
        type: 'get',
        dataType: 'html',
        success: function(data) {
            //update html
            $('#players').html(data);
        },
        error: function(xhr, status) {
            msg("popup", false, "lobby-players-not-loaded");
            clearInterval(updatePlayersInterval);
        }
    });
}

//funtion to check the status of the game
function checkGame() {
    $.ajax({
        url: "check-game.php",
        type: "get",
        success: function(result) {
            console.log(result);

            //check if the game has started
            if (!result.exists) {
                location.reload();
            } else if (result.forceReload) {
                location.reload();
            }
        },
        error(xhr, status, error) {
            console.log(error);
            msg("dialog", false, "lobby-check-game-not-working");
            clearInterval(checkGameInterval);
        }
    });
}


// function updateChat() {
//     $.ajax({
//         url: "chat.php",
//         type: 'get',
//         dataType: 'html',
//         success: function(data) {
//             $('#chatMessages').html(data);
//         },
//         error: function(xhr, status) {
//             $('body').append('<dialog class="mdl-dialog"><h4 class="mdl-dialog__title">Oops!</h4><div class="mdl-dialog__content"><p style="color: #ccc; font-size: 8px;">We done did it.</p><p>There was an error updating the chat room.</p></div><div class="mdl-dialog__actions"><button type="button" class="mdl-button close">OK</button></div></dialog>')
//         }
//     });
// }