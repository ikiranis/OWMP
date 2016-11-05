<?php
/**
 * File: getNextVideo.php
 * Created by rocean
 * Date: 05/11/16
 * Time: 01:30
 * Επιστρέφει το επόμενο τραγούδι για να παίξει. Το file id του
 */



require_once ('../libraries/common.inc.php');

session_start();

$conn = new RoceanDB();

$tempUserPlaylist=$conn->getSession('username').CUR_PLAYLIST_STRING;

if(isset($_GET['currentPlaylistID']))
    $currentPlaylistID=intval($_GET['currentPlaylistID']);

if(isset($_GET['playMode']))
    $playMode=ClearString($_GET['playMode']);



if($playMode=='shuffle') {
    $tableCount = RoceanDB::countTable($tempUserPlaylist);
    $randomRow = rand(1,$tableCount);
    $return = OWMP::getRandomPlaylistID($tempUserPlaylist, $randomRow);
    $playlistID = $return['playlist_id'];
    $fileID = $return['file_id'];
} else {
    $playlistID = $currentPlaylistID;
    $fileID=RoceanDB::getTableFieldValue($tempUserPlaylist, 'id=?', $currentPlaylistID, 'file_id');
}

if($playlistID && $fileID)
    $jsonArray = array('success' => true,
                        'playlist_id' => $playlistID,
                        'file_id' => $fileID);
else $jsonArray = array('success' => false);



echo json_encode($jsonArray);