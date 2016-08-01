        <script src="../../bower_components/jquery/dist/jquery.min.js"></script>
        <script defer src="../../bower_components/material-design-lite/material.min.js"></script>
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
                    });
                }
            })();
        </script>
    </body>
</html>