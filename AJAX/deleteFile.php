<?php
/**
 * File: deleteFile.php
 * Created by rocean
 * Date: 22/07/16
 * Time: 19:00
 * Σβήνει το αρχείο, μαζί με την αντίστοιχη εγγραφή στην βάση
 */



require_once('../libraries/common.inc.php');

session_start();

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if(OWMP::deleteFile($id)==true)
    $jsonArray = array('success' => true, 'id'=>$id);
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);


?>