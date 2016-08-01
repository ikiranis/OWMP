//
// File: scripts.js
// Created by rocean
// Date: 20/05/16
// Time: 19:44
// Javascript controls και functions
//

var UserKeyPressed=false;

// TODO να το τραβάει από τα options ή από το common.inc.php
var AJAX_path='AJAX/';  // ο κατάλογος των AJAX files
var TimePercentTrigger=20; // το ποσοστό που ενημερώνει το κάθε βίντεο με το play_count

var currentID; // Το τρέχον βίντεο

var myVideo;

// TODO να το παίρνει από τα options
var DIR_PREFIX='/media/';    // dir που μπαίνει μπροστά από το path


var TimeUpdated=false; // Κρατάει το αν έχει ήδη ενημερωθεί ο played time του βίντεο για να μην το ξανακάνει
var FocusOnForm=false; // Κρατάει το αν είμαστε στην φόρμα

var PlaylistContainerHTML='';   // τα περιεχόμενα του div playlist_containter


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

    if(page!==1)
        PlaylistContainerHTML=$('#playlistTable').html();


    $('section article').load(callFile, function() {

        if(page==1) {

            $('#playlistTable').html(PlaylistContainerHTML);
        }


        for(var i=1;i<=NavLength;i++)   // Κάνει όλα τα nav πεδία inactive
            $('#navID'+i).removeClass('active');
        
        $('#navID'+page).addClass('active');   // κάνει το page active
    });
}








// *************************************************************************
// OWMP functions

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
function showFullScreenVideoTags() {
    if (checkFullscreen ()) {  // Αν είναι σε fullscreen

        $('#overlay').stop(true,true).show().delay(10000).hide('slow');
    }
    else $('#overlay').hide();

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



    $.get(callFile, function (data) {  // τραβάει τα metadata του αρχείου
        // console.log(data);
        file_path=DIR_PREFIX+data.file.path+encodeURIComponent(data.file.filename);    // Το filename μαζί με όλο το path
        myVideo.src = file_path;
        // console.log(file_path);

        filename=data.file.filename; // σκέτο το filename

        if (data.tags.success == true) { // τυπώνει τα data που τραβάει
            // console.log(data);

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
            $('#track_time').val(data.tags.track_time);
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


// Ενημερώνει τα tags του κομματιού
function update_tags(key_rating) {
    song_name=$('#title').val();
    artist=$('#artist').val();
    genre=$('#genre').val();
    song_year=$('#year').val();
    album=$('#album').val();
    if(!key_rating)
        rating=$('#rating').val();
    else rating=key_rating;  // Αν έχει πατηθεί νούμερο για βαθμολογία
    live=$('#live').val();



    callFile=AJAX_path+"updateTags.php?id="+currentID+"&song_name="+encodeURIComponent(song_name)+"&artist="+encodeURIComponent(artist)+"&genre="+genre+
        "&song_year="+song_year+"&album="+encodeURIComponent(album)+"&rating="+rating+"&live="+live;


    $.get(callFile, function (data) {
        if (data.success == true) {

            $("#message").addClassDelay("success", 3000);

            if($("#fileID"+currentID).length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                $("#fileID"+currentID).find('.song_name').text(song_name);
                $("#fileID"+currentID).find('.artist').text(artist);
                $("#fileID"+currentID).find('.genre').text(genre);
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
            // $('#overlay_rating').html(stars);
            ratingToStars(rating,'#overlay_rating');

            showFullScreenVideoTags();


        }
        else $("#message").addClassDelay("failure", 3000);
    }, "json");
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



// αναζήτηση στην playlist
function searchPlaylist(offset, step, firstTime, numberOfQueries) {
    var searchArray=[];
    for(var i=1;i<=numberOfQueries;i++){
        searchArray[i]= {
            'search_field': $('#search_field' + i).val(),
            'search_text': $('#search_text' + i).val(),
            'search_operator': $('#search_operator' + i).val(),
            'search_equality': $('#search_equality' + i).val()
        }
    }

    jsonArray=JSON.stringify(searchArray);


    callFile=AJAX_path+"searchPlaylist.php?jsonArray="+encodeURIComponent(jsonArray)+"&offset="+offset+"&step="+step+"&firstTime="+firstTime;



    $('#playlist_container').load(callFile, function() {
        // console.log('load is done');
    });

}

// Κάνει τον συγχρονισμό των αρχείων
function startSync(operation) {
    callFile=AJAX_path+"syncTheFiles.php?operation="+operation;

    if(!localStorage.syncPressed)  // Αν δεν υπάρχει το localStorage.syncPressed θέτει αρχική τιμή
        localStorage.syncPressed=false;


    if(localStorage.syncPressed=='false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
        localStorage.syncPressed=true;

        $('#progress').show();
        
        $('#syncButtons').find('input').prop('disabled', true);

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

        var checIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checIDs.push(all_checkboxes[i].value);
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
            for(var i = 0; i < checIDs.length;  i++) {
                callFile = AJAX_path + "deleteFile.php?id=" + checIDs[i];

                $.get(callFile, function (data) {
                    if (data.success == true) {
                        $("#fileID" + data.id).remove();
                    }
                }, "json");
            }
        }
    }
}

// Προσθέτει ένα αρχείο σε playlist
function addToPlaylist(id) {
    alert('Δεν είναι έτοιμο ακόμη');
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

    // έλεγχος του focus στην FormTags. Αν είναι focus να μην δέχεται keys
    $("#FormTags input").click(function() {
        FocusOnForm=true;
    });

    $("#FormTags input").focus(function() {
        FocusOnForm=true;
    });

    $("#FormTags input").focusout(function() {
        FocusOnForm=false;
    });


    // έλεγχος του focus στην SearchForm
    $("#SearchForm input").click(function() {
        FocusOnForm=true;
    });

    $("#SearchForm input").focus(function() {
        FocusOnForm=true;
    });

    $("#SearchForm input").focusout(function() {
        FocusOnForm=false;
    });



    // TODO συμβατότητα με άλλους browsers
    document.addEventListener("webkitfullscreenchange", function() {
        showFullScreenVideoTags();
    });

    // Έλεγχος πατήματος πλήκτρων
    window.addEventListener('keydown', function(event) {

        if (!FocusOnForm) {
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
                showFullScreenVideoTags();
            }

            if (event.keyCode === 38) {   // πάνω βελάκι
                myVideo.volume += 0.05;
                localStorage.volume=myVideo.volume;
            }

            if (event.keyCode === 40) {   // κάτω βελάκι
                myVideo.volume -= 0.05;
                localStorage.volume=myVideo.volume;
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
            case 'date_added': searchStringElement.type='datetime-local'; break;
            case 'date_last_played': searchStringElement.type='datetime-local'; break;
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




});



