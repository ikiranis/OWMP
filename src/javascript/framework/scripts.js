//
// File: scripts.js
// Created by Yiannis Kiranis <rocean74@gmail.com>
// http://www.apps4net.eu
// Date: 20/05/16
// Time: 19:44
//
// Framework javascript controls και functions
//
//


// extension στην jquery. Προσθέτει την addClassDelay. π.χ. $('div').addClassDelay('somedivclass',3000)
// Προσθέτει μια class και την αφερεί μετά από λίγο
$.fn.addClassDelay = function(className,delay) {
    var $addClassDelayElement = $(this), $addClassName = className;
    $addClassDelayElement.addClass($addClassName);
    setTimeout(function(){
        $addClassDelayElement.removeClass($addClassName);
    },delay);
};


// extension του jquery που επιστρέφει την λίστα των κλάσεων ενός element, σε array
// π.χ myClasses= $("#AlertID"+id).find('input[name=delete_alert]').classes();
!(function ($) {
    $.fn.classes = function (callback) {
        var classes = [];
        $.each(this, function (i, v) {
            var splitClassName = v.className.split(/\s+/);
            for (var j in splitClassName) {
                var className = splitClassName[j];
                if (-1 === classes.indexOf(className)) {
                    classes.push(className);
                }
            }
        });
        if ('function' === typeof callback) {
            for (var i in classes) {
                callback(classes[i]);
            }
        }
        return classes;
    };
})(jQuery);

// extension του jquery για να τρέχει ajax requests σε queue. Παράδειγμα στην function downloadYouTube()
(function($) {
    // Empty object, we are going to use this as our Queue
    var ajaxQueue = $({});

    $.ajaxQueue = function(ajaxOpts) {
        // hold the original complete function
        var oldComplete = ajaxOpts.complete;

        // queue our ajax request
        ajaxQueue.queue(function(next) {

            // create a complete callback to fire the next event in the queue
            ajaxOpts.complete = function() {
                // fire the original complete if it was there
                if (oldComplete) oldComplete.apply(this, arguments);
                next(); // run the next query in the queue
            };

            // run the query
            $.ajax(ajaxOpts);
        });
    };

})(jQuery);

function DisplayMessage (element, error) {
    $(element).text(error);
    $(element).stop().show(0).delay(5000).hide(0);
}

// Εισαγωγή αρχικού χρήστη admin
function registerUser() {
    username = $("#RegisterUserWindow").find('input[name="username"]').val();
    email = $("#RegisterUserWindow").find('input[name="email"]').val();
    password = $("#RegisterUserWindow").find('input[name="password"]').val();
    repeat_password = $("#RegisterUserWindow").find('input[name="repeat_password"]').val();

    if ($('#RegisterForm').valid()) {

        callFile = AJAX_path+"framework/registerUser.php?username=" + username + "&password=" + password + "&email=" + email;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            if (result['success'] == true) {

                document.querySelector('#RegisterForm #register').style.backgroundColor='green';
                $('#RegisterForm #register').prop('disabled', true);
                window.location.href = "";
            }
            else  DisplayMessage('.alert_error',result['message']);

        });

    }

}


// Έλεγχος του login
function login() {
    username = $("#LoginWindow").find('input[name="username"]').val();
    password = $("#LoginWindow").find('input[name="password"]').val();
    if ($("#LoginWindow").find('input[name="SavePassword"]').is(":checked"))
        SavePassword = true;
    else SavePassword = false;

    if ($('#LoginForm').valid()) {


        callFile = AJAX_path+"framework/checkLogin.php?username=" + username + "&password=" + password + "&SavePassword=" + SavePassword;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            if (result['success'] == true) {
                // TODO να αλλάζει χρώμα προσθέτοντας κλάση css καλύτερα
                // TODO δεν δουλεύει σε safari
                document.querySelector('#LoginForm #submit').style.backgroundColor='green';
                $('#LoginForm #submit').prop('disabled', true);
                window.location.href = "";
            }
            else  DisplayMessage('.alert_error',result['message']);

        });

    }

}

// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table alerts, ή εισάγει νέα εγγραφή
function updateUser(id) {
    username=$("#UserID"+id).find('input[name="theUsername"]').val();
    email=$("#UserID"+id).find('input[name="email"]').val();
    password=$("#UserID"+id).find('input[name="password"]').val();
    repeat_password=$("#UserID"+id).find('input[name="repeat_password"]').val();
    usergroup=$("#UserID"+id).find('select[name="usergroup"]').val();
    fname=$("#UserID"+id).find('input[name="fname"]').val();
    lname=$("#UserID"+id).find('input[name="lname"]').val();

    if (password=='') changepass=false;
    else changepass=true;

    if(changepass)
        var callFile=AJAX_path+"framework/updateUser.php?id="+id+"&username="+username+"&email="+email+"&password="+password+
            "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;
    else var callFile=AJAX_path+"framework/updateUser.php?id="+id+"&username="+username+"&email="+email+
        "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;

    if ( $('#users_formID'+id).valid() && password==repeat_password ) {

        $.get(callFile, function (data) {

            if (data.success == true) {
                if (id == 0) {   // αν έχει γίνει εισαγωγή νέας εγγρσφής, αλλάζει τα ονόματα των elements σχετικά
                    UserKeyPressed = false;
                    LastInserted = data.lastInserted;
                    $("#UserID0").prop('id', 'UserID' + LastInserted);
                    $("#UserID" + LastInserted).find('form').prop('id','users_formID'+ LastInserted);
                    $("#UserID" + LastInserted).find('input[name="update_user"]')
                        .attr("onclick", "updateUser(" + LastInserted + ")");
                    $("#UserID" + LastInserted).find('input[name="delete_user"]')
                        .attr("onclick", "deleteUser(" + LastInserted + ")");
                    $("#UserID" + LastInserted).find('input[id^="messageUserID"]').prop('id', 'messageUserID' + LastInserted);
                    $("#messageUserID" + LastInserted).addClassDelay("success", 3000);
                }
                else $("#messageUserID" + id).addClassDelay("success", 3000);
            }
            else if(data.UserExists) {
                $("#messageUserID" + id).addClassDelay("failure", 3000);

                DisplayMessage('.alert_error', error1+' '+username+' '+error2);
            } else $("#messageUserID" + id).addClassDelay("failure", 3000);

        }, "json");
    }

}

// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table options, ή εισάγει νέα εγγραφή
function updateOption(id) {
    option_name=$("#OptionID"+id).find('input[name="option_name"]').val();
    option_value=$("#OptionID"+id).find('input[name="option_value"]').val();

    var callFile=AJAX_path+"framework/updateOption.php?id="+id+"&option_name="+option_name+"&option_value="+encodeURIComponent(option_value);

    if ($('#options_formID'+id).valid()) {
        $.get(callFile, function (data) {
            if (data.success == 'true') {

                $("#messageOptionID" + id).addClassDelay("success", 3000);
            }
            else $("#messageOptionID" + id).addClassDelay("failure", 3000);
        }, "json");
    }

}



// Σβήνει την εγγραφή στο user, user_details, salts
function deleteUser(id) {
    var callFile=AJAX_path+"framework/deleteUser.php?id="+id;

    $.get( callFile, function( data ) {
        console.log(data.success);
        if(data.success=='true') {

            $("#messageUserID"+id).addClassDelay("success",3000);

            myClasses= $("#UserID"+id).find('input[name=delete_user]').classes();   // Παίρνει τις κλάσεις του delete_alert

            if(!myClasses[2])   // Αν δεν έχει κλάση dontdelete σβήνει το div
                $("#UserID"+id).remove();
            else {   // αλλιώς καθαρίζει μόνο τα πεδία
                $("#UserID"+id).find('input').val('');   // clear field values
                $("#UserID"+id).prop('id','UserID0');
                $("#UserID0").find('form').prop('id','users_formID0');
                $("#UserID0").find('input[name="email"]').val('');
                $("#UserID0").find('input[name="fname"]').val('');
                $("#UserID0").find('input[name="lname"]').val('');
                $("#UserID0").find('input[name="password"]').prop('required',true).prop('id','password0');
                $("#UserID0").find('input[name="repeat_password"]').prop('required',true).prop('id','0');
                $("#UserID0").find('input[id^="messageUserID"]').text('').prop('id','messageUserID0');
                // αλλάζει την function στο button
                $("#UserID0").find('input[name="update_user"]').attr("onclick", "updateUser(0)");
                $("#UserID0").find('input[name="delete_user"]').attr("onclick", "deleteUser(0)");


                $('#users_formID0').validate({ // initialize the plugin
                    errorElement: 'div'
                });

            }


        }
        else $("#messageUserID"+id).addClassDelay("failure",3000);
    }, "json" );

}


// Εισάγει νέα div γραμμή αντιγράφοντας την τελευταία και μηδενίζοντας τις τιμές που είχε η τελευταία
function insertUser() {
    if(!UserKeyPressed) {

        // clone last div row
        $('div[id^="UserID"]:last').clone().insertAfter('div[id^="UserID"]:last').prop('id','UserID0');
        $("#UserID0").find('input[name="theUsername"]').val(''); // clear field values
        $("#UserID0").find('form').prop('id','users_formID0');
        $("#UserID0").find('input[name="email"]').val('');
        $("#UserID0").find('input[name="fname"]').val('');
        $("#UserID0").find('input[name="lname"]').val('');
        $("#UserID0").find('input[name="password"]').prop('required',true).prop('id','password0');
        $("#UserID0").find('input[name="repeat_password"]').prop('required',true).prop('id','0');
        $("#UserID0").find('input[id^="messageUserID"]').text('').removeClass('success').prop('id','messageUserID0');
        // αλλάζει την function στο button
        $("#UserID0").find('input[name="update_user"]').attr("onclick", "updateUser(0)");
        $("#UserID0").find('input[name="delete_user"]').attr("onclick", "deleteUser(0)");
        UserKeyPressed=true;

        $('#users_formID0').validate({ // initialize the plugin
            errorElement: 'div'
        });

    }
}

// μετράει τα πεδία ενός json object
function countjson(obj) {
    var count=0;
    for(var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            ++count;
        }
    }
    return count;
}

// Μετατρέπει τα δευτερόλεπτα σε "ανθρώπινα" λεπτά και δευτερόλεπτα. Επιστρέφει τιμές σε array (minutes, seconds)
function seconds2MinutesAndSeconds(timeInSeconds) {
    timeInMinutes=parseInt(timeInSeconds/60);
    newTimeInSeconds=parseInt(timeInSeconds%60);

    if(timeInMinutes<10) timeInMinutes='0'+timeInMinutes.toString();
    if(newTimeInSeconds<10) newTimeInSeconds='0'+newTimeInSeconds.toString();

    timeArray= {  // Μετατροπή σε array
            'minutes': timeInMinutes,
            'seconds': newTimeInSeconds
        }

    return timeArray;
}

// Προσθέτει το 0 μπροστά από τον αριθμό όταν είναι κάτω από το 10
function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

// Επιστρέφει την τρέχουσα ώρα σε string και το εμφανίζει στο element name
function getTime(name) {
    var myTime = new Date();

    var curTime=addZero(myTime.getHours())+':'+
        addZero(myTime.getMinutes())+':'+
        addZero(myTime.getSeconds());

    $(name).text(curTime);
}

// Διαβάζει τις τιμές των search fields και τις αποθηκεύει στο GlobalSearchArray
function readSearchFields(numberOfFields) {
    for(var i=1;i<=numberOfFields;i++){
        GlobalSearchArray[i]= {
            'search_field': $('#search_field' + i).val(),
            'search_text': $('#search_text' + i).val(),
            'search_operator': $('#search_operator' + i).val(),
            'search_equality': $('#search_equality' + i).val()
        }
    }
}

// Γράφει στην φόρμα τις τιμές των search fields που ήταν αποθηκευμένες στο GlobalSearchArray
function writeSearchFields(numberOfFields) {
    for(var i=1;i<=numberOfFields;i++){
        $('#search_field' + i).val(GlobalSearchArray[i]['search_field']);
        $('#search_text' + i).val(GlobalSearchArray[i]['search_text']);
        $('#search_operator' + i).val(GlobalSearchArray[i]['search_operator']);
        $('#search_equality' + i).val(GlobalSearchArray[i]['search_equality']);
    }
}

// Δημιουργεί ένα cookie
function createCookie(name, value, minutes) {
    var expires;
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes));
        expires = minutes;
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

// Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου με ajax
function DisplayWindow(page, offset, step) {
    // console.log(curNavItem+ ' '+ NavLength);
    callFile=AJAX_path+"framework/displayWindow.php?page="+page+"&offset="+offset+"&step="+step+'&tabID='+tabID;

    // Αν target σελίδα δεν είναι η 1
    if(page!==1) {

        // Αν το #search δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!$('#search').length==0) {

            // διαβάζουμε τις τιμές των search fields
            readSearchFields(getNumberOfSearchRows());

            // αντιγράφουμε τον html κώδικα που βρίσκεται μέσα στο #search, στην μεταβλητή SearchHTML
            SearchHTML = $('#search').html();
        }

        // Αν το #ChooseMediaKind δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!$('#ChooseMediaKind').length==0) {
            MediaKindChosen=document.querySelector('#ChooseMediaKind select[name=mediakind]').value;
        }

        // Αν το #playlist_content δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!$('#playlist_content').length==0)
        // αντιγράφουμε τον html κώδικα που βρίσκεται μέσα στο #playlist_content, στην μεταβλητή PlaylistContainerHTML
            PlaylistContainerHTML = $('#playlist_content').html();

    }


    if(page!==CurrentPage) {
        // όταν ανοίγει το section article
        $('section article').load(callFile, function () {

            // Αν εμφανίζουμε την σελίδα 1
            if (page === 1) {

                // εμφανίζουμε τις μεταβλητές που έχουμε σώσει στα αντίστοιχα divs
                $('#search').html(SearchHTML);
                writeSearchFields(getNumberOfSearchRows());
                document.querySelector('#ChooseMediaKind select[name=mediakind]').value = MediaKindChosen;
                $('#playlist_content').html(PlaylistContainerHTML);
                checkSearchFieldChanges();  // επανεκίννηση του έλεγχου αλλαγών στα search fields
                checkFormsFocus();
            }

            CurrentPage=page;

            for (var i = 1; i <= NavLength; i++)   // Κάνει όλα τα nav πεδία inactive
                $('#navID' + i).removeClass('active');

            $('#navID' + page).addClass('active');   // κάνει το page active
        });
    }
}

// Κλείνει το παράθυρο της βοήθειας
function closeHelp()
{
    $('#helpContainer').hide();
}

// Κλείνει το παράθυρο container
function closeWindow(container)
{
    $(container).toggleClass('isVisible isHidden');
}


// Eμφανίζει box με text το helpText
function getHelp(helpText) {
    document.querySelector('#helpText').innerHTML = phrases[helpText];

    $('#helpContainer').show();
}


// *******************************************************************
// functions για έλεγχο των audio output devices. Παίζουν μόνο σε https
function gotDevices(deviceInfos) {
    // window.deviceInfos = deviceInfos;
    for (var i = 0; i !== deviceInfos.length; ++i) {
        var deviceInfo = deviceInfos[i];

        if (deviceInfo.kind === 'audiooutput') {
            console.log('Found audio output device: ' + deviceInfo.deviceId + '  ' + deviceInfo.label);
        }
    }
}

function errorCallback(error) {
    console.log('Error: ', error);
}


// Attach audio output device to video element using device/sink ID.
function attachSinkId(element, sinkId) {
    if (typeof element.sinkId !== 'undefined') {
        element.setSinkId(sinkId)
            .then(function() {
                console.log('Success, audio output device attached: ' + sinkId);
            })
            .catch(function(error) {
                var errorMessage = error;
                if (error.name === 'SecurityError') {
                    errorMessage = 'You need to use HTTPS for selecting audio output ' +
                        'device: ' + error;
                }
                console.error(errorMessage);
                // Jump back to first output device in the list as it's the default.
                audioOutputSelect.selectedIndex = 0;
            });
    } else {
        console.warn('Browser does not support output device selection.');
    }
}

// Κάνει έλεγχο της τρέχουσας έκδοσης της εφαρμογής
function checkCurrentVersion() {
    callFile = ParrotVersionFile;

    $.get(callFile, function (data) {
        // αν η έκδοση της εγκατεστημένης εφαρμογής δεν ταιριάζει με την τρέχουσα, βγάζει μήνυμα
        if(AppVersion!==data.app_version)
            $("#checkCurrentVersion").html(phrases['need_update']+': '+data.app_version+'&nbsp;<a href='+changeLogUrl+'>('+phrases['change_log']+')</a>');
    }, "json");
}

// Στέλνει kill command στην βάση για να σταματήσει το php script που τρέχει
function sendKillCommand() {
    // console.log(runningYoutubeDownload);

    if(!runningYoutubeDownload) {
        callFile = AJAX_path + "framework/sendKillCommand.php";

        $("#killCommand_img").hide();

        $.get(callFile, function (data) {
            if (data.success)
                console.log('Killed');
        }, "json");
    }
    else {
        $("#killCommand_img").hide();
        runningYoutubeDownload=false;

    }

}

// Ψάχνει και καθαρίζει την βάση από προσωρινά tables που δεν χρησιμοποιούνται πλέον
function garbageCollection() {
    callFile=AJAX_path+"framework/garbageCollection.php?tabID="+tabID;


    $.get(callFile, function (data) {
        // if (data.success == true) {
        //
        //
        // }
    }, "json");
}

// Κάνει submit στην αντίστοιχη φόρμα που είναι ανοιχτή
function pressEnterToForm() {

    if(!$('#LoginForm').length==0) {
        $('#LoginForm #submit').click();
    }

    if(!$('#RegisterForm').length==0) {
        $('#RegisterForm #register').click();
    }
}

// Ελέγχει το focus μιας φόρμας
// source code from http://help.dottoro.com/ljmusasd.php
function checkTheFocus(theForm) {
    var form = document.getElementById (theForm);
    if ("onfocusin" in form) {  // Internet Explorer
        // the attachEvent method can also be used in IE9,
        // but we want to use the cross-browser addEventListener method if possible
        if (form.addEventListener) {    // IE from version 9
            form.addEventListener ("focusin", OnFocusInForm, false);
            form.addEventListener ("focusout", OnFocusOutForm, false);
        }
        else {
            if (form.attachEvent) {     // IE before version 9
                form.attachEvent ("onfocusin", OnFocusInForm);
                form.attachEvent ("onfocusout", OnFocusOutForm);
            }
        }
    }
    else {
        if (form.addEventListener) {    // Firefox, Opera, Google Chrome and Safari
            // since Firefox does not support the DOMFocusIn/Out events
            // and we do not want browser detection
            // the focus and blur events are used in all browsers excluding IE
            // capturing listeners, because focus and blur events do not bubble up
            form.addEventListener ("focus", OnFocusInForm, true);
            form.addEventListener ("blur", OnFocusOutForm, true);
        }
    }
}


// Τρέχει τα validates για τις διάφορες φόρμες
function startValidates() {
    $('#LoginForm').validate({ // initialize the plugin
        errorElement: 'span'
    });

    $('#RegisterForm').validate({ // initialize the plugin
        errorElement: 'span',
        rules : {
            repeat_password: {
                equalTo : '[name="password"]'
            }
        }
    });


    // Validate της users form
    $('.users_form').each(function() {  // attach to all form elements on page
        $(this).validate({       // initialize plugin on each form
            errorElement: 'span'
        });
    });


    // Validate της options form
    $('.options_form').each(function() {  // attach to all form elements on page
        $(this).validate({       // initialize plugin on each form
            errorElement: 'span'
        });
    });

    // Έλεγχος αν το repeat password  συμφωνεί με το password
    $('.UsersList').find('input[name=repeat_password]').keyup(function () {
        curEl=eval($(document.activeElement).prop('id'));

        if ($('#password'+curEl).val() === $(this).val()) {
            $(this)[0].setCustomValidity('');

        } else {
            $(this)[0].setCustomValidity(phrases['valid_passwords_must_match']);
        }

    });


    $('#RegisterForm').find('input[name=repeat_password]').keyup(function () {

        if ($('input[name=password]').val() === $(this).val()) {
            $(this)[0].setCustomValidity('');

        } else {
            $(this)[0].setCustomValidity(phrases['valid_passwords_must_match']);
        }

    });
}


// Κάνει το update της εφαρμογής
function startTheUpdate() {
    callFile=AJAX_path+'framework/updateApp.php';

    $.get(callFile, function (data) {

        if (data.success == true) {

            DisplayMessage('.alert_error', 'App Updated');

        }
        else {
            DisplayMessage('.alert_error', 'App Not Updated');
        }

    }, "json");
}

// Δημιουργία a href DOM element και αυτόματο (ή όχι) download
// @param: string fullPath = το πλήρες path μαζί με το filename του αρχείου
// @param: string filename = σκέτο το όνομα του αρχείου
// @param: string hrefText = το κείμενο που θα εμφανιστεί
// @param: Bool autoDownload = true για να αρχίσει να κατεβάζει αυτόματα το αρχείο
// @return: DOM object = To a href που θα εμφανίσει
function getDownloadLink(fullPath, filename, hrefText, autoDownload) {

    var downloadText = document.createElement('a');
    downloadText.href = fullPath;
    downloadText.innerHTML = hrefText;
    downloadText.target = '_blank';
    downloadText.download = filename;

    if(autoDownload) {
        downloadText.click();
    }

    return downloadText;

}

//  Παίρνει backup της βάσης
function startTheBackup() {
    var confirmAnswer=confirm(phrases['sure_to_backup']);

    if (confirmAnswer==true) {

        if(localStorage.syncPressed=='false') {  // Έλεγχος αν δεν έχει πατηθεί ήδη
            localStorage.syncPressed = 'true';

            callFile = AJAX_path + 'framework/backupDatabase.php';

            initProgressAnimation();
            $('#logprogress').show();
            $("#killCommand_img").show();
            document.querySelector('#theProgressBar').value=0;
            $("#theProgressNumber" ).html('');

            // Κοιτάει για το progress κάθε 5 λεπτά και το τυπώνει
            var syncInterval = setInterval(function () {
                checkProgress();
            }, 5000);

            $.ajax({
                url: callFile,
                type: 'GET',
                dataType: "json",
                success: function(data) {
                    if (data.success == true) {

                        DisplayMessage('.alert_error', phrases['backup_success']);

                        // To checkbox για autodownload
                        var autoDownload = document.querySelector('#autoDownloadBackupFile').checked;

                        var path = AJAX_path+"app/serveFile.php?path="+data.fullPath;

                        // Δημιουργία a href element και αυτόματο download
                        var downloadText = getDownloadLink(path, data.filename, path, autoDownload);

                        $('.o-resultsContainer_text').append('<br>');
                        $('.o-resultsContainer_text').append(downloadText);
                        killAnimation();
                        $('#logprogress').hide();
                        localStorage.syncPressed = 'false';
                        $('.syncButton').prop('disabled', false);
                        clearInterval(syncInterval);

                    }
                    else {

                        DisplayMessage('.alert_error', phrases['backup_failure']);

                        killAnimation();
                        $('#logprogress').hide();
                        localStorage.syncPressed = 'false';
                        $('.syncButton').prop('disabled', false);
                        clearInterval(syncInterval);
                    }
                }
            });

        }
    }
}

//  Κάνει restore της βάσης από ένα αρχείο backup
function restoreTheBackup() {
    if(myFile!=='') {
        var confirmAnswer=confirm(phrases['sure_to_restore']);

        if (confirmAnswer==true) {
            if(localStorage.syncPressed=='false') {  // Έλεγχος αν δεν έχει πατηθεί ήδη
                localStorage.syncPressed = 'true';

                callFile = AJAX_path + 'framework/restoreDatabase.php';

                initProgressAnimation();
                $('#logprogress').show();
                $("#killCommand_img").show();
                document.querySelector('#theProgressBar').value=0;
                $("#theProgressNumber" ).html('');

                // Κοιτάει για το progress κάθε 5 λεπτά και το τυπώνει
                var syncInterval = setInterval(function () {
                    checkProgress();
                }, 5000);

                $.ajax({
                    url: callFile,
                    type: 'GET',
                    dataType: "json",
                    success: function(data) {
                        if (data.success == true) {

                            DisplayMessage('.alert_error', phrases['restore_success']);

                            killAnimation();
                            $('#logprogress').hide();
                            localStorage.syncPressed = 'false';
                            $('.syncButton').prop('disabled', false);
                            clearInterval(syncInterval);

                        }
                        else {

                            DisplayMessage('.alert_error', phrases['restore_failure']);

                            killAnimation();
                            $('#logprogress').hide();
                            localStorage.syncPressed = 'false';
                            $('.syncButton').prop('disabled', false);
                            clearInterval(syncInterval);
                        }
                    }
                });

            }
        }
    } else {
        DisplayMessage('.alert_error', phrases['file_not_upload']);
    }
}

// ************************************
// On load
$(function(){

    // Έναρξη των validates
    startValidates();



    // Εμφανίζει την ώρα
    getTime('#timetext');


    // Εμφανίζει συνεχώς την ώρα
    setInterval(function(){
        getTime('#timetext');
        if(checkFullscreen()) getTime('#overlay_time');

    }, 1000);



    // Ελέγχει τον χρόνο που βρίσκεται το βίντεο και όταν περάσει το όριο εκτελεί συγκεκριμένες εντολές
    $("#myVideo").on(
        "timeupdate",
        function(event){
            curTimePercent=(this.currentTime/this.duration)*100; // O τρέχον χρόνος σε ποσοστό επί του συνολικού


            if( (curTimePercent>TimePercentTrigger) && (TimeUpdated==false) ) {   // Όταν περάσει το 20% ενημερώνει την βάση
                updateVideoPlayed();
                TimeUpdated=true;
            }

            //Μετατροπή του track time σε λεπτά και δευτερόλεπτα
            timeInMinutesAndSeconds=seconds2MinutesAndSeconds(this.currentTime)['minutes']+' : '+seconds2MinutesAndSeconds(this.currentTime)['seconds'];

            // Εμφάνιση του τρέχοντα track time
            if(checkFullscreen()) { // όταν είναι σε full screen
                $('#jsOverlayTrackTime').html(timeInMinutesAndSeconds);
                $('.o-trackTime--overlay__range ').val(curTimePercent);
            } else {   // όταν δεν είναι σε full screen
                $('#jsTrackTime').html(timeInMinutesAndSeconds);
                $('.o-trackTime__range').val(curTimePercent);
            }

        });


    // Ελέγχει αν είναι focus οι φόρμες
    checkFormsFocus();

    // Έλεγχος πατήματος πλήκτρων
    getShortcuts(window);

    // Έναρξη των ελέγχων για όταν γίνονται αλλαγές στα search fields
    checkSearchFieldChanges();

    // Ελέγχει την τρέχουσα έκδοση
    checkCurrentVersion();

    // Έλεγχος για garbage collection
    setInterval(garbageCollection, 600000);


    document.addEventListener('touchmove', displayFullscreenControls, false);
    


});



