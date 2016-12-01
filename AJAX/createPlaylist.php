<?php
/**
 * File: createPlaylist.php
 * Created by rocean
 * Date: 30/11/16
 * Time: 02:36
 * 
 * Δημιουργεί μια playlist
 */


require_once ('../libraries/common.inc.php');

session_start();

if(isset($_GET['playlistName']))
    $playlistName=ClearString($_GET['playlistName']);

$conn = new RoceanDB();

$userID=$conn->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

$playlistTableName = MANUAL_PLAYLIST_STRING.date('YmdHis');   // Το όνομα που θα πάρει το table του manual playlist

if(OWMP::createPlaylistTempTable($playlistTableName)) {  // Αν δημιουργηθεί κανονικά το table του manual playlist
    $sql = 'INSERT INTO manual_playlists (table_name, playlist_name, user_id) VALUES(?,?,?)';   // Εισάγει στον πίνακα manual_playlists
    $playlistArray = array($playlistTableName, $playlistName, $userID);

    if($conn->ExecuteSQL($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στην manual_playlists
        $jsonArray = array('success' => true);

    }
    else {
        $jsonArray = array('success' => false);
    }
}
else {
    $jsonArray = array('success' => false);
}



echo json_encode($jsonArray);