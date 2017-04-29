<?php
/**
 * File: checkLogin.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 05/06/16
 * Time: 19:09
 * Έλεγχος αν έχει γίνει σωστά το login
 *
 */

use apps4net\framework\User;
use apps4net\framework\Page;
use apps4net\framework\Language;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);


$user = new User();
$lang = new Language();

if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

if(isset($_GET['password']))
    $password=ClearString($_GET['password']);

if (isset($_GET['SavePassword']))
    $SavePassword=$_GET['SavePassword'];


    $login=$user->CheckLogin($username, $password, $SavePassword);

    if($login['success']) {
        $jsonArray=array( 'success'=>true, 'message'=>$login['message']);
    }
    else {
        $jsonArray=array( 'success'=>false, 'message'=>$login['message']);
    }


echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);

