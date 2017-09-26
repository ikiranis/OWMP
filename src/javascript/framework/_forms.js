/**
 *
 * File: _forms.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 16:41
 *
 * Forms functions
 *
 */

/**
 * Κάνει submit στην αντίστοιχη φόρμα που είναι ανοιχτή
 */
function pressEnterToForm() {

    if(!$('#LoginForm').length == 0) {
        $('#LoginForm #submit').click();
    }

    if(!$('#RegisterForm').length == 0) {
        $('#RegisterForm #register').click();
    }
}

/**
 * Ελέγχει το focus μιας φόρμας
 * @source http://help.dottoro.com/ljmusasd.php
 *
 * @param theForm
 */
function checkTheFocus(theForm) {
    var form = document.getElementById (theForm);
    if ("onfocusin" in form) {  // Internet Explorer
        // the attachEvent method can also be used in IE9,
        // but we want to use the cross-browser addEventListener method if possible
        if (form.addEventListener) {    // IE from version 9
            form.addEventListener ("focusin", OnFocusInForm, false);
            form.addEventListener ("focusout", OnFocusOutForm, false);
        } else {
            if (form.attachEvent) {     // IE before version 9
                form.attachEvent ("onfocusin", OnFocusInForm);
                form.attachEvent ("onfocusout", OnFocusOutForm);
            }
        }
    } else {
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


/**
 * Τρέχει τα validates για τις διάφορες φόρμες
 */
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

/**
 * όταν η φόρμα είναι focused
 *
 * @param event
 */
function OnFocusInForm (event) {
    var target = event.target ? event.target : event.srcElement;
    if (target) {
        FocusOnForm=true;
    }
}

/**
 * όταν η φόρμα δεν είναι focused
 *
 * @param event
 */
function OnFocusOutForm (event) {
    var target = event.target ? event.target : event.srcElement;
    if (target) {
        FocusOnForm=false;
    }
}

/**
 * Ελέγχει αν είναι focus οι φόρμες
 */
function checkFormsFocus() {
    if(VideoLoaded) { // αν έχει φορτωθεί το βίντεο
        checkTheFocus('FormTags');
        checkTheFocus('FormMassiveTags');
        checkTheFocus('SearchForm');
        checkTheFocus('insertPlaylist');
        checkTheFocus('insertSmartPlaylist');
        // checkTheFocus('paths_form');
    }
}

/**
 * Σβήνει όλα τα περιεχόμενα της φόρμας
 */
function resetFormMassiveTags() {
    document.querySelector('#FormMassiveTags').reset();
    document.querySelector('#myImage').innerHTML='';
    document.querySelector('#uploadFile').value='';
}