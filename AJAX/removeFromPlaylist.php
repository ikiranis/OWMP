<?php
/**
 * File: removeFromPlaylist.php
 * Created by rocean
 * Date: 01/12/16
 * Time: 21:24
 *
 * Αφαίρεση κομματιού από την playlist
 *
 */

require_once ('../libraries/common.inc.php');

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);

if(isset($_GET['fileID']))
    $fileID=ClearString($_GET['fileID']);

// Παίρνει το όνομα του table για την συγκεκριμένο playlistID
$playlistTableName = RoceanDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');

if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

    // Ο τίτλος του τραγουδιού
    $songName = RoceanDB::getTableFieldValue('music_tags', 'id=?', array($fileID), 'song_name');

    // Αν υπάρχει η συγκεκριμένη εγγραφή στο $playlistTableName
    if(RoceanDB::getTableFieldValue($playlistTableName, 'file_id=?', array($fileID), 'id')) {

        if($conn->deleteRowFromTable($playlistTableName, 'file_id', $fileID)) {
            $jsonArray = array('success' => true, 'song_name' => $songName, 'fileID' => $fileID);

        } else {
            $jsonArray = array('success' => false, 'errorID'=> 1);   // Δεν έγινε η διαγραφή του row από τον πίνακα $playlistTableName
        }
    }
    else $jsonArray = array('success' => false, 'errorID'=> 2, 'song_name' => $songName); // δεν υπάρχει το συγκεκριμένο fileID στην playlist
}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);