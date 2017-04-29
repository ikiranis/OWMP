<?php
/**
 * File: getNextVideo.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 05/11/16
 * Time: 01:30
 * Επιστρέφει το επόμενο τραγούδι για να παίξει. Το file id του
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\User;
use apps4net\framework\Utilities;
use apps4net\framework\Progress;
use apps4net\parrot\app\OWMPElements;

require_once('../../src/boot.php');

session_start();
Page::checkValidAjaxRequest(true);

$user = new User();


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
MyDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));

$UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης


// Αν δεν είναι admin γίνεται true το $cantPlayVotes και δεν μπορεί να παίξει votes
if($UserGroup!=='1') {
    $cantPlayVotes = true;
} else {
    $cantPlayVotes = false;
}

if($operation=='next') { // όταν θέλουμε να παίξει το επόμενο

    if(!MyDB::countTable('votes') || $cantPlayVotes) {  // Αν δεν υπάρχουν ψήφοι στο votes
        if ($playMode == 'shuffle') {
            $tableCount = MyDB::countTable($tempUserPlaylist);
            $randomRow = rand(0, $tableCount);
            $return = OWMPElements::getRandomPlaylistID($tempUserPlaylist, $randomRow);
            $playlistID = $return['playlist_id'];
            $fileID = $return['file_id'];
        } else {
            $playlistID = $currentPlaylistID;
            $fileID = MyDB::getTableFieldValue($tempUserPlaylist, 'id=?', $currentPlaylistID, 'file_id');
        }
    } else {  // αλλιώς παίρνει το επόμενο τραγούδι από την καταμέτρηση των ψήφων

        // Ο δισδιάστατος πίνακας με τις ψήφους. Στην 1η στήλη είναι το fileID, στην 2η ο αριθμός των ψήφων
        $votesArray = OWMPElements::getVotes();

        // Παίρνει τα fileID που έχουν τις περισσότερες ψήφους
        $fileIDsWithMaxVotes=Utilities::getArrayMax($votesArray);
        
        $VotesCounter=count($fileIDsWithMaxVotes);
        
        // Αν υπάρχει ισοψηφία τότε παίρνει κάποιο random
        if($VotesCounter>1) {
            $RandomVote=rand(0,$VotesCounter-1);
            $getFileID=$fileIDsWithMaxVotes[$RandomVote];
        } else {  // Αλλιώς το μοναδικό που έχει τις περισσότερες ψήφους
            $getFileID=$fileIDsWithMaxVotes[0];
        }

        // Επιστρέφει τις τιμές για να παίξουν στον player
        $playlistID = $currentPlaylistID;
        $fileID = $getFileID;

        // Σβήνει όλες τις ψήφους για να αρχίσει η ψηφοφορία από την αρχή
        MyDB::deleteTable('votes');

        // Σβήνει την εγγραφή από την jukebox playlist
        $conn->deleteRowFromTable(JUKEBOX_LIST_NAME, 'file_id', $fileID);


    }
    
}


if($operation=='prev') {  // όταν θέλουμε να παίξει το προηγούμενο τραγούδι που θα πάρει από την queue playlist
    
    // Έλεγχος αν υπάρχουν τραγούδια στην playlist και του μεγέθους του table $tempPlayedQueuePlaylist
    if($countQueuePlaylist=MyDB::countTable($tempPlayedQueuePlaylist)) {
        
        if($currentPlaylistID==0) { // Αν δεν έχει οριστεί προηγούμενο playlist ID
            // στέλνει το προτελευταίο id της queue playlist, γιατί το τελευταίο id είναι το τραγούδι που παίζει
            $fileID = MyDB::getTableFieldValue($tempPlayedQueuePlaylist, 'id=?', $countQueuePlaylist-1, 'file_id');
            $playlistID = $countQueuePlaylist;
        } else {  // Αν έχει οριστεί προηγούμενο playlist ID
            // στέλνει το προηγούμενο id από το $currentPlaylistID
            $previousPlaylistID = $currentPlaylistID-1;
            // Αν το προηγούμενο id είναι ίσο με το προτελευταίο id της λίστας, τότε αφαιρούμε ακόμη ένα
            // Αλλιώς θα παίξει δυο φορές το προτελευταίο
            if($previousPlaylistID==$countQueuePlaylist-1) {
                $previousPlaylistID--;
            }
            $fileID = MyDB::getTableFieldValue($tempPlayedQueuePlaylist, 'id=?', $previousPlaylistID, 'file_id');
            $playlistID = $previousPlaylistID;
        }

    }
}


if ($playlistID && $fileID) {
    $jsonArray = array('success' => true,
        'playlist_id' => $playlistID,
        'file_id' => $fileID,
        'operation' => $operation);
    
    // Σετάρει στο currentSong στην βάση, πιο ειναι το τρέχον τραγούδι
    Progress::setCurrentSong($fileID);

    // Στέλνει στον icecast server
    if(ICECAST_ENABLE) {
        $songInfo = OWMPElements::getSongInfo($fileID);
        OWMPElements::sendToIcecast($songInfo[0]['song_name'] . ' : ' . $songInfo[0]['artist']);
    }
}
else $jsonArray = array('success' => false);


echo json_encode($jsonArray);