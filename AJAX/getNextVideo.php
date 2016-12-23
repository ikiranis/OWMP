<?php
/**
 * File: getNextVideo.php
 * Created by rocean
 * Date: 05/11/16
 * Time: 01:30
 * Επιστρέφει το επόμενο τραγούδι για να παίξει. Το file id του
 */



require_once ('../libraries/common.inc.php');

session_start();

$conn = new RoceanDB();



//trigger_error(TAB_ID);

if(isset($_GET['currentPlaylistID']))
    $currentPlaylistID=intval($_GET['currentPlaylistID']);

if(isset($_GET['playMode']))
    $playMode=ClearString($_GET['playMode']);

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

if(isset($_GET['operation']))
    $operation=ClearString($_GET['operation']);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
$tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;


// Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
$theDate = date('Y-m-d H:i:s');
RoceanDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));

if($operation=='next') { // όταν θέλουμε να παίξει το επόμενο
    if ($playMode == 'shuffle') {
        $tableCount = RoceanDB::countTable($tempUserPlaylist);
        $randomRow = rand(0, $tableCount);
        $return = OWMP::getRandomPlaylistID($tempUserPlaylist, $randomRow);
        $playlistID = $return['playlist_id'];
        $fileID = $return['file_id'];
    } else {
        $playlistID = $currentPlaylistID;
        $fileID = RoceanDB::getTableFieldValue($tempUserPlaylist, 'id=?', $currentPlaylistID, 'file_id');
    }
    
}

// TODO να σκεφτώ αν όταν πατάς επόμενο, να παίζει από την ουρά το επόμενο ή όπως είναι τώρα να ξανα γυρνάει στην αρχική λίστα

if($operation=='prev') {  // όταν θέλουμε να παίξει το προηγούμενο τραγούδι που θα πάρει από την queue playlist
    
    // Έλεγχος αν υπάρχουν τραγούδια στην playlist και του μεγέθους του table $tempPlayedQueuePlaylist
    if($countQueuePlaylist=RoceanDB::countTable($tempPlayedQueuePlaylist)) {
        
        if($currentPlaylistID==0) { // Αν δεν έχει οριστεί προηγούμενο playlist ID
            // στέλνει το προτελευταίο id της queue playlist, γιατί το τελευταίο id είναι το τραγούδι που παίζει
            $fileID = RoceanDB::getTableFieldValue($tempPlayedQueuePlaylist, 'id=?', $countQueuePlaylist-1, 'file_id');
            $playlistID = $countQueuePlaylist;
        } else {  // Αν έχει οριστεί προηγούμενο playlist ID
            // στέλνει το προηγούμενο id από το $currentPlaylistID
            $previousPlaylistID = $currentPlaylistID-1;
            // Αν το προηγούμενο id είναι ίσο με το προτελευταίο id της λίστας, τότε αφαιρούμε ακόμη ένα
            // Αλλιώς θα παίξει δυο φορές το προτελευταίο
            if($previousPlaylistID==$countQueuePlaylist-1) {
                $previousPlaylistID--;
            }
            $fileID = RoceanDB::getTableFieldValue($tempPlayedQueuePlaylist, 'id=?', $previousPlaylistID, 'file_id');
            $playlistID = $previousPlaylistID;
        }

    }
}


if ($playlistID && $fileID)
    $jsonArray = array('success' => true,
        'playlist_id' => $playlistID,
        'file_id' => $fileID,
        'operation' => $operation);
else $jsonArray = array('success' => false);


echo json_encode($jsonArray);