<?php
/**
 * File: deleteOnlyTheFile.php
 * Created by rocean
 * Date: 01/09/16
 * Time: 00:50
 * Σβήνει μόνο το αρχείο στον δίσκο και όχι και εγγραφή στην βάση
 */




require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['filename']))
    $filename=ClearString($_GET['filename']);

if(isset($_GET['fullpath']))
    $fullpath=urldecode($_GET['fullpath']);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if (OWMP::deleteOnlyFile($fullpath)) {  // Αν υπάρχει ήδη στην βάση σβήνει το αρχείο στον δίσκο και βγάζει μήνυμα
    $jsonArray = array('success' => true, 'id' => $id);

    RoceanDB::insertLog('File ' . $filename . ' deleted.'); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);


?>