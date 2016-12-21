<?php
/**
 * File: deletePlaylist.php
 * Created by rocean
 * Date: 01/12/16
 * Time: 19:07
 *
 * Σβήνει μια manual playlist
 */



require_once ('../libraries/common.inc.php');

session_start();

$conn = new RoceanDB();

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);


// Παίρνει το όνομα του table για την συγκεκριμένο playlistID
$playlistTableName = RoceanDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');


if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

    // Σβήνει το table $playlistTableName
    if(RoceanDB::dropTable($playlistTableName)) {

        // Σβήνει το συγκεκριμένο row της playlist από το manual_playlists
        if ($conn->deleteRowFromTable('manual_playlists', 'id', $playlistID) ) {
            $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η διαγραφή του row

    } else $jsonArray = array('success' => false, 'errorID' => 2); // Δεν έγινε η διαγραφή του $playlistTableName

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);