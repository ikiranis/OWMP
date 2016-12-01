<?php
/**
 * File: addToPlaylist.php
 * Created by rocean
 * Date: 01/12/16
 * Time: 01:05
 *
 * Εισάγει ένα κομμάτι στην playlist
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

    if(!RoceanDB::getTableFieldValue($playlistTableName, 'file_id=?', array($fileID), 'id')) {
        $sql = 'INSERT INTO ' . $playlistTableName . ' (file_id) VALUES(?)';   // Εισάγει στον πίνακα $playlistTableName
        $playlistArray = array($fileID);

        if ($conn->ExecuteSQL($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στο $playlistTableName
            $jsonArray = array('success' => true, 'song_name' => $songName);

        } else {
            $jsonArray = array('success' => false, 'errorID'=> 1);   // Δεν έγινε η εγγραφή στην βάση
        }
    }
    else $jsonArray = array('success' => false, 'errorID'=> 2, 'song_name' => $songName); // υπάρχει ήδη το συγκεκριμένο fileID στην playlist
}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);