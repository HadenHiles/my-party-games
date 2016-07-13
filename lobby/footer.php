        <script src="../bower_components/jquery/dist/jquery.min.js"></script>
        <script defer src="../bower_components/material-design-lite/material.min.js"></script>
        <script>
            (function() {
                var dialog = document.querySelector('dialog#error');
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
        </script>
    </body>
</html>