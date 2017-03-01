<?php
/**
 * File: checkVideoURL.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 28/01/17
 * Time: 00:17
 *
 * Ελέγχει αν ένα url είναι video ή playlist
 */


require_once('../libraries/common.inc.php');
require_once('../libraries/framework/videoDownload.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['url']))
    $url=ClearString($_GET['url']);

$youtubeDL = new videoDownload();

$youtubeDL->videoURL = $url;

// Ελέγχει αν είναι video ή playlist
if($urlKind=$youtubeDL->checkURLkind()) {

    if ($urlKind == 'video') { // Αν είναι video
        $videoID = $youtubeDL->getYoutubeID();
        $jsonArray = array('success' => true, 'videoKind' => 'video', 'videoID' => $videoID);
    } else {  // Αν είναι playlist
        $playlistItems = $youtubeDL->getYoutubePlaylistItems();
        $jsonArray = array('success' => true, 'videoKind' => 'playlist', 'playlistItems' => $playlistItems);
    }
}


echo json_encode($jsonArray);
