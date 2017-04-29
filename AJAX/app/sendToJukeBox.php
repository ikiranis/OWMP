<?php
/**
 * File: sendToJukeBox.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 25/12/16
 * Time: 16:40
 *
 * Αντιγράφει την τρέχουσα playlist στην jukebox list
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\parrot\app\OWMPElements;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;


// Αν δεν υπάρχει ήδη το JUKEBOX_LIST_NAME το δημιουργούμε
if(!MyDB::checkIfTableExist(JUKEBOX_LIST_NAME)) {
    OWMPElements::createPlaylistTempTable(JUKEBOX_LIST_NAME);
} 

// Αντιγράφει τον $tempUserPlaylist στον JUKEBOX_LIST_NAME
if(MyDB::checkIfTableExist(JUKEBOX_LIST_NAME)) {
    
    // Πρώτα σβήνει τα υπάρχοντα περιεχρόμενα του JUKEBOX_LIST_NAME
    MyDB::deleteTable(JUKEBOX_LIST_NAME);
    
    // Κάνει την αντιγραφή
    if (MyDB::copyTable($tempUserPlaylist, JUKEBOX_LIST_NAME)) {
        $jsonArray = array('success' => true);
    } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η αντιγραφή
}



echo json_encode($jsonArray);