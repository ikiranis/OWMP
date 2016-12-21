<?php
/**
 * File: garbageCollection.php
 * Created by rocean
 * Date: 14/11/16
 * Time: 01:33
 * Καθαρίζει την βάση από προσωρινούς πίνακες που δεν χρησιμοποιούνται άλλο
 */


require_once('../libraries/common.inc.php');

session_start();

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
$tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

// Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
$theDate = date('Y-m-d H:i:s');
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempPlayedQueuePlaylist));

$conn = new RoceanDB();

$lastMinutes = strtotime('-10 minutes');
$theDate = date('Y-m-d H:i:s', $lastMinutes);
$playlistTablesToDelete = RoceanDB::getTableArray('playlist_tables', 'table_name', 'last_alive<?', array($theDate), null, null, null);

//trigger_error('RUNNING '.$theDate);

foreach ($playlistTablesToDelete as $item) {
    if(RoceanDB::checkIfTableExist($item['table_name'])) // Αν υπάρχει το σβήνουμε
        if(RoceanDB::dropTable($item['table_name'])) {
            if($conn->deleteRowFromTable ('playlist_tables','table_name',$item['table_name']))
//                trigger_error('SBHSIMO TOY: '.$item['table_name']);
                $jsonArray = array('success' => true);
        }
}




//echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);


?>