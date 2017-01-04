<?php
/**
 * File: loadPlaylist.php
 * Created by rocean
 * Date: 01/12/16
 * Time: 02:18
 *
 * Αντιγραφή της manual playlist στην current playlist
 *
 */




require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;


// Παίρνει το όνομα του table για την συγκεκριμένο playlistID
$playlistTableName = RoceanDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');


if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

    // Σβήνει πρώτα τα περιεχόμενα του $tempUserPlaylist
    if(RoceanDB::deleteTable($tempUserPlaylist)) {

        // Αντιγράφει τον $playlistTableName στον $tempUserPlaylist
        if (RoceanDB::copyTable($playlistTableName, $tempUserPlaylist)) {
            $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η αντιγραφή

    } else $jsonArray = array('success' => false, 'errorID' => 2); // Δεν έγινε η διαγραφή του $tempUserPlaylist

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);