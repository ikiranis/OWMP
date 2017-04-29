<?php
/**
 * File: checkIfUserExists.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 07/06/16
 * Time: 18:51
 * Ελέγχει αν ο χρήστης υπάρχει στην βάση κι επιστρέφει true or false
 */

use apps4net\framework\User;
use apps4net\framework\Page;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);


if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

$user = new User();
if ($user->checkIfUserExists($username))
    $jsonArray=array( 'success'=>true);
else $jsonArray=array( 'success'=>false);


echo json_encode($jsonArray);

