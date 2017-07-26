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

$youtubeDL = new VideoDownload();

$youtubeDL->maxVideoHeight = MAX_VIDEO_HEIGHT;

if(isset($_GET['id'])) {
    $youtubeDL->videoID = ClearString($_GET['id']);
}

if(isset($_GET['mediaKind'])) {
    $youtubeDL->mediaKind = ClearString($_GET['mediaKind']);
}

if($result=$youtubeDL->downloadVideo()) {
    // Εγγραφή στην βάση του τραγουδιού που κατέβηκε από το youtube
    $syncFile = new SyncFiles();
    $file = str_replace(DIR_PREFIX, '', $result);
    $syncFile->file = $file;
    $syncFile->searchIDFiles = true;
    $syncFile->mediaKind = $youtubeDL->mediaKind;
    $syncFile->name = $youtubeDL->title;

    $syncFile->writeTrack();

    $jsonArray = array('success' => true, 'result' => $result, 'imageThumbnail' => $youtubeDL->imageThumbnail,
        'filesToDelete' => $syncFile->deleteFilesString);
} else {
    $jsonArray=array( 'success'=> false, 'theUrl' => $youtubeDL->videoID);
}

echo json_encode($jsonArray);