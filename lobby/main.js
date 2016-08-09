/**
 * Created by handshiles on 2016-07-17.
 */
(function() {
    var dialog = document.querySelector('dialog.error');
    if(dialog != null) {
        if (!dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }
        dialog.showModal();
        dialog.querySelector('.close').addEventListener('click', function() {
            dialog.close();
        });
    }

    setTimeout(function() {
        $('.mdl-textfield.is-invalid').each(function(){$(this).removeClass('is-invalid')});
    }, 400);

    updatePlayers();
    // updateChat();

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
})();

function updatePlayers() {
    $.ajax({
        url: "players.php",
        type: 'get',
        dataType: 'html',
        success: function(data) {
            $('#players').html(data);
        },
        error: function(xhr, status) {
            $('body').append('<dialog class="mdl-dialog"><h4 class="mdl-dialog__title">Oops!</h4><div class="mdl-dialog__content"><p style="color: #ccc; font-size: 8px;">You done did it.</p><p>There was an error updating the player list.</p></div><div class="mdl-dialog__actions"><button type="button" class="mdl-button close">OK</button></div></dialog>')
        }
    });
}

window.setInterval(updatePlayers, 5000);

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

function checkGame() {
    $.ajax({
        url: "check-game.php",
        type: "get",
        dataType: "json",
        success: function(result) {
            if(result.getGame == false) {
                location.reload();
            }
        },
        error(xhr, status, error) {
            console.log(error);
        }
    });
}

// window.setInterval(updateChat, 2000);

window.setInterval(checkGame, 3000);