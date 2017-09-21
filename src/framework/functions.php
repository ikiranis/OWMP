<?php
/**
 *
 * File: functions.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 24/03/17
 * Time: 22:24
 *
 * Public functions
 *
 */

use apps4net\framework\Language;

// Καθαρίζει τα data που έδωσε ο χρήστης από περίεργο κώδικα
function ClearString($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// shortcut του $lang->phrases[$text];    Using like: __('αυτό είναι ένα μήνυμα');
function __($text){

    return Language::$phrases[$text];

}