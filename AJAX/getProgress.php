<?php
/**
 * File: getProgress.php
 * Created by rocean
 * Date: 28/09/16
 * Time: 23:50
 *
 * Παίρνει την τιμή του progress
 */


require_once('../libraries/common.inc.php');



if($progressInPercent=Page::getPercentProgress()) {
    $jsonArray = array('success' => true, 'progressInPercent' => $progressInPercent);
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);
