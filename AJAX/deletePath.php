<?php
/**
 * File: deletePath.php
 * Created by rocean
 * Date: 13/10/16
 * Time: 02:00
 * 
 * Σβήνει μια εγγραφή από τον πίνακα paths
 */


require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


$conn = new RoceanDB();


if($conn->deleteRowFromTable('paths','id',$id)) {
    $jsonArray=array( 'success'=>true);

    RoceanDB::insertLog('Path deleted with id '. $id); // Προσθήκη της κίνησης στα logs 

}
else $jsonArray=array( 'success'=>false);

echo json_encode($jsonArray);


?>