<?php
/**
 * File: UsersManagement.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 24/04/16
 * Time: 21:45
 */

require_once ('libraries/common.inc.php');
require_once ('login.php');

session_start();


$UsersPage = new Page();
$lang = new Language();

// Τίτλος της σελίδας
$UsersPage->tittle = APP_NAME." | Users Page";


$UsersPage->showHeader();

if (isset($_SESSION["username"])) {
    $crypto = new Crypto();
    echo __('user_logged_in').$crypto->DecryptText($_SESSION["username"]);
}

if (isset($_GET['RegisterUser']))
    ShowRegisterUser();
else {
    $CheckDB = new RoceanDB();
    if($CheckDB->CheckIfThereIsUsers())
        DisplayUsers();
    else ShowRegisterUser();
}


?>

<div id="InsertNewUser">
    <a href="UsersManagement.php?RegisterUser=true">Πρόσθεσε νέο χρήστη</a>
</div>

<?php

$UsersPage->showFooter(true,true,true);
