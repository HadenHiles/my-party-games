        <script src="../bower_components/jquery/dist/jquery.min.js"></script>
        <script defer src="https://code.getmdl.io/1.1.3/material.min.js"></script>
        <script>
            (function() {
                var dialog = document.querySelector('dialog');
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