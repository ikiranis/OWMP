<?php
/**
 * File: getSongInfo.php
 * Created by rocean
 * Date: 30/12/16
 * Time: 00:01
 * 
 * Επιστρέφει τα στοιχεία του τραγουδιού που παίζει αυτή την στιγμή
 * 
 */


require_once ('../libraries/common.inc.php');

if($currentSong = OWMP::getSongInfo(null)) { // Τα στοιχεία του τραγουδιού
    $jsonArray = array('success' => true,
        'songName' => $currentSong[0]['song_name'],
        'artist' => $currentSong[0]['artist'], 
        'fileID' => $currentSong[0]['id']);
} else {
    $jsonArray = array('success' => false);
}

echo json_encode($jsonArray);
