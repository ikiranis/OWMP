<?php
/**
 *
 * File: saveSmartPlaylist.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 15/06/2017
 * Time: 02:46
 *
 * Σώζει το search query σε smart playlist, σε μορφή json
 *
 */


use apps4net\framework\Page;
use apps4net\framework\MyDB;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);

if(isset($_GET['searchJsonString']))
    $searchJsonString=urldecode($_GET['searchJsonString']);

$update=MyDB::updateTableFields('smart_playlists', 'id=?',
    array('playlist_data'),
    array($searchJsonString, $playlistID));

if ($update) { // Ενημερώνει την εγγραφή με το $searchJsonString
    $jsonArray = array('success' => true);
} else { // Δεν έγινε το update του row
    $jsonArray = array('success' => false, 'errorID' => 1);
}

echo json_encode($jsonArray);