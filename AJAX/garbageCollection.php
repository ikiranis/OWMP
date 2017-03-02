<?php
/**
 * File: garbageCollection.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 14/11/16
 * Time: 01:33
 * Καθαρίζει την βάση από προσωρινούς πίνακες που δεν χρησιμοποιούνται άλλο
 */


require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
$tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

// Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
$theDate = date('Y-m-d H:i:s');
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempPlayedQueuePlaylist));

$conn = new RoceanDB();

$lastMinutes = strtotime('-12 hours');
$theDate = date('Y-m-d H:i:s', $lastMinutes);
$playlistTablesToDelete = RoceanDB::getTableArray('playlist_tables', 'table_name', 'last_alive<?', array($theDate), null, null, null);


foreach ($playlistTablesToDelete as $item) {
    if(RoceanDB::checkIfTableExist($item['table_name'])) // Αν υπάρχει το σβήνουμε
        if(RoceanDB::dropTable($item['table_name'])) {
            if($conn->deleteRowFromTable ('playlist_tables','table_name',$item['table_name']))
                $jsonArray = array('success' => true);
        }
}




//echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);


?>