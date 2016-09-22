/**
 * Created by handshiles on 2016-07-17.
 */
$('.imagefill').imagefill();

var parentSearch = document.getElementById('search-results');

$(function() {

    var checkFocus = setInterval(function() {

        //check to see if the search field has focus
        var isFocus = $('#search-results').parent().hasClass('is-focused');

        if (!isFocus) {
            parentSearch.style.display = 'none';
        } else {
            parentSearch.style.display = 'block';
        }
    }, 250);

    $('#search-results a').on('click', function() { clearInterval(isFocus); });

    //trigger search on keyups
    $('#search-field').on('keyup', function(e) {

        search(e.currentTarget.value);
    });

    search(document.getElementById('search-field').value);
});

function search(text) {

    var regex = /^[A-Za-z0-9_.]*$/;

    //check for only alphanumeric characters
    if (regex.exec(text)) {

        if (text.length >= 3) {

            $.ajax({
                url:"/splash/get-search-results.php",
                method:"POST",
                data:{"text":text}
            }).done(function(result) {
                console.log(result);

                parentSearch.innerHTML = result;
                componentHandler.upgradeDom();
            });
        } else {
            parentSearch.innerHTML = '';
        }
    } // end of regex
}