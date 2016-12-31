<?php
/**
 * File: sendToJukeBox.php
 * Created by rocean
 * Date: 25/12/16
 * Time: 16:40
 *
 * Αντιγράφει την τρέχουσα playlist στην jukebox list
 *
 */



require_once ('../libraries/common.inc.php');

Page::checkValidAjaxRequest();

session_start();

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;


// Αν δεν υπάρχει ήδη το JUKEBOX_LIST_NAME το δημιουργούμε
if(!RoceanDB::checkIfTableExist(JUKEBOX_LIST_NAME)) {
    OWMP::createPlaylistTempTable(JUKEBOX_LIST_NAME);
} 

// Αντιγράφει τον $tempUserPlaylist στον JUKEBOX_LIST_NAME
if(RoceanDB::checkIfTableExist(JUKEBOX_LIST_NAME)) {
    
    // Πρώτα σβήνει τα υπάρχοντα περιεχρόμενα του JUKEBOX_LIST_NAME
    RoceanDB::deleteTable(JUKEBOX_LIST_NAME);
    
    // Κάνει την αντιγραφή
    if (RoceanDB::copyTable($tempUserPlaylist, JUKEBOX_LIST_NAME)) {
        $jsonArray = array('success' => true);
    } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η αντιγραφή
}



echo json_encode($jsonArray);