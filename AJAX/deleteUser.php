<?php
/**
 * File: deleteUser.php
 * Created by rocean
 * Date: 03/06/16
 * Time: 21:15
 * Σβήνει εγγραφή στο user, user_details, salts
 */


require_once('../libraries/common.inc.php');

session_start();

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


$conn = new RoceanDB();


$deleteAlerts=$conn->deleteRowFromTable ('alerts','user_id',$id);
$deleteSalts=$conn->deleteRowFromTable ('salts','user_id',$id);
$deleteUserDetails=$conn->deleteRowFromTable ('user_details','user_id',$id);


if($deleteSalts==true && $deleteUserDetails==true){
    if($conn->deleteRowFromTable ('user','user_id',$id))
        $jsonArray=array( 'success'=>'true');
    else $jsonArray=array( 'success'=>'false');
}




echo json_encode($jsonArray);


?>