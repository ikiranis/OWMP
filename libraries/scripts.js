//
// File: scripts.js
// Created by rocean
// Date: 20/05/16
// Time: 19:44
// Javascript controls και functions
//

var UserKeyPressed=false;
var PathKeyPressed=false;

var TimePercentTrigger=20; // το ποσοστό που ενημερώνει το κάθε βίντεο με το play_count

var currentID; // Το τρέχον βίντεο

var myVideo;

var TimeUpdated=false; // Κρατάει το αν έχει ήδη ενημερωθεί ο played time του βίντεο για να μην το ξανακάνει
var FocusOnForm=false; // Κρατάει το αν είμαστε στην φόρμα

var PlaylistContainerHTML=null;   // τα περιεχόμενα του div playlist_containter

var OverlayON=false;  // Κρατάει το αν το overlay εμφανίζεται
// var OverlayAllwaysOn=false;  // Κρατάει το αν αν έχει πατηθεί κουμπί για να παραμένει το overlay συνέχεια on

var myImage='';   // Το cover art που κάνουμε upload
var myMime='';  // Ο τύπος του cover art


if(localStorage.OverlayAllwaysOn==null) localStorage.OverlayAllwaysOn='false';    // μεταβλητή που κρατάει να θέλουμε να είναι πάντα on το overlay
if(localStorage.AllwaysGiphy==null) localStorage.AllwaysGiphy='false';   // μεταβλητή που κρατάει αν θέλουμε πάντα να δείχνει gifs αντί για albums


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
    $(element).show('slow').delay(5000).hide('fast');
}



// Εισαγωγή αρχικού χρήστη admin
function registerUser() {
    username = $("#RegisterUserWindow").find('input[name="username"]').val();
    email = $("#RegisterUserWindow").find('input[name="email"]').val();
    password = $("#RegisterUserWindow").find('input[name="password"]').val();
    repeat_password = $("#RegisterUserWindow").find('input[name="repeat_password"]').val();

    if ($('#RegisterForm').valid()) {


        callFile = AJAX_path+"registerUser.php?username=" + username + "&password=" + password + "&email=" + email;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            if (result['success'] == true) {

                window.location.href = "index.php";
            }
            else  DisplayMessage('#alert_error',result['message']);

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

    console.log(SavePassword);

    if ($('#LoginForm').valid()) {


        callFile = AJAX_path+"checkLogin.php?username=" + username + "&password=" + password + "&SavePassword=" + SavePassword;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            console.log(result['success']);
            if (result['success'] == true) {

                window.location.href = "index.php";
            }
            else  DisplayMessage('#alert_error',result['message']);

        });

    }

}




// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table alerts, ή εισάγει νέα εγγραφή
function updateUser(id) {
    username=$("#UserID"+id).find('input[name="username"]').val();
    email=$("#UserID"+id).find('input[name="email"]').val();
    password=$("#UserID"+id).find('input[name="password"]').val();
    repeat_password=$("#UserID"+id).find('input[name="repeat_password"]').val();
    usergroup=$("#UserID"+id).find('select[name="usergroup"]').val();
    fname=$("#UserID"+id).find('input[name="fname"]').val();
    lname=$("#UserID"+id).find('input[name="lname"]').val();

    if (password=='') changepass=false;
    else changepass=true;

    // console.log(id+' '+username+' '+email+' '+password+' '+repeat_password+' '+usergroup+' '+fname+' '+lname+ ' '+changepass);

    if(changepass)
        callFile=AJAX_path+"updateUser.php?id="+id+"&username="+username+"&email="+email+"&password="+password+
            "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;
    else callFile=AJAX_path+"updateUser.php?id="+id+"&username="+username+"&email="+email+
        "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;



    if ( $('#users_formID'+id).valid() && password==repeat_password ) {

        $.get(callFile, function (data) {

            if (data.success == true) {
                // console.log(data.success);

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

                DisplayMessage('#alert_error', error1+' '+username+' '+error2);
            } else $("#messageUserID" + id).addClassDelay("failure", 3000);

        }, "json");
    }

}




// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table options, ή εισάγει νέα εγγραφή
function updateOption(id) {
    option_name=$("#OptionID"+id).find('input[name="option_name"]').val();
    option_value=$("#OptionID"+id).find('input[name="option_value"]').val();


    callFile=AJAX_path+"updateOption.php?id="+id+"&option_name="+option_name+"&option_value="+encodeURIComponent(option_value);


    // console.log(callFile);

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
    callFile=AJAX_path+"deleteUser.php?id="+id;

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
        $("#UserID0").find('input[name="username"]').val(''); // clear field values
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

// TODO να το υλοποιήσω αλλιώς. Κάθε σελίδα να είναι σε ξεχωριστό div και να κάνει hide/show όποιο έχεις επιλέξει
// Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου με ajax
function DisplayWindow(page, offset, step) {
    // console.log(curNavItem+ ' '+ NavLength);
    callFile=AJAX_path+"displayWindow.php?page="+page+"&offset="+offset+"&step="+step;



    if(page!==1) {
        if(!$('#playlist_content').length==0)
            PlaylistContainerHTML = $('#playlist_content').html();

    }


    $('section article').load(callFile, function() {

        if(page==1) {

            $('#playlist_content').html(PlaylistContainerHTML);
        }


        for(var i=1;i<=NavLength;i++)   // Κάνει όλα τα nav πεδία inactive
            $('#navID'+i).removeClass('active');
        
        $('#navID'+page).addClass('active');   // κάνει το page active
    });
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

// *******************************************************************





// *************************************************************************
// OWMP functions

// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table paths, ή εισάγει νέα εγγραφή
function updatePath(id) {
    file_path=$("#PathID"+id).find('input[name="file_path"]').val();
    kind=$("#PathID"+id).find('select[name="kind"]').val();
    main=$("#PathID"+id).find('select[name="main"]').val();

    callFile=AJAX_path+"updatePath.php?id="+id+"&file_path="+file_path+"&kind="+kind+"&main="+main;

    if ($('#paths_formID'+id).valid()) {
        $.get(callFile, function (data) {
            if (data.success == true) {
                if (id == 0) {   // αν έχει γίνει εισαγωγή νέας εγγρσφής, αλλάζει τα ονόματα των elements σχετικά
                    PathKeyPressed = false;
                    LastInserted = data.lastInserted;
                    $("#PathID0").prop('id', 'PathID' + LastInserted);
                    $("#PathID" + LastInserted).find('form').prop('id','paths_formID'+ LastInserted);
                    $("#PathID" + LastInserted).find('input[name="update_path"]').attr("onclick", "updatePath(" + LastInserted + ")");
                    $("#PathID" + LastInserted).find('input[name="delete_path"]').attr("onclick", "deletePath(" + LastInserted + ")");
                    $("#PathID" + LastInserted).find('input[id^="messagePathID"]').prop('id', 'messagePathID' + LastInserted);
                    $("#messagePathID" + LastInserted).addClassDelay("success", 3000);
                }
                else $("#messagePathID" + id).addClassDelay("success", 3000);
            }
            else $("#messagePathID" + id).addClassDelay("failure", 3000);
        }, "json");
    }
}

// Σβήνει την εγγραφή στο paths
function deletePath(id) {
    callFile=AJAX_path+"deletePath.php?id="+id;

    $.get( callFile, function( data ) {
        if(data.success==true) {

            $("#messagePathID"+id).addClassDelay("success",3000);


            myClasses= $("#PathID"+id).find('input[name=delete_path]').classes();   // Παίρνει τις κλάσεις του delete_path

            if(!myClasses[2])   // Αν δεν έχει κλάση dontdelete σβήνει το div
                $("#PathID"+id).remove();
            else {   // αλλιώς καθαρίζει μόνο τα πεδία
                $("#PathID"+id).find('input').val('');   // clear field values
                $("#PathID"+id).prop('id','PathID0');
                $("#PathID0").find('form').prop('id','paths_formID0');
                $("#PathID0").find('input[id^="messagePathID"]').text('').prop('id','messagePathID0');
                // αλλάζει την function στο button
                $("#PathID0").find('input[name="update_path"]').attr("onclick", "updatePath(0)");
                $("#PathID0").find('input[name="delete_Path"]').attr("onclick", "deletePath(0)");

                $('#paths_formID0').validate({ // initialize the plugin
                    errorElement: 'div'
                });

            }
        }
        else $("#messagePathID"+id).addClassDelay("failure",3000);
    }, "json" );

}

// Εισάγει νέα div γραμμή αντιγράφοντας την τελευταία και μηδενίζοντας τις τιμές που είχε η τελευταία
function insertPath() {

    if(!PathKeyPressed) {
        // clone last div row
        $('div[id^="PathID"]:last').clone().insertAfter('div[id^="PathID"]:last').prop('id','PathID0');
        $("#PathID0").find('input').val('');   // clear field values
        $("#PathID0").find('form').prop('id','paths_formID0');
        $("#PathID0").find('input[id^="messagePathID"]').text('').removeClass('success').prop('id','messagePathID0');
        // αλλάζει την function στο button
        $("#PathID0").find('input[name="update_path"]').attr("onclick", "updatePath(0)");
        $("#PathID0").find('input[name="delete_path"]').attr("onclick", "deletePath(0)");
        PathKeyPressed=true;

        $('#paths_formID0').validate({ // initialize the plugin
            errorElement: 'div'
        });


    }
}


// Εμφανίζει rating αστεράκια στο elem
function ratingToStars(rating,elem) {
    rating=parseInt(rating);

    $(elem).html('');

    for(var i=1;i<=rating;i++){
        var img = document.createElement("img");
        img.src = "img/star.png";
        var src = document.querySelector(elem);
        src.appendChild(img);
    }


}


// Αλλάζει όλα τα checkItems checkboxes με την τιμή του checkAll
function changeCheckAll(checkAll, checkItems) {
    // Η τιμή του checkAll checkbox
    var currentCheckAllValue = document.querySelector('input[name="'+checkAll+'"]').checked;

    // Όλα τα elements που θέλουμε να αλλάξουμε
    var all_checkboxes = document.querySelectorAll('input[name="'+checkItems+'"]');

    for(var i = 0; i < all_checkboxes.length; i++) {
        // Αλλαγή της τιμής όλων των checkbox elements με την currentCheckAllValue
        all_checkboxes[i].checked = currentCheckAllValue;
    }

}



// TODO συμβατότητα με safari και firefox. Ο Firefox δεν εμφανίζει τον div. Ο safari δεν δέχεται κάποια keys όταν είναι σε fullscreen
// βάζει/βγάζει το video σε fullscren
function toggleFullscreen() {
    elem = myVideo;
    if (!document.fullscreenElement && !document.mozFullScreenElement &&
        !document.webkitFullscreenElement && !document.msFullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
}


// Ελέγχει αν βρίσκεται σε fullscreen
function checkFullscreen () {
    if (document.fullscreenElement || document.mozFullScreenElement ||
        document.webkitFullscreenElement || document.msFullscreenElement)
        return true;
    else return false;
}

// Εμφανίζει το div με τα metadata όταν είναι σε fullscreen
function showFullScreenVideoTags(toggle) {
    if(localStorage.OverlayAllwaysOn==null) localStorage.OverlayAllwaysOn='false';

    if (checkFullscreen ()) {  // Αν είναι σε fullscreen
        if(toggle!=null) {
            if (toggle == 'on') {
                $('#overlay').show();
                localStorage.OverlayAllwaysOn = 'true';
            }
            else {
                $('#overlay').hide();
                localStorage.OverlayAllwaysOn = 'false';
            }
        }
        else {
            if (localStorage.OverlayAllwaysOn=='false') {  // αν δεν εχει πατηθεί να πρέπει να είναι allways on
                if (!OverlayON) {  // αν δεν είναι on ήδη
                    OverlayON = true;
                    $('#overlay').show().delay(5000).hide('fast');
                    OverlayON = false;
                }

            }
            else $('#overlay').show();
        }

    }
    else {
        $('#overlay').hide();
        $('#overlay_volume').hide();
    }

}


// Set the src of the video to the next URL in the playlist
// If at the end we start again from beginning (the modulo
// source.length does that)
function loadNextVideo(id) {

    if(id==0) {
        files_index=Math.floor(Math.random()*files.length);    // Παίρνει τυχαίο index
        callFile = AJAX_path+"getVideoMetadata.php?id="+files[files_index][0];
        currentID=files[files_index][0];
    }

    else {
        files_index=id;
        callFile = AJAX_path+"getVideoMetadata.php?id="+id;
        currentID=id;
    }

    TimeUpdated=false;


    if(localStorage.AllwaysGiphy=='true') // Αν θέλουμε μόνο από Giphy
        callFile=callFile+'&onlyGiphy=true';

    $.get(callFile, function (data) {  // τραβάει τα metadata του αρχείου



        filename=data.file.filename; // σκέτο το filename

        if (data.tags.success == true) { // τυπώνει τα data που τραβάει
            var thePath=data.file.path;
            thePath=thePath.replace(WebFolderPath,'');
            file_path=DIR_PREFIX+thePath+encodeURIComponent(data.file.filename);    // Το filename μαζί με όλο το path

            myVideo.src = file_path;
            console.log(myVideo.src);

            if(data.file.kind=='Music') {  // Αν είναι Music τότε παίρνει το album cover και το εμφανίζει


                    var albumCoverPath = Album_covers_path + data.tags.albumCoverPath;
                    document.querySelector('#overlay_poster_source').innerHTML=data.tags.apiSource;

                if(localStorage.AllwaysGiphy=='true'){  // Αν θέλουμε μόνο από Giphy
                    if(data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                        myVideo.poster = data.tags.fromAPI;
                    }
                    else myVideo.poster = albumCoverPath;
                } else {   // όταν δεν θέλουμε μόνο από giphy
                    if (albumCoverPath == Album_covers_path + 'default.gif') {  // Αν δεν υπάρχει album cover το παίρνουμε από itunes ή giphy API
                        if (data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                            myVideo.poster = data.tags.fromAPI;
                        }
                        else myVideo.poster = albumCoverPath;
                    }
                    else myVideo.poster = albumCoverPath;
                }

            }
            else document.querySelector('#overlay_poster_source').innerHTML='';

            //Μετατροπή του track time σε λεπτά και δευτερόλεπτα
            timeInMinutesAndSeconds=seconds2MinutesAndSeconds(data.tags.track_time)['minutes']+' : '+seconds2MinutesAndSeconds(data.tags.track_time)['seconds'];

            // εμφανίζει τα metadata στα input fields
            $('#title').val(data.tags.title);
            $('#artist').val(data.tags.artist);
            $('#genre').val(data.tags.genre);
            $('#year').val(data.tags.year);
            $('#album').val(data.tags.album);
            $('#play_count').val(data.tags.play_count);
            $('#date_played').val(data.tags.date_played);
            $('#date_added').val(data.tags.date_added);
            $('#rating').val(data.tags.rating);
            $('#rating_output').val(data.tags.rating);
            $('#track_time').val(timeInMinutesAndSeconds);
            $('#live').val(data.tags.live);
            $('#path_filename').val(decodeURIComponent(file_path));


            // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
            $('#overlay_artist').html(data.tags.artist);
            $('#overlay_song_name').html(data.tags.title);
            $('#overlay_song_year').html(data.tags.year);
            $('#overlay_album').html(data.tags.album);
            // $('#overlay_rating').html(stars);
            ratingToStars(data.tags.rating,'#overlay_rating');
            $('#overlay_play_count').html(data.tags.play_count);
            $('#overlay_total_track_time').html(timeInMinutesAndSeconds);
            $('#overlay_live').html(liveOptions[data.tags.live]);
            showFullScreenVideoTags();

            makePlaylistItemActive(currentID);  // Κάνει active την συγκεκριμένη γραμμή στην playlist


        } else {   // Αν δεν βρει metadata τα κάνει όλα κενα

            $('#FormTags').find('input').not('[type="button"]').val('');
            $('#title').val(filename);
        }

        $("#TotalNumberInPlaylist").text(files.length);  // εμφανίζει το σύνολο των κομματιών στην playlist

    }, "json");

    myVideo.load();



}

// callback that loads and plays the next video
function loadAndplayNextVideo() {
    loadNextVideo(0);
    // myVideo.play();

}

// Called when the page is loaded
function init(){
    // get the video element using the DOM api
    myVideo = document.querySelector("#myVideo");
    // Define a callback function called each time a video ends
    myVideo.addEventListener('ended', loadAndplayNextVideo, false);

    if(!localStorage.volume)  // Αν δεν υπάρχει το localStorage.volume θέτει αρχική τιμή
        localStorage.volume='1';

    myVideo.volume=parseFloat(localStorage.volume);   // Θέτει το volume με βάση την τιμή του localStorage.volume


    // Load the first video when the page is loaded.
    loadNextVideo(0);

}

// Όταν δεν βρει ένα video να παίξει
function failed(e) {
    // video playback failed - show a message saying why
    switch (e.target.error.code) {
        case e.target.error.MEDIA_ERR_ABORTED:
            console.log('You aborted the video playback.');
            break;
        case e.target.error.MEDIA_ERR_NETWORK:
            console.log('A network error caused the video download to fail part-way.');
            break;
        case e.target.error.MEDIA_ERR_DECODE:
            console.log('The video playback was aborted due to a corruption problem or because the video used features your browser did not support.');
            break;
        case e.target.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
            console.log('The video could not be loaded, either because the server or network failed or because the format is not supported.');
            break;
        default:
            console.log('An unknown error occurred.');
            break;
    }

    loadAndplayNextVideo();
}

// Κάνει active το τρέχον row στην playlist
function makePlaylistItemActive(id) {
    $('.track').removeClass('ItemActive');  // Κάνει unactive όλα τα rows

    if($("#fileID"+id).length) { // Αν υπάρχει στην λίστα το συγκεκριμένο row το κάνει active
        $("#fileID" + id).addClass('ItemActive');

        if (!checkFullscreen ()) // Αν δεν είναι σε fullscreen, αλλιώς λειτουργεί περιέργως
            document.querySelector("#fileID"+id).scrollIntoView();  // κάνει scrolling στο συγκεκριμένο row
    }

}

// TODO για κάποιον λόγο κάποιες φορές πρέπει να παίρνει λάθος currentID και κάνει update λάθος εγγραφή
// Ενημερώνει τα tags του κομματιού
function update_tags(key_rating) {
    song_name=$('#FormTags #title').val();
    artist=$('#FormTags #artist').val();
    genre=$('#FormTags #genre').val();
    song_year=$('#FormTags #year').val();
    album=$('#FormTags #album').val();
    if(!key_rating)
        rating=$('#FormTags #rating').val();
    else rating=key_rating;  // Αν έχει πατηθεί νούμερο για βαθμολογία
    live=$('#FormTags #live').val();



    callFile=AJAX_path+"updateTags.php";


    $.ajax({
        url: callFile,
        type: 'POST',
        data: {
            id: currentID,
            song_name: song_name,
            artist: artist,
            genre: genre,
            song_year: song_year,
            album: album,
            rating: rating,
            live: live
        },
        dataType: "json",
        success: function(data) {
            if (data.success == true) {

                $("#message").addClassDelay("success", 3000);

                if($("#fileID"+currentID).length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                    $("#fileID"+currentID).find('.song_name').text(song_name);
                    $("#fileID"+currentID).find('.artist').text(artist);
                    $("#fileID"+currentID).find('.genre').text(genre);
                    $("#fileID"+currentID).find('.album').text(album);
                    $("#fileID"+currentID).find('.song_year').text(song_year);
                    $("#fileID"+currentID).find('.rating').text(rating);
                }


                if(key_rating) {   // Αν έχει πατηθεί νούμερο για βαθμολογία
                    $('#rating').val(rating);
                    $('#rating_output').val(rating);
                }

                FocusOnForm=false;


                // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
                $('#overlay_artist').html(artist);
                $('#overlay_song_name').html(song_name);
                $('#overlay_song_year').html(song_year);
                $('#overlay_album').html(album);
                $('#overlay_live').html(liveOptions[live]);

                // $('#overlay_rating').html(stars);
                ratingToStars(rating,'#overlay_rating');

                showFullScreenVideoTags();


            }
            else $("#message").addClassDelay("failure", 3000);
        }

    })
}

// Ενημερώνει τα play count και date last played
function updateVideoPlayed() {
    callFile=AJAX_path+"updateTimePlayed.php?id="+currentID;


    $.get(callFile, function (data) {
        if (data.success == true) {


            $('#play_count').val(data.play_count);     // Ενημερώνει τα σχετικά input πεδία
            $('#date_played').val(data.date_last_played);

            if($("#fileID"+currentID).length) {    // Ενημερώνει τα σχετικά πεδία στην λίστα
                $("#fileID"+currentID).find('.play_count').text(data.play_count);
            }

            $('#overlay_play_count').html(data.play_count);

        }
    }, "json");
}

// Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
function findDuplicates(offset, step, firstTime) {
    callFile=AJAX_path+"searchPlaylist.php?duplicates=true"+"&firstTime="+firstTime+"&offset="+offset+"&step="+step;


    $('#playlist_container').load(callFile, function() {
        // console.log('load is done');
    });
}

// αναζήτηση στην playlist
function searchPlaylist(offset, step, firstTime, numberOfQueries) {
    $('#progress').show();
    
    var searchArray=[];
    for(var i=1;i<=numberOfQueries;i++){
        searchArray[i]= {
            'search_field': $('#search_field' + i).val(),
            'search_text': $('#search_text' + i).val(),
            'search_operator': $('#search_operator' + i).val(),
            'search_equality': $('#search_equality' + i).val()
        }
    }

    var mediaKind=document.querySelector('#ChooseMediaKind select[name=mediakind]').value;

    jsonArray=JSON.stringify(searchArray);


    callFile=AJAX_path+"searchPlaylist.php?jsonArray="+encodeURIComponent(jsonArray)+"&offset="+offset+"&step="+step+"&firstTime="+firstTime+"&mediaKind="+encodeURI(mediaKind);


    
    $('#playlist_container').load(callFile, function() {
        // console.log('load is done');
        $('#progress').hide();
        $('#search').hide();
    });

}

// Κάνει τον συγχρονισμό των αρχείων
function startSync(operation) {
    var mediaKind=document.querySelector('#mediakind').value;
    
    callFile=AJAX_path+"syncTheFiles.php?operation="+operation+'&mediakind='+encodeURIComponent(mediaKind);


    // TODO να κάνω έλεγχο του php id process για να βλέπω αν τρέχει διεργασία αντί για το localstorage
    // if(!localStorage.syncPressed)  // Αν δεν υπάρχει το localStorage.syncPressed θέτει αρχική τιμή
        localStorage.syncPressed=false;


    if(localStorage.syncPressed=='false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
        localStorage.syncPressed=true;

        $('#progress').show();
        
        $('#syncButtons').find('input').prop('disabled', true);

        progressCallFile = AJAX_path + "getProgress.php";

        setInterval(function(){

            $.get(progressCallFile, function (progressData) {
                if (progressData.success == true) {
                    $("#logprogress" ).html(progressData.progressInPercent+'%');
                }
            }, "json");

        }, 5000);

        $('#SyncDetails').load(callFile, function() {

        // console.log('load is done');
            $('#progress').hide();
            localStorage.syncPressed=false;
            $('#syncButtons').find('input').prop('disabled', false);
        });
    }
    else alert ('Τρέχει ο συγχρονισμός σε άλλη διεργασία ήδη');
    // TODO υπάρχει περίπτωση να κλείσει ο browser πριν να τελειώσει η διεργασία και άρα το localStorage να μην πάρει
    // την τιμή false.  Έτσι δεν θα μπορούμε να ξανατρέξουμε την διεργασία. Να το διορθώσω με κάποιον έλεγχο ή να
    // μπορείς από τα options να το κάνεις reset.

}


// Καλεί AJAX request για να κατεβάσει το βίντεο από το youtube
function callGetYouTube(url) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "getYouTube.php",
        type: 'GET',
        async: true,
        data: {
            url: url
        },
        dataType: "json",
        beforeSend: function (xhr) {
            $("#logprogress").append('<p>Κατεβάζω το ' + url + '</p>');
        },
        success: function (data) {
            if (data.success == true) {
                $("#logprogress").append('<p>To video κατέβηκε στο path: ' + data.result + '</p>');
            }
        }
    });
}

// Κατεβάζει ένα ή περισσότερα βίντεο από το YouTube
function downloadYouTube() {
    var urls=document.querySelector('#youTubeUrl').value;

    urls=urls.split(',');  // Παίρνουμε το string σε array
    
    $('#progress').show();

    for (var i = 0; i < urls.length; i++) {

        callGetYouTube(urls[i]);

    }


    $( document ).one("ajaxStop", function() {  // Μόλις εκτελεστούν όλα τα ajax κάνει το παρακάτω
        $("#progress").hide();
        $("#logprogress").append('<p>Αρχίζω τον συγχρονισμό</p>');
        startSync('sync');
        // return;
    });


}


// Αλλάζει ένα text input σε select. Elem είναι το input field που θα αλλάξουμε. ID το id του row
function changeToSelect(elem, elementID, optionsArray) {

    elem.outerHTML = ""; // Σβήσιμο του υπάρχοντος
    delete elem;

    var afterElement = document.querySelector('#search_operator' + elementID); // To element πριν το οποίο θα προστεθεί το select

    // Δημιουργεί το select
    var element = document.createElement('select');
    element.setAttribute('type', 'text');
    element.setAttribute('id', 'search_text' + elementID);
    element.setAttribute('name', 'search_text' + elementID);

    var option=[];

    // Δημιουργεί τα options του select
    for (var i = 0; i < optionsArray.length; i++) {
        option[i] = document.createElement('option');
        option[i].value = i;
        option[i].innerHTML = optionsArray[i];
    }

    var newSelect=document.querySelector('#searchRow'+elementID).insertBefore(element, afterElement); // προσθέτει το element πριν το afterElement

    for (var i = 0; i < optionsArray.length; i++)
        newSelect.appendChild(option[i]); // προσθέτει τα options

}

// Αλλάζει ένα select σε input
function changeSelectToInput(elem, elementID) {
    elem.outerHTML = ""; // Σβήσιμο του υπάρχοντος
    delete elem;

    var afterElement = document.querySelector('#search_operator' + elementID); // To element πριν το οποίο θα προστεθεί το select

    // Δημιουργεί το select
    var element = document.createElement('input');
    element.setAttribute('type', 'text');
    element.setAttribute('id', 'search_text' + elementID);
    element.setAttribute('name', 'search_text' + elementID);


    var newSelect=document.querySelector('#searchRow'+elementID).insertBefore(element, afterElement); // προσθέτει το element πριν το afterElement


}

// εμφανίζει το sliderId value στο outputId
function printValue(sliderId, outputId) {
    outputId.value = sliderId.value;
}

// Σβήνει ένα αρχείο μαζί με την αντίστοιχη εγγραφή στην βάση
function deleteFile(id) {
    if(id==0) {  // Αν το id 0 παίρνει τα ids όλων των checkbox items σε πίνακα
        var all_checkboxes = document.querySelectorAll('input[name="check_item[]"]:checked');

        var checkIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checkIDs.push(all_checkboxes[i].value);
        }
    }


    var confirmAnswer=confirm('Are You Sure?');

    if (confirmAnswer==true) {
        if(id!=0) { // Αν δεν είναι 0 τότε σβήνει μοναδική εγγραφή
            callFile = AJAX_path + "deleteFile.php?id=" + id;

            $.get(callFile, function (data) {
                if (data.success == true) {

                    $("#fileID" + id).remove();
                    // loadNextVideo(0);
                }


            }, "json");
        }
        else {  // σβήνει μαζικά όσα αρχεία έχουν τσεκαριστεί
            for(var i = 0; i < checkIDs.length;  i++) {
                callFile = AJAX_path + "deleteFile.php?id=" + checkIDs[i];

                $.get(callFile, function (data) {
                    if (data.success == true) {
                        $("#fileID" + data.id).remove();
                    }
                }, "json");
            }
        }
    }
}

// Σβήνει μια λίστα (array) αρχείων
function deleteFiles(filesArray) {
    var confirmAnswer=confirm('Are You Sure?');

    if (confirmAnswer==true) {
        for (var i = 0; i < filesArray.length; i++) {
            callFile = AJAX_path + "deleteOnlyTheFile.php?fullpath=" + encodeURIComponent(filesArray[i]['fullpath']) +
                            "&filename=" + encodeURIComponent(filesArray[i]['filename']) +
                            "&id=" + filesArray[i]['id'];


            $.get(callFile, function (data) {
                if (data.success == true) {
                    $("#deleteRow" + data.id).remove();
                }
            }, "json");
        }
        $("#AgreeToDeleteFiles").remove();
    }
}


// Ανοίγει το παράθυρο για edit των tags
function openMassiveTagsWindow() {
    $('#editTag').show();
}

// Κλείνει το παράθυρο για edit των tags
function cancelEdit() {
    $('#editTag').hide();
}

// Κλείνει το παράθυρο για search
function cancelTheSearch() {
    $('#search').hide();
}

function readImage(files) {
    var selectedFile = document.getElementById('uploadFile').files[0];

    myMime = selectedFile.type;

    var f = files[0];


    var reader = new FileReader();

    // Called when the file content is loaded, e.target.result is
    // The content
    reader.onload = function (e) {
        // create a span with CSS class="thumb", for nicer layout
        var thumbImage = document.querySelector('#myImage');

        thumbImage.innerHTML = "<img class='thumb' src='" +
            e.target.result + "' alt='a picture'/>";

        myImage = e.target.result;

    };


    // Start reading asynchronously the file
    reader.readAsDataURL(f);

}

// Κάνει μαζικό edit των στοιχείων μιας λίστας (array) αρχείων
function editFiles() {

    var confirmAnswer=confirm('Are You Sure?');

    if (confirmAnswer==true) {
        var all_checkboxes = document.querySelectorAll('input[name="check_item[]"]:checked');

        var checkIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checkIDs.push(all_checkboxes[i].value);
        }

        artist=$('#FormMassiveTags #artist').val();
        genre=$('#FormMassiveTags #genre').val();
        song_year=$('#FormMassiveTags #year').val();
        album=$('#FormMassiveTags #album').val();
        rating=$('#FormMassiveTags #rating').val();
        live=$('#FormMassiveTags #live').val();


        if(myImage!='') {
            coverImage = myImage;
            coverMime = myMime;
        }
        else {
            coverImage = '';
            coverMime = '';
        }



        for (var i = 0; i < checkIDs.length; i++) {


            callFile=AJAX_path+"updateTags.php";

            $.ajax({
                url: callFile,
                type: 'POST',
                data: {
                    id: checkIDs[i],
                    artist: artist,
                    genre: genre,
                    song_year: song_year,
                    album: album,
                    rating: rating,
                    live: live,
                    coverMime: coverMime,
                    coverImage: coverImage
                },
                dataType: "json",
                success: function(data) {
                    if (data.success == true) {

                        if($("#fileID"+data.id).length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                            if(artist!='')
                                $("#fileID"+data.id).find('.artist').text(artist);
                            if(genre!='')
                                $("#fileID"+data.id).find('.genre').text(genre);
                            if(song_year!='')
                                $("#fileID"+data.id).find('.song_year').text(song_year);
                            if(rating!=0)
                                $("#fileID"+data.id).find('.rating').text(rating);
                        }

                    }
                }
            })
            
        }

        $('#editTag').hide();
    }

}


// Ενημερώνει μια λίστα (array) αρχείων που έχουν αλλάξει filepath και filename
function updateFiles(filesArray) {
    var confirmAnswer=confirm('Are You Sure?');

    if (confirmAnswer==true) {
        for (var i = 0; i < filesArray.length; i++) {
            callFile = AJAX_path + "updateFile.php?path=" + encodeURIComponent(filesArray[i]['path']) +
                "&filename=" + encodeURIComponent(filesArray[i]['filename']) +
                "&id=" + filesArray[i]['id'];

            $.get(callFile, function (data) {
                if (data.success == true) {
                    $("#updateRow" + data.id).remove();
                }
            }, "json");
        }
        $("#AgreeToUpdateFiles").remove();
    }
}

// Προσθέτει ένα αρχείο σε playlist
function addToPlaylist(id) {
    alert('Δεν είναι έτοιμο ακόμη');
}

// Εμφανίζει το volume
function displayVolume(operation) {
    if(checkFullscreen()) {
        var volume = parseInt(localStorage.volume * 100);

        if(operation!='giphyON' && operation!='giphyOFF')
            document.querySelector('#overlay_volume_text').innerText = volume;

        $('#overlay_volume_text').removeClass();

        switch (operation) {  // Αναλόγως τι είναι το πεδίο αλλάζουμε το search text type
            case 'up':
                $('#overlay_volume_text').addClass('overlay_volume_up');
                break;
            case 'down':
                $('#overlay_volume_text').addClass('overlay_volume_down');
                break;
            case 'mute':
                $('#overlay_volume_text').addClass('overlay_volume_mute');
                break;
            case 'giphyON':
                $('#overlay_volume_text').addClass('overlay_giphy');
                document.querySelector('#overlay_volume_text').innerText = 'on';
                break;
            case 'giphyOFF':
                $('#overlay_volume_text').addClass('overlay_giphy');
                document.querySelector('#overlay_volume_text').innerText = 'off';
                break;
        }


        $('#overlay_volume').show().delay(1500).fadeOut();
    }
}


// Αλλάζει τον χρόνο που βρίσκεται το track αναλόγως την θέση στον slider
function controlTrack() {
    var curTime=document.querySelector('#overlay_track_range').value;  // ο τρέχον track time σε ποσοστό
    var duration=myVideo.duration;  // ο συνολικός track time

    var PercentToTrackSeconds=parseInt( (curTime/100)*duration );  // μετατροπή του ποσοστού χρόνου σε πραγματικά δευτερόλεπτα

    myVideo.currentTime=PercentToTrackSeconds;
}


// Εμφανίζει το τρέχον cover image, όπου είναι ο κέρσορας
function displayCoverImage(elem) {
    $('.coverImage').hide();
    $('#'+elem).find('img').show();
}

function hideCoverImage() {
    $('.coverImage').hide();
}

// Εμφανίζει το παράθυρο για αναζήτηση
function displaySearchWindow() {
    $('#search').show();
}



// ************************************
// On load
$(function(){



    $('#LoginForm').validate({ // initialize the plugin
        errorElement: 'div'
    });

    $('#RegisterForm').validate({ // initialize the plugin
        errorElement: 'div',
        rules : {
            repeat_password: {
                equalTo : '[name="password"]'
            }
        }
    });


    $('.users_form').each(function() {  // attach to all form elements on page
        $(this).validate({       // initialize plugin on each form
            errorElement: 'div'
        });
    });



    $('.options_form').each(function() {  // attach to all form elements on page
        $(this).validate({       // initialize plugin on each form
            errorElement: 'div'
        });
    });




    getTime('#timetext'); // Εμφανίζει την ώρα


    // Εμφανίζει συνεχώς την ώρα
    setInterval(function(){
        getTime('#timetext');
        if(checkFullscreen()) getTime('#overlay_time');

    }, 1000);


    // Έλεγχος αν το repeat password  συμφωνεί με το password
    $('.UsersList').find('input[name=repeat_password]').keyup(function () {
        curEl=eval($(document.activeElement).prop('id'));

        // console.log($('#password'+curEl).val());

        if ($('#password'+curEl).val() === $(this).val()) {
            $(this)[0].setCustomValidity('');

        } else {
            $(this)[0].setCustomValidity('Passwords must match');
        }

    });


    $('#RegisterForm').find('input[name=repeat_password]').keyup(function () {
        // curEl=eval($(document.activeElement).prop('id'));
        //
        // console.log($(this).val());

        if ($('input[name=password]').val() === $(this).val()) {
            $(this)[0].setCustomValidity('');

        } else {
            $(this)[0].setCustomValidity('Passwords must match');
        }

    });
    // $("#FormMassiveTags input")
    // έλεγχος του focus στην FormTags. Αν είναι focus να μην δέχεται keys
    $("#FormTags input, #FormMassiveTags input, #SearchForm input").click(function() {
        FocusOnForm=true;
    });

    $("#FormTags input, #FormMassiveTags input, #SearchForm input").focus(function() {
        FocusOnForm=true;
    });

    $("#FormTags input, #FormMassiveTags input, #SearchForm input").focusout(function() {
        FocusOnForm=false;
    });


    // έλεγχος του focus στην SearchForm
    $("#SearchForm input, #FormMassiveTags input, #SearchForm input").click(function() {
        FocusOnForm=true;
    });

    $("#SearchForm input, #FormMassiveTags input, #SearchForm input").focus(function() {
        FocusOnForm=true;
    });

    $("#SearchForm input, #FormMassiveTags input, #SearchForm input").focusout(function() {
        FocusOnForm=false;
    });



    // TODO συμβατότητα με άλλους browsers
    document.addEventListener("webkitfullscreenchange", function() {
        showFullScreenVideoTags();
    });

    // Έλεγχος πατήματος πλήκτρων
    window.addEventListener('keydown', function(event) {

        if (!FocusOnForm && VideoLoaded) {
            if (event.keyCode === 78) {  // N
                loadAndplayNextVideo();
            }

            if (event.keyCode === 39) {  // δεξί βελάκι
                myVideo.currentTime+=60;
            }

            if (event.keyCode === 37) {  // αριστερό βελάκι
                myVideo.currentTime-=60;
            }

            if (event.keyCode === 32) {   // space
                if (myVideo.paused)
                    myVideo.play();
                else myVideo.pause();
                showFullScreenVideoTags();
            }

            if (event.keyCode === 73) {   // I

                if(localStorage.OverlayAllwaysOn=='true')
                    showFullScreenVideoTags('off');
                else
                    showFullScreenVideoTags('on');

            }

            if (event.keyCode === 71) {   // G

                if(localStorage.AllwaysGiphy=='true') {
                    localStorage.AllwaysGiphy = 'false';
                    displayVolume('giphyOFF');
                }
                else {
                    localStorage.AllwaysGiphy = 'true';
                    displayVolume('giphyON');
                }

            }

            if (event.keyCode === 38) {   // πάνω βελάκι
                myVideo.volume += 0.01;
                localStorage.volume=myVideo.volume;
                displayVolume('up');
            }

            if (event.keyCode === 40) {   // κάτω βελάκι
                myVideo.volume -= 0.01;
                localStorage.volume=myVideo.volume;
                displayVolume('down');
            }

            if (event.keyCode === 77) {   // M Mute
                if(localStorage.mute==null) localStorage.mute='false';

                if (localStorage.mute=='false') {
                    localStorage.oldVolume = localStorage.volume;
                    localStorage.mute = 'true';
                    myVideo.volume = 0;
                    localStorage.volume = myVideo.volume;
                    displayVolume('mute');
                } else {
                    localStorage.mute = 'false';
                    myVideo.volume = localStorage.oldVolume;
                    localStorage.volume = myVideo.volume;
                    displayVolume('up');
                }
            }

            if (event.keyCode === 190) {   // >
                myVideo.playbackRate += 1;
            }

            if (event.keyCode === 188) {   // <
                myVideo.playbackRate -= 1;
            }

            // if (event.keyCode === 187) {   // +
            // }
            //
            // if (event.keyCode === 189) {   // -
            // }

            if (event.keyCode === 191) {   // /
                myVideo.playbackRate = 1;
            }

            if (event.keyCode === 76) {   // L Αλλαγή live
                live=$('#live').val(); // Η τρέχουσα τιμή του live

                if (live==0) $('#live').val('1'); // Αν είναι 0 το κάνει 1
                else $('#live').val('0'); // Αλλιώς (αν είναι 1) το κάνει 0

                update_tags();  // ενημερώνει τα tags
            }

            if (event.keyCode === 49) {   // 1
                update_tags(1);
            }

            if (event.keyCode === 50) {   // 2
                update_tags(2);
            }

            if (event.keyCode === 51) {   // 3
                update_tags(3);
            }

            if (event.keyCode === 52) {   // 4
                update_tags(4);
            }

            if (event.keyCode === 53) {   // 5
                update_tags(5);
            }

            if (event.keyCode === 70) {   // F
                toggleFullscreen();  // μπαινοβγαίνει σε fullscreen
                FocusOnForm=false;
            }

        }

        // console.log(event.keyCode);

    }, false);



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

            // Εμφάνιση του τρεχόντα track time
            $('#overlay_current_track_time').html(timeInMinutesAndSeconds);
            $('#overlay_track_range').val(curTimePercent);

        });


    // Έλεγχος πιο πεδίο έχουμε διαλέξει για να ψάξουμε, ώστε να αλλάξουμε τον τύπο του search text
    $('.search_field').change(function() {
        changedElement=$(this).attr('id');  // το id του αλλαγμένου selected
        valueOfChangedElement=$(this).val();  // η τιμή του αλλαγμένου selected

        elementID=parseInt(changedElement.replace('search_field',''));   // παίρνουμε μόνο το id για να το προσθέσουμε στο search_text element
        searchStringElement=document.querySelector('#search_text'+elementID);

        // αν το πεδίο που θέλουμε να αλλάξουμε δεν είναι κάποιο από αυτά
        if( valueOfChangedElement!='rating' &&  valueOfChangedElement!='live' )
            if (searchStringElement.type=='select-one')  // Ελέγχουμε αν το υπάρχον είναι select
                changeSelectToInput(searchStringElement, elementID);  // Αν είναι select το αλλάζουμε σε input

        switch (valueOfChangedElement) {  // Αναλόγως τι είναι το πεδίο αλλάζουμε το search text type
            case 'date_added': searchStringElement.type='date'; break;
            case 'date_last_played': searchStringElement.type='date'; break;
            case 'play_count': searchStringElement.type='number'; break;
            case 'rating': changeToSelect(searchStringElement, elementID, ratingOptions); break;
            case 'video_width': searchStringElement.type='number'; break;
            case 'video_height': searchStringElement.type='number'; break;
            case 'filesize': searchStringElement.type='number'; break;
            case 'track_time': searchStringElement.type='number'; break;
            case 'song_year': searchStringElement.type='number'; break;
            case 'live': changeToSelect(searchStringElement, elementID, liveOptions); break;
            case 'song_name':  searchStringElement.type='text'; break;
            case 'artist': searchStringElement.type='text'; break;
            case 'genre': searchStringElement.type='text'; break;
            case 'album': searchStringElement.type='text'; break;
        }


    });



    //Λίστα των audio devices και επιλογή του. Παίζει μόνο σε https
    // navigator.mediaDevices.enumerateDevices()
    //     .then(gotDevices)
    //     .catch (errorCallback);
    //
    //
    // attachSinkId(myVideo, '8e2bf9f13b6253c686d45db2c3a7a38154f2ca4cb08243e32f8baa4171999958');
    //




});



