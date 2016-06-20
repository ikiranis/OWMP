//
// File: scripts.js
// Created by rocean
// Date: 20/05/16
// Time: 19:44
// Javascript controls και functions
//

var UserKeyPressed=false;


var AJAX_path='AJAX/';


var myVideo;
var currentVideo = 25313;


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


    callFile="updateOption.php?id="+id+"&option_name="+option_name+"&option_value="+encodeURIComponent(option_value);


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
    callFile="deleteUser.php?id="+id;

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
            // rules : {
            //     repeat_password: {
            //         equalTo : '[name="password"]'
            //     }
            // }
        });



    }
}


// Set the src of the video to the next URL in the playlist
// If at the end we start again from beginning (the modulo
// source.length does that)
function loadNextVideo() {

    files_index=Math.floor(Math.random()*files.length);    // Παίρνει τυχαίο index
    file_path=files[files_index][1];    // Το filename μαζί με όλο το path
    myVideo.src = file_path;

    filename=file_path.split('/'); // σκέτο το filename
    filename=filename[filename.length-1];

    callFile = AJAX_path+"getVideoMetadata.php?filename=" + encodeURIComponent(file_path);
    console.log(file_path);
    
    $.get(callFile, function (data) {  // τραβάει τα metadata του αρχείου

        if (data.success == true) {
        //     console.log(data);
            if (data.artist)
                $('#file_name').text(data.artist+' - '+data.title);
            else $('#file_name').text(filename);
        }

    }, "json");
    
    

    myVideo.load();
}
// callback that loads and plays the next video
function loadAndplayNextVideo() {
    // console.log("playing " + files[Math.random()*files.length][1]);
    loadNextVideo();
    myVideo.play();
}
// Called when the page is loaded
function init(){
    // get the video element using the DOM api
    myVideo = document.querySelector("#myVideo");
    // Define a callback function called each time a video ends
    myVideo.addEventListener('ended', loadAndplayNextVideo, false);
    // Load the first video when the page is loaded.
    loadNextVideo();
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
        //     rules : {
        //         repeat_password: {
        //             equalTo : '[name="password"]'
        //         }
        //     }
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
    



});



