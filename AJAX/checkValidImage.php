<?php
/**
 * File: checkValidImage.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/01/17
 * Time: 19:36
 * 
 * Ελέγχει ένα image αν είναι valid. Επιστρέφει true αν είναι εντάξει
 * 
 */

use apps4net\parrot\app\OWMPElements;

require_once('../src/boot.php');

session_start();

//Page::checkValidAjaxRequest(true);

if(isset($_GET['imagePath']))
    $imagePath=$_GET['imagePath'];


if($myImage=OWMPElements::openImage($imagePath)) {
    $jsonArray = array('success' => true);
    imagedestroy($myImage);
}


echo json_encode($jsonArray);