<?php
/**
 * File: getYouTube.php
 * Created by rocean
 * Date: 30/09/16
 * Time: 00:38
 * 
 * Κατεβάζει ένα βίντεο από το YouTube
 */

require_once('../libraries/common.inc.php');

session_start();



if(isset($_GET['url']))
    $url=ClearString($_GET['url']);


if($result=OWMP::downloadYoutube($url)) {
    $jsonArray = array('success' => true, 'result' => $result);
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);
