        <script src="../bower_components/jquery/dist/jquery.min.js"></script>
        <script defer src="../bower_components/material-design-lite/material.min.js"></script>
        <script>
            (function() {
                var dialog = document.querySelector('dialog.error');
                if(dialog != null) {
                    if (!dialog.showModal) {
                        dialogPolyfill.registerDialog(dialog);
                    }
                    dialog.showModal();
                    dialog.querySelector('.close').addEventListener('click', function() {
                        dialog.close();
                        if(dialog.code == 1) {
                            location.reload();
                        }
                    });
                }

                var avatars = document.querySelector('dialog.avatars');
                var showAvatarButton = document.querySelector('#show-avatars');
                if(avatars != null) {
                    if (! avatars.showModal) {
                        dialogPolyfill.registerDialog(avatars);
                    }
                    showAvatarButton.addEventListener('click', function() {
                        avatars.showModal();
                    });
                    avatars.querySelector('.close').addEventListener('click', function() {
                        avatars.close();
                    });

                    setTimeout(function() {
                        $('.mdl-textfield.is-invalid').each(function(){$(this).removeClass('is-invalid')});
                        if(avatars.hasAttribute('open')) {
                            avatars.close();
                        }
                    }, 400);

                    $('.avatar-image').each(function() {
                        $(this).click(function(e) {
                            //Clear previous selection(s)
                            $('.avatar-image').each(function() {
                                $(this).removeClass('selected');
                            });

                            var $avatarImage = $(e.target);
                            var $avatar = $(e.target).parent('.avatar-image');
                            var imageUrl = $avatarImage.attr('src');
                            $avatar.addClass('selected');
                            $('#avatar-picture').val(imageUrl);
                            $('.select-avatar .avatar a img').attr('src', imageUrl);
                        });
                    });
                }
            })();
        </script>
    </body>
</html>