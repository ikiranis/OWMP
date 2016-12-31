<?php
/**
 * File: voteSong.php
 * Created by rocean
 * Date: 25/12/16
 * Time: 17:21
 * 
 * Προσθέτει μία ψήφο για το τραγούδι
 * 
 */


require_once('../libraries/common.inc.php');

Page::checkValidAjaxRequest();

session_start();

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if(OWMP::voteSong($id)) {
    $jsonArray = array('success' => true, 'id' => $id);
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);


?>