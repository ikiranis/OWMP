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



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['mediaKind']))
    $mediaKind=ClearString($_GET['mediaKind']);

$youtubeDL = new videoDownload();

$youtubeDL->videoID = $id;
$youtubeDL->mediaKind = $mediaKind;

//trigger_error($youtubeDL->checkURLkind());

//if($result=$youtubeDL->getYoutubePlaylistItems()) {
//    var_dump($result);
//    $jsonArray = array('success' => true, 'result' => $result);
//}
//else $jsonArray=array( 'success'=> false, 'theUrl' => $url);

if($result=$youtubeDL->downloadVideo()) {
    $jsonArray = array('success' => true, 'result' => $result);
}
else $jsonArray=array( 'success'=> false, 'theUrl' => $id);


echo json_encode($jsonArray);
