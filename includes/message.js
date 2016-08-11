//list of all messages and icons for website
var messages = {
    "game-deleted":"Sorry, your game was deleted",
    "game-name-in-use":"Someone is already using that name!",
    "game-not-found":"Game could not be found, do you have the right code?",
    "game-empty-name":"Please enter a name",
    "game-drink-or-dare-empty-dare":"Dare cannot be empty",
    "game-drink-or-dare-stolen":"Someone stole your dare! Pick another",
    "game-drink-or-dare-submitted-dare":"Dare created successfully!",
    "game-drink-or-dare-chosen-dare":"You have chosen your dare. Face the concequences.",
    "game-drink-or-dare-already-picked-card":"You have already chosen a card. Please wait.",
    "game-drink-or-dare-vote-cast-success":"Your vote has been sent",
    "game-drink-or-dare-vote-cast-change":"Your vote has been changed",
    "game-drink-or-dare-vote-cast-failure":"Your vote could not be cast. Something went wrong."
};
var icons = {
    "warning":'<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',
    "danger":'<i class="fa fa-exclamation-circle" aria-hidden="true"></i>',
    "success":'<i class="fa fa-smile-o" aria-hidden="true"></i>',
    "info":'<i class="fa fa-info-circle" aria-hidden="true"></i>',
    "times":'<i class="fa fa-times" style="color:red" aria-hidden="true"></i>',
    "check":'<i class="fa fa-check" style="color:green" aria-hidden="true"></i>',
    "spinner":'<i class="fa fa-refresh fa-spin fa-fw"></i>'
}

//on document load
$(function() {

    //check for server messages
    if (srvmsg != "") {
        console.log("Checking for messages...");

        srvmsg = JSON.parse(srvmsg);
        var maxTries = 20;

        var waitForMDL = setInterval(function () {

            console.log(typeof MaterialSnackbar);
            //set a timeout to wait for MDL to load
            if (typeof MaterialSnackbar != "undefined") {

                //loop through each server message
                for (var message of srvmsg) {

                    //send the message request
                    if (message.msg != "") {
                        msg(message.popup, message.ele, message.msg, message.title, message.type, message.icon, (message.hide == false ? false : true), message.delay);
                    }
                }

                //clear the timeout
                clearInterval(waitForMDL);
            }

            //if MDL hasnt loaded in 20 tries, clean interval
            maxTries--;
            if (maxTries <= 0) {
                clearInterval(waitForMDL);
            }
        }, 100);
    }
});

//display an error on the webpage
function msg(popup, ele, msgid, title, type, icon, hide, delay) {

    //check for type
    // if (type != "warning" && type != "info" && type != "danger" && type != "success") {
    //     type = "info";
    // }

    //check for undefined parameters
    delay = (parseInt(delay) ? delay : 3000);
    //hide = !(hide == false);
    popup = (popup == "snackbar" || popup == "dialog" ? popup : "snackbar");

    //default element example: #info-msg
    ele = (ele == false || typeof ele == "undefined" ? 'snackbar-message' : ele);

    console.log("Message request - Popup: " + popup + ", Ele: " + ele + ", Msgid: " + msgid + ", Type: " + type + ", Icon: " + icon + ", Hide: " + hide);

    //if (ele = document.getElementById(ele)) {
        //check for empty message
        if (msgid != "") {

            if (messages[msgid]) {
                var message = messages[msgid];
            } else {
                var message = msgid;
            }

            if (popup == "snackbar") {

                //MDL
                //'use strict';
                var snackbarContainer = document.querySelector('#'+ele);
                // var showSnackbarButton = document.querySelector('#demo-show-snackbar');
                // var handler = function (event) {
                //     showSnackbarButton.style.backgroundColor = '';
                // };
                //showSnackbarButton.addEventListener('click', function () {
                    //'use strict';
                    // showSnackbarButton.style.backgroundColor = '#' +
                    //     Math.floor(Math.random() * 0xFFFFFF).toString(16);
                    var data = {
                        message: message,
                        timeout: delay,
                        //actionHandler: handler,
                        //actionText: 'Undo'
                    };
                    snackbarContainer.MaterialSnackbar.showSnackbar(data);
                //});

            } else if (popup == "dialog") {

                var dialog = document.querySelector('dialog');
                //var showDialogButton = document.querySelector('#show-dialog');
                if (! dialog.showModal) {
                    dialogPolyfill.registerDialog(dialog);
                }

                if (typeof title != "undefined") {
                    document.getElementById('dialog-title').innerHTML = title;
                }
                document.getElementById('dialog-text').innerHTML = message;
                //showDialogButton.addEventListener('click', function() {
                    dialog.showModal();
               // });

                dialog.querySelector('.close').addEventListener('click', function() {
                    dialog.close();
                });
            }

            //show message
            // $(ele).attr('class', 'alert alert-'+type);
            // if (icons[icon]) {
            //     $(ele).html(icons[icon] + " " + messages[msgid]);
            // } else {
            //     $(ele).html(icons[type] + " " + messages[msgid]);
            // }
            //$(ele).slideDown();

            //check for hiding of message
            // if (hide) {
            //     switch (type) {
            //         case "info":
            //             var infoclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(infoclear);
            //             }, delay);
            //             break;
            //         case "danger":
            //             var dangerclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(dangerclear);
            //             }, delay);
            //             break;
            //         case "warning":
            //             var warningclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(warningclear);
            //             }, delay);
            //             break;
            //         case "success":
            //             var successclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(successclear);
            //             }, delay);
            //             break;
            //     }
            // } // end of check for hide
        } else {
            console.log("Message could not be found or message empty");
        }
    // } else {
    //     console.log("Element could not be found");
    // }
}

//hide a msg element
function msgHide(ele) {
    var isDefault = true;

    //check for default message
    if (ele == false || ele == "undefined") {
        ele = 'msg';
    } else {
        isDefault = false;
    }

    if (ele = document.getElementById(ele)) {
        $(ele).slideUp();
        //if default, hide all children elements of parent 'msg'
        if (isDefault) {
            $(ele).children().each(function(index) {
                $(this).hide();
            });
        }
    }
}
