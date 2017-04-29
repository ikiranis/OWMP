<?php
/**
 * File: deletePath.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 13/10/16
 * Time: 02:00
 * 
 * Σβήνει μια εγγραφή από τον πίνακα paths
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\Logs;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


$conn = new MyDB();


if($conn->deleteRowFromTable('paths','id',$id)) {
    $jsonArray=array( 'success'=>true);

    Logs::insertLog('Path deleted with id '. $id); // Προσθήκη της κίνησης στα logs

}
else $jsonArray=array( 'success'=>false);

echo json_encode($jsonArray);