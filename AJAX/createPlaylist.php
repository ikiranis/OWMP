<?php
/**
 * File: createPlaylist.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 30/11/16
 * Time: 02:36
 * 
 * Δημιουργεί μια playlist
 */

use apps4net\framework\Page;
use apps4net\framework\User;
use apps4net\parrot\app\OWMP;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);



if(isset($_GET['playlistName']))
    $playlistName=ClearString($_GET['playlistName']);

$user = new User();

$userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

$playlistTableName = MANUAL_PLAYLIST_STRING.date('YmdHis');   // Το όνομα που θα πάρει το table του manual playlist

if(OWMP::createPlaylistTempTable($playlistTableName)) {  // Αν δημιουργηθεί κανονικά το table του manual playlist
    $sql = 'INSERT INTO manual_playlists (table_name, playlist_name, user_id) VALUES(?,?,?)';   // Εισάγει στον πίνακα manual_playlists
    $playlistArray = array($playlistTableName, $playlistName, $userID);

    if($playlistID=$conn->insertInto($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στην manual_playlists
        $jsonArray = array('success' => true, 'playlistID' => $playlistID, 'playlistName' => $playlistName);

    }
    else {
        $jsonArray = array('success' => false, 'playlistName' => $playlistName);
    }
}
else {
    $jsonArray = array('success' => false, 'playlistName' => $playlistName);
}



echo json_encode($jsonArray);