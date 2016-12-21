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



//trigger_error(TAB_ID);

if(isset($_GET['currentPlaylistID']))
    $currentPlaylistID=intval($_GET['currentPlaylistID']);

if(isset($_GET['playMode']))
    $playMode=ClearString($_GET['playMode']);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;


// Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
$theDate = date('Y-m-d H:i:s');
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));

if($playMode=='shuffle') {
    $tableCount = RoceanDB::countTable($tempUserPlaylist);
    $randomRow = rand(0,$tableCount);
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