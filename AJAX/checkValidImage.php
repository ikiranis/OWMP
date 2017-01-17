<?php
/**
 * File: checkValidImage.php
 * Created by rocean
 * Date: 17/01/17
 * Time: 19:36
 * 
 * Ελέγχει ένα image αν είναι valid. Επιστρέφει true αν είναι εντάξει
 * 
 */


require_once('../libraries/common.inc.php');

session_start();

//Page::checkValidAjaxRequest(true);

if(isset($_GET['imagePath']))
    $imagePath=$_GET['imagePath'];


if($myImage=OWMP::openImage($imagePath)) {
    $jsonArray = array('success' => true);
    imagedestroy($myImage);
}


echo json_encode($jsonArray);