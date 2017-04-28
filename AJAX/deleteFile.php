<?php
/**
 * File: deleteFile.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 22/07/16
 * Time: 19:00
 * Σβήνει το αρχείο, μαζί με την αντίστοιχη εγγραφή στην βάση
 */

use apps4net\framework\Page;
use apps4net\framework\Logs;
use apps4net\parrot\app\OWMPElements;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if(OWMPElements::deleteFile($id)==true) {
    $jsonArray = array('success' => true, 'id' => $id);
    Logs::insertLog('Deleted song with id: '.$id); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);