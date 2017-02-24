<?php
/**
 * File: deleteUser.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 03/06/16
 * Time: 21:15
 * Σβήνει εγγραφή στο user, user_details, salts
 */


require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


$conn = new RoceanDB();


$deleteAlerts=$conn->deleteRowFromTable ('alerts','user_id',$id);
$deleteSalts=$conn->deleteRowFromTable ('salts','user_id',$id);
$deletePlaylists=$conn->deleteRowFromTable ('manual_playlists','user_id',$id);
$deleteUserDetails=$conn->deleteRowFromTable ('user_details','user_id',$id);


if($deleteSalts==true && $deleteUserDetails==true && $deletePlaylists==true && $deleteAlerts==true){
    if($conn->deleteRowFromTable ('user','user_id',$id)) {
        $jsonArray = array('success' => 'true');

        RoceanDB::insertLog('User deleted with id '.$id); // Προσθήκη της κίνησης στα logs
    }
    else $jsonArray=array( 'success'=>'false');
} else $jsonArray=array( 'success'=>'false');




echo json_encode($jsonArray);


?>