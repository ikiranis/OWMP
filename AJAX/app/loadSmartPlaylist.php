<?php
/**
 *
 * File: loadSmartPlaylist.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 15/06/2017
 * Time: 22:38
 *
 * Φορτώνει μία smart playlist και επιστρέφει το json string
 *
 */


use apps4net\framework\Page;
use apps4net\framework\MyDB;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

$conn = new MyDB();

if(isset($_GET['playlistID'])) {
    $playlistID = ClearString($_GET['playlistID']);
}

// Ψάχνει αν υπάρχει η συγκεκριμένη λίστα
$smartPlaylist = MyDB::getTableArray('smart_playlists', 'id, playlist_data, playlist_name', 'id=?', array($playlistID), null, null, null);

if($smartPlaylist) {  // Αν υπάρχει η συγκεκριμένη εγγραφή
        $jsonArray = array('success' => true,
            'searchJsonArray' => $smartPlaylist[0]['playlist_data'],
            'playlistName' => $smartPlaylist[0]['playlist_name']);

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο row στην βάση
}

echo json_encode($jsonArray);