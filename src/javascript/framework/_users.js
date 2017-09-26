/**
 *
 * File: _users.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 16:32
 *
 * Users management functions
 *
 */

// Εισαγωγή αρχικού χρήστη admin
function registerUser() {
    var registerUserWindowID = $("#RegisterUserWindow");

    username = registerUserWindowID.find('input[name="username"]').val();
    email = registerUserWindowID.find('input[name="email"]').val();
    password = registerUserWindowID.find('input[name="password"]').val();
    repeat_password = registerUserWindowID.find('input[name="repeat_password"]').val();

    if ($('#RegisterForm').valid()) {

        callFile = AJAX_path+"framework/registerUser.php?username=" + username + "&password=" + password + "&email=" + email;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            if (result['success'] === true) {

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
    var loginWindowID = $("#LoginWindow");
    username = loginWindowID.find('input[name="username"]').val();
    password = loginWindowID.find('input[name="password"]').val();
    if (loginWindowID.find('input[name="SavePassword"]').is(":checked"))
        SavePassword = true;
    else SavePassword = false;

    if ($('#LoginForm').valid()) {


        callFile = AJAX_path+"framework/checkLogin.php?username=" + username + "&password=" + password + "&SavePassword=" + SavePassword;

        $.get(callFile, function (data) {

            result = JSON.parse(data);
            if (result['success'] === true) {
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
    var userIDElem = $("#UserID"+id);

    var username=userIDElem.find('input[name="theUsername"]').val();
    var email=userIDElem.find('input[name="email"]').val();
    var password=userIDElem.find('input[name="password"]').val();
    var repeat_password=userIDElem.find('input[name="repeat_password"]').val();
    var usergroup=userIDElem.find('select[name="usergroup"]').val();
    var fname=userIDElem.find('input[name="fname"]').val();
    var lname=userIDElem.find('input[name="lname"]').val();

    var changepass;

    if (password === '') {
        changepass = false;
    } else {
        changepass = true;
    }

    var callFile;

    if(changepass) {
        callFile=AJAX_path+"framework/updateUser.php?id="+id+"&username="+username+"&email="+email+"&password="+password+
            "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;
    } else {
        callFile=AJAX_path+"framework/updateUser.php?id="+id+"&username="+username+"&email="+email+
            "&usergroup="+usergroup+"&fname="+fname+"&lname="+lname;
    }

    if ( $('#users_formID'+id).valid() && password === repeat_password ) {

        $.get(callFile, function (data) {

            if (data.success === true) {
                if (id == 0) {   // αν έχει γίνει εισαγωγή νέας εγγρσφής, αλλάζει τα ονόματα των elements σχετικά
                    UserKeyPressed = false;
                    LastInserted = data.lastInserted;
                    $("#UserID0").prop('id', 'UserID' + LastInserted);
                    var userIDElem = $("#UserID" + LastInserted);
                    userIDElem.find('form').prop('id','users_formID'+ LastInserted);
                    userIDElem.find('input[name="update_user"]')
                        .attr("onclick", "updateUser(" + LastInserted + ")");
                    userIDElem.find('input[name="delete_user"]')
                        .attr("onclick", "deleteUser(" + LastInserted + ")");
                    userIDElem.find('input[id^="messageUserID"]').prop('id', 'messageUserID' + LastInserted);
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

// Σβήνει την εγγραφή στο user, user_details, salts
function deleteUser(id) {
    var callFile = AJAX_path + "framework/deleteUser.php?id=" + id;

    $.get( callFile, function( data ) {
        console.log(data.success);
        if(data.success === 'true') {

            $("#messageUserID"+id).addClassDelay("success",3000);

            var userIDElem = $("#UserID"+id);

            var myClasses = userIDElem.find('input[name=delete_user]').classes();   // Παίρνει τις κλάσεις του delete_alert

            if(!myClasses[2])   // Αν δεν έχει κλάση dontdelete σβήνει το div
                userIDElem.remove();
            else {   // αλλιώς καθαρίζει μόνο τα πεδία
                userIDElem.find('input').val('');   // clear field values
                userIDElem.prop('id','UserID0');
                var userID0Elem = $("#UserID0");
                userID0Elem.find('form').prop('id','users_formID0');
                userID0Elem.find('input[name="email"]').val('');
                userID0Elem.find('input[name="fname"]').val('');
                userID0Elem.find('input[name="lname"]').val('');
                userID0Elem.find('input[name="password"]').prop('required',true).prop('id','password0');
                userID0Elem.find('input[name="repeat_password"]').prop('required',true).prop('id','0');
                userID0Elem.find('input[id^="messageUserID"]').text('').prop('id','messageUserID0');
                // αλλάζει την function στο button
                userID0Elem.find('input[name="update_user"]').attr("onclick", "updateUser(0)");
                userID0Elem.find('input[name="delete_user"]').attr("onclick", "deleteUser(0)");

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
        var userID0Elem = $("#UserID0");
        userID0Elem.find('input[name="theUsername"]').val(''); // clear field values
        userID0Elem.find('form').prop('id','users_formID0');
        userID0Elem.find('input[name="email"]').val('');
        userID0Elem.find('input[name="fname"]').val('');
        userID0Elem.find('input[name="lname"]').val('');
        userID0Elem.find('input[name="password"]').prop('required',true).prop('id','password0');
        userID0Elem.find('input[name="repeat_password"]').prop('required',true).prop('id','0');
        userID0Elem.find('input[id^="messageUserID"]').text('').removeClass('success').prop('id','messageUserID0');
        // αλλάζει την function στο button
        userID0Elem.find('input[name="update_user"]').attr("onclick", "updateUser(0)");
        userID0Elem.find('input[name="delete_user"]').attr("onclick", "deleteUser(0)");
        UserKeyPressed = true;

        $('#users_formID0').validate({ // initialize the plugin
            errorElement: 'div'
        });

    }
}