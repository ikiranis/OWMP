<?php
/**
 *
 * File: updateApp.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 09/03/17
 * Time: 00:51
 *
 * Κάνει update την εφαρμογή χρησιμοποιόντας το git
 *
 */

use apps4net\framework\Page;
use apps4net\framework\Crypto;
use apps4net\framework\Utilities;


require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

//if(isset($_GET['tabID']))
//    $tabID=ClearString($_GET['tabID']);


$crypt = new Crypto();
$sudoPass = $crypt->EncryptText('xxxxxxxx');


if (Utilities::runGitUpdate($sudoPass)) {
    $jsonArray = array('success' => true);
} else $jsonArray = array('success' => false);


echo json_encode($jsonArray);