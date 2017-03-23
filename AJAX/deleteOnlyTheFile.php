<?php
/**
 * File: deleteOnlyTheFile.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 01/09/16
 * Time: 00:50
 * 
 * Σβήνει μόνο το αρχείο στον δίσκο και όχι και εγγραφή στην βάση
 * 
 */

use apps4net\framework\Page;
use apps4net\framework\RoceanDB;
use apps4net\parrot\app\OWMP;

require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['filename']))
    $filename=$_GET['filename'];

if(isset($_GET['fullpath']))
    $fullpath=$_GET['fullpath'];

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if (OWMP::deleteOnlyFile($fullpath)) {  // Αν υπάρχει ήδη στην βάση σβήνει το αρχείο στον δίσκο και βγάζει μήνυμα
    $jsonArray = array('success' => true, 'id' => $id);

    RoceanDB::insertLog('File ' . $filename . ' deleted.'); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);