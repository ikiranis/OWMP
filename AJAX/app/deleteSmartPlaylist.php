<?php
/**
 *
 * File: deleteSmartPlaylist.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 15/06/2017
 * Time: 01:54
 *
 * Σβήνει μια smart playlist
 */


use apps4net\framework\Page;
use apps4net\framework\MyDB;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

$conn = new MyDB();

if(isset($_GET['playlistID']))
    $playlistID=ClearString($_GET['playlistID']);

// Ψάχνει αν υπάρχει η συγκεκριμένη λίστα
$searchPlaylist = MyDB::getTableArray('smart_playlists', 'id', 'id=?', array($playlistID), null, null, null);

if($searchPlaylist) {  // Αν υπάρχει η συγκεκριμένη εγγραφή

    // Σβήνει το συγκεκριμένο row της playlist από το smart_playlists
    if ($conn->deleteRowFromTable('smart_playlists', 'id', $playlistID) ) {
        $jsonArray = array('success' => true);
    } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η διαγραφή του row

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο row στην βάση
}

echo json_encode($jsonArray);