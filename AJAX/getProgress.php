<?php
/**
 * File: getProgress.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 28/09/16
 * Time: 23:50
 *
 * Παίρνει την τιμή του progress
 */

use apps4net\framework\Page;

require_once('../src/boot.php');


session_start();
Page::checkValidAjaxRequest(true);

if($progressInPercent=Page::getPercentProgress()) {
    $jsonArray = array('success' => true, 'progressInPercent' => $progressInPercent);
}
else {
    $jsonArray=array( 'success'=> false);
}



echo json_encode($jsonArray);