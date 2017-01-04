<?php
/**
 * File: loadPlayedQueue.php
 * Created by rocean
 * Date: 21/12/16
 * Time: 01:54
 *
 * Φορτώνει την λίστα με τα τραγούδια που παίξανε
 * 
 */




require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
$tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;


if($tempPlayedQueuePlaylist) {  // Αν υπάρχει το συγκεκριμένο $tempPlayedQueuePlaylist

    // Σβήνει πρώτα τα περιεχόμενα του $tempUserPlaylist
    if(RoceanDB::deleteTable($tempUserPlaylist)) {

        // Αντιγράφει τον $tempPlayedQueuePlaylist στον $tempUserPlaylist
        if (RoceanDB::copyTable($tempPlayedQueuePlaylist, $tempUserPlaylist)) {
            $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η αντιγραφή

    } else $jsonArray = array('success' => false, 'errorID' => 2); // Δεν έγινε η διαγραφή του $tempUserPlaylist

}
else {
    $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
}



echo json_encode($jsonArray);