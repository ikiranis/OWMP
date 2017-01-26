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
require_once ('../libraries/videoDownload.php');

session_start();

Page::checkValidAjaxRequest(true);

set_time_limit(0);



if(isset($_GET['url']))
    $url=ClearString($_GET['url']);

if(isset($_GET['mediaKind']))
    $mediaKind=ClearString($_GET['mediaKind']);

$youtubeDL = new videoDownload();

$youtubeDL->videoURL = $url;
$youtubeDL->mediaKind = $mediaKind;

if($result=$youtubeDL->downloadVideo()) {
    $jsonArray = array('success' => true, 'result' => $result);
}
else $jsonArray=array( 'success'=> false, 'theUrl' => $url);



echo json_encode($jsonArray);
