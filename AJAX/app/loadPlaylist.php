<?php
/**
 * File: loadPlaylist.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 01/12/16
 * Time: 02:18
 *
 * Αντιγραφή της manual playlist στην current playlist
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;


// Παίρνει το όνομα του table για την συγκεκριμένο playlistID
$playlistTableName = MyDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');


if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

    // Σβήνει πρώτα τα περιεχόμενα του $tempUserPlaylist
    if(MyDB::deleteTable($tempUserPlaylist)) {

        // Αντιγράφει τον $playlistTableName στον $tempUserPlaylist
        if (MyDB::copyTable($playlistTableName, $tempUserPlaylist)) {
            $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η αντιγραφή

    } else $jsonArray = array('success' => false, 'errorID' => 2); // Δεν έγινε η διαγραφή του $tempUserPlaylist

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);