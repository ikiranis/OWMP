<?php
/**
 *
 * File: createSmartPlaylist.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 15/06/2017
 * Time: 01:37
 *
 * Δημιουργεί μια smart playlist
 *
 */


use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\User;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['playlistName']))
    $playlistName=ClearString($_GET['playlistName']);

$user = new User();

$userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

$sql = 'INSERT INTO smart_playlists (playlist_name, user_id) VALUES(?,?)';   // Εισάγει στον πίνακα manual_playlists
$playlistArray = array($playlistName, $userID);

// Ψάχνει αν υπάρχει ήδη λίστα με συγκεκριμένο όνομα
$searchPlaylist = MyDB::getTableArray('smart_playlists', 'playlist_name', 'playlist_name=?', array($playlistName), null, null, null);

if(!$searchPlaylist) { // Αν δεν υπάρχει την εισάγουμε
    if($playlistID=$conn->insertInto($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στην smart_playlists
        $jsonArray = array('success' => true, 'playlistID' => $playlistID, 'playlistName' => $playlistName);
    }
    else {
        $jsonArray = array('success' => false, 'playlistName' => $playlistName);
    }
} else {
    $jsonArray = array('success' => false, 'playlistName' => $playlistName);
}


echo json_encode($jsonArray);