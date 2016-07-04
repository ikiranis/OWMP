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
            console.log(result['success']);
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


// Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου με ajax
function DisplayWindow(page, offset, step) {
    console.log('offset: '+offset+' step: '+step);
    callFile=AJAX_path+"displayWindow.php?page="+page+"&offset="+offset+"&step="+step;



        $('section article').load(callFile, function() {
                console.log('load is done');
        });
}








// *************************************************************************
// OWMP functions

// Εμφανίζει rating αστεράκια στο elem
function ratingToStars(rating,elem) {
    rating=parseInt(rating);

    $(elem).html('');

    for(i=1;i<=rating;i++){
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


// Εμφανίζει το div με τα metadata όταν είναι σε fullscreen
function showFullScreenVideoTags() {
    if (document.fullscreenElement || document.mozFullScreenElement ||
        document.webkitFullscreenElement || document.msFullscreenElement) {  // Αν είναι σε fullscreen

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



        if (data.tags.success == true) {
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
                $('#track_time').val(data.tags.track_time);
                $('#live').val(data.tags.live);


                // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
                $('#overlay_artist').html(data.tags.artist);
                $('#overlay_song_name').html(data.tags.title);
                $('#overlay_song_year').html(data.tags.year);
                $('#overlay_album').html(data.tags.album);
                // $('#overlay_rating').html(stars);
                ratingToStars(data.tags.rating,'#overlay_rating');
                $('#overlay_play_count').html(data.tags.play_count);
                showFullScreenVideoTags();


        } else {   // Αν δεν βρει metadata τα κάνει όλα κενα

            $('#FormTags').find('input').not('[type="button"]').val('');
            $('#title').val(filename);
        }

    }, "json");

    myVideo.load();
}

// callback that loads and plays the next video
function loadAndplayNextVideo() {
    loadNextVideo(0);
    myVideo.play();

}

// Called when the page is loaded
function init(){
    // get the video element using the DOM api
    myVideo = document.querySelector("#myVideo");
    // Define a callback function called each time a video ends
    myVideo.addEventListener('ended', loadAndplayNextVideo, false);



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



    callFile=AJAX_path+"updateTags.php?id="+currentID+"&song_name="+song_name+"&artist="+artist+"&genre="+genre+
        "&song_year="+song_year+"&album="+album+"&rating="+rating+"&live="+live;


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


                if(key_rating)    // Αν έχει πατηθεί νούμερο για βαθμολογία
                    $('#rating').val(rating);

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
function searchPlaylist(offset, step, firstTime) {
    search_text=$('#search_text').val();
    search_genre=$('#search_genre').val();


    callFile=AJAX_path+"searchPlaylist.php?search_text="+encodeURIComponent(search_text)+"&search_genre="+encodeURIComponent(search_genre)
        +"&offset="+offset+"&step="+step+"&firstTime="+firstTime;



    $('#playlist_containter').load(callFile, function() {
        console.log('load is done');
    });
    
}






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


    setInterval(function(){  // Εμφανίζει συνεχώς την ώρα
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

    // TODO συμβατότητα με άλλους browsers
    document.addEventListener("webkitfullscreenchange", function() {
        showFullScreenVideoTags();
    });

    // Έλεγχος πατήματος πλήκτρων
    window.addEventListener('keydown', function(event) {

        if (!FocusOnForm) {
            if (event.keyCode === 39) {  // δεξί βελάκι
                loadAndplayNextVideo();
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
            }

            if (event.keyCode === 40) {   // κάτω βελάκι
                myVideo.volume -= 0.05;
            }

            if (event.keyCode === 187) {   // +
                myVideo.playbackRate += 1;
            }

            if (event.keyCode === 189) {   // -
                myVideo.playbackRate -= 1;
            }

            if (event.keyCode === 48) {   // 0
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




    $("#myVideo").on(    // Ελέγχει τον χρόνο που βρίσκετα το βίντεο και όταν περάσει το όριο εκτελεί συγκεκριμένες εντολές
        "timeupdate",
        function(event){
            curTimePercent=(this.currentTime/this.duration)*100; // O τρέχον χρόνος σε ποσοστό επί του συνολικού


            if( (curTimePercent>TimePercentTrigger) && (TimeUpdated==false) ) {   // Όταν περάσει το 20% ενημερώνει την βάση
                updateVideoPlayed();
                TimeUpdated=true;
            }

        });
    



});



