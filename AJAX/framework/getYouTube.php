<?php
/**
 * File: getYouTube.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 30/09/16
 * Time: 00:38
 * 
 * Κατεβάζει ένα βίντεο από το YouTube
 *
 */

use apps4net\framework\Page;
use apps4net\framework\VideoDownload;
use apps4net\parrot\app\SyncFiles;


require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

set_time_limit(0);



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['mediaKind']))
    $mediaKind=ClearString($_GET['mediaKind']);

$youtubeDL = new VideoDownload();

$youtubeDL->videoID = $id;
$youtubeDL->mediaKind = $mediaKind;

//trigger_error($youtubeDL->checkURLkind());

//if($result=$youtubeDL->getYoutubePlaylistItems()) {
//    var_dump($result);
//    $jsonArray = array('success' => true, 'result' => $result);
//}
//else $jsonArray=array( 'success'=> false, 'theUrl' => $url);

if($result=$youtubeDL->downloadVideo()) {
    $jsonArray = array('success' => true, 'result' => $result, 'imageThumbnail' => $youtubeDL->imageThumbnail);

    // Εγγραφή στην βάση του τραγουδιού που κατέβηκε από το youtube
    $syncFile = new SyncFiles();
    $syncFile->startingValues();
    $file = str_replace(DIR_PREFIX, '', $result);
    $syncFile->file = $file;
    $syncFile->searchIDFiles = true;
    $syncFile->mediaKind = $youtubeDL->mediaKind;
    $syncFile->name = $youtubeDL->title;

    $syncFile->writeTrack();
} else {
    $jsonArray=array( 'success'=> false, 'theUrl' => $id);
}

echo json_encode($jsonArray);