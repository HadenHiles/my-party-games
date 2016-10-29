<div>
    <style>
        @media screen and (max-width: 700px) {
            .halloween-image {
                width: 50% !important;
            }
        }
    </style>
    <img src="https://a2ua.com/ghost/ghost-002.jpg" class="halloween-image" style="
        pointer-events: none;
        position: fixed;
        top: 50%;
        left: 50%;
        /* right: 0; */
        width: 20%;
        /* bottom: 0; */
        z-index: 10;
        opacity: 0;
    " id="testerino">
    <img src="/images/web.png" alt="" style="position: fixed; left: -25px; bottom: -25px; z-index: 1; pointer-events: none;" />
</div>


<?php
/**
 * Created by IntelliJ IDEA.
 * User: Justin
 * Date: 9/23/2016
 * Time: 9:27 PM
 */
?>

<script src="/bower_components/material-design-lite/material.min.js"></script>

<!--<script defer src="https://code.getmdl.io/1.1.3/material.min.js"></script>-->

<script src="/3rd-party/imagefill/imagesLoaded.js"></script>
<script src="/3rd-party/imagefill/jquery-imagefill.js"></script>

<script>
$(function() {
    var ghost = $('#testerino');
    var toggle = false;
    var op = 0;

    var images = [
        "https://img.fireden.net/v/image/1450/88/1450884080160.png",
        "http://67.media.tumblr.com/4d92609b430dc10cc1dbe648b35aa7ad/tumblr_nmxi5gL0jL1sfb87ho1_500.png",
        "https://www.shareicon.net/horror-knife-terror-spooky-head-halloween-scary-costume-fear-828598/data/128x128/2016/10/18/845341_avatar_512x512.png",
        "https://www.shareicon.net/horror-knife-terror-spooky-head-halloween-scary-costume-fear-828598/data/128x128/2016/10/18/845346_avatar_512x512.png",
        "https://holybeeofephesus.files.wordpress.com/2015/10/the_raven1.png",
        "http://i.imgur.com/lz7lLow.png",
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://gallery.yopriceville.com/var/resizes/Free-Clipart-Pictures/Halloween-PNG-Pictures/Halloween_Funny_Witch_PNG_Clipart.png?m=1376604000",
        "http://vignette2.wikia.nocookie.net/trollpasta/images/3/3d/Bloody_Mickey_Mouse_Render.png/revision/latest?cb=20150103154257",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935",
        "http://images.clipartpanda.com/halloween-skeleton-clipart-117-Happy-Skeleton-Free-Halloween-Vector-Clipart-Illustration.png",
        "https://www.clipartsgram.com/image/1562746355-zombie-halloween-clip-art-2.png",
        "http://www.halloweenasylum.com/assets/images/GhoulishProductions/26446.png",
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935",
        "http://stockpictures.io/wp-content/uploads/2016/06/48053-masked-man-evil-scary-spooky-mystery-demon-creepy-darkness-mysterious-terrify-gothic-frightening-terrified-satanic-demonic-free-vector-graphics-free-illustrations-free-images-royalty-free-768x601.png",
        "http://cdn.akamai.steamstatic.com/steam/apps/372210/extras/bat.png?t=1460138974",
        "https://staticdelivery.nexusmods.com/mods/1151/images/1917-0-1448130696.png",
        "http://images.vectorhq.com/images/previews/b42/gravestone-psd-436814.png",
        "http://vignette3.wikia.nocookie.net/torchlight/images/f/f5/Werewolf.png/revision/latest?cb=20130122032955",
        "http://gallery.yopriceville.com/var/albums/Free-Clipart-Pictures/Halloween-PNG-Pictures/Halloween_Spooky_Tree_PNG_Clipart_Image.png?m=1444774897",
        "http://images.clipartpanda.com/bat-clipart-bat-gallery-free-clipart-pictures.png",
        "http://www.pngall.com/wp-content/uploads/2016/03/Spider-PNG.png",
        "http://www.clipartlord.com/wp-content/uploads/2013/06/mummy4.png",
        "http://hypnoticowl.com/wordpress/wp-content/uploads/2014/03/Wizard.png",
        "https://img.joke.co.uk/images/products/jmw-v3/large/67508.png",
        "http://www.lovethisgif.com/uploaded_images/104222-3d-Gifs-Blinking-Eyes.gif",
        "https://sites.google.com/a/clipartonline.net/disney-halloween/_/rsrc/1468757607839/mickey-mouse-halloween-clip-art/Disney%20-%20Halloween-MickeyMouse-3.png?height=320&width=320",
        "http://cliparts.co/cliparts/yik/A5k/yikA5kq6T.png",
        "http://i.imgur.com/EvrkuXB.png",
        "http://vignette2.wikia.nocookie.net/animaljam/images/2/21/LETS_GET_SPOOKY.gif/revision/latest?cb=20150804223039",
        "https://www.google.ca/url?sa=i&rct=j&q=&esrc=s&source=images&cd=&ved=0ahUKEwi6o7Dqw__PAhWDKCYKHS1QBYwQjBwIBA&url=http%3A%2F%2Frs717.pbsrc.com%2Falbums%2Fww173%2Fprestonjjrtr%2FHalloween%2FPirate_bones.gif~c200&bvm=bv.136811127,d.eWE&psig=AFQjCNEw0N599s_MxwiN9_4Xs2fQoROo1A&ust=1477813836505636",
        "https://www.google.ca/url?sa=i&rct=j&q=&esrc=s&source=images&cd=&ved=&url=http%3A%2F%2Fwww.crabapples.net%2Fartwork%2FlittlePirate.gif&bvm=bv.136811127,d.eWE&psig=AFQjCNEw0N599s_MxwiN9_4Xs2fQoROo1A&ust=1477813836505636",
        "http://www.partecipiamo.it/befana/befana/befana_00111.gif",
        "https://media.tenor.co/images/64b88333b0e1c4f5a3a11be05b6cbfff/raw",
        "https://lh3.googleusercontent.com/l_pr4qOF-Ba-8BQw9PxI2VQyGwSVopDbQIDpt3ArxgvOltDVRLVzW8BjtnCVSYN4vH8=h900",
        "http://www.drodd.com/images8/halloween/halloween-clipart-file19.gif",
        "http://worldartsme.com/images/haunted-castle-clipart-1.jpg",
        "http://www.officialpsds.com/images/thumbs/Freddy-Krueger-psd106761.png",
        "http://www.easydefenseproducts.com/media/wysiwyg/Vampire-PIX.png",
        "https://upload.wikimedia.org/wikipedia/commons/9/92/The_death.png",
        "https://writerswin.com/wp-content/uploads/2015/10/author-death-scenes.png",
        "http://icons.iconarchive.com/icons/iconshock/halloween/256/scream-icon.png",
        "http://icons.iconarchive.com/icons/3xhumed/mega-games-pack-36/256/SAW-TheGame-3-icon.png",
        "https://www.google.ca/search?biw=1600&bih=721&tbs=ic%3Atrans&tbm=isch&sa=1&q=horror&oq=horror&gs_l=img.3...24830.25810.0.25854.0.0.0.0.0.0.0.0..0.0....0...1c.1.64.img..0.0.0.oHptHGcxGx8#imgrc=R0EFidBrtBAnsM%3A",
        "http://www.tirebusiness.com/apps/pbcsi.dll/storyimage/TB/20121024/BLOGS/121029956/H1/0/H1-121029956.jpg&cci_ts=20121024165232&ExactW=320"
    ];

    var index = Math.floor(Math.random() * images.length);
    $(ghost).attr('src', images[index]);
    var wait = Math.floor(Math.random() * 160) + 60;
    var current = 0;

    var inte = setInterval(function() {

        if (current < wait) {
            current++;
            return;
        }

        if (!toggle) {
            op+=.02;
        } else {
            op-=.03;
        }

        if (op >= .9) {
            toggle = true;
        } else if ( op <= 0) {
            toggle = false;
            index = Math.floor(Math.random() * images.length);
            $(ghost).attr('src', images[index]);
            $(ghost).css("top", Math.floor((Math.random() * 65) + 10)+"%");
            $(ghost).css("left", Math.floor((Math.random() * 70) + 10)+"%");
            current = 0;
            wait = Math.floor(Math.random() * 20) + 2;
        }
        $(ghost).css("opacity", op);
    }, 50);
});
</script>

<?php
require_once(ROOT.'/includes/message.php');
?>