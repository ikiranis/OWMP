<?php
/**
 * File: getNextVideo.php
 * Created by rocean
 * Date: 05/11/16
 * Time: 01:30
 * Επιστρέφει το επόμενο τραγούδι για να παίξει. Το file id του
 */

// Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη κι επιστρέφει πίνακα με τις τιμές της πρώτης στήλης που έχουν την μέγιστη τιμή
function getArrayMax($myArray) {
    $myMax=0;

    // Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη
    foreach ($myArray as $row) {
        if($row[1]>$myMax) {
            $myMax=$row[1];
        }
    }

    // Επιστρέφει τις τιμές της πρώτης στήλης που έχουν την μεγαλύτερη τιμή
    foreach ($myArray as $row) {
        if($row[1]==$myMax) {
            $newArray[]=$row[0];
        }
    }

    return $newArray;

}


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

$UserGroup=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης


// Αν δεν είναι admin γίνεται true το $cantPlayVotes και δεν μπορεί να παίξει votes
if($UserGroup!=='1') {
    $cantPlayVotes = true;
} else {
    $cantPlayVotes = false;
}

if($operation=='next') { // όταν θέλουμε να παίξει το επόμενο

    if(!RoceanDB::countTable('votes') || $cantPlayVotes) {  // Αν δεν υπάρχουν ψήφοι στο votes
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
    } else {  // αλλιώς παίρνει το επόμενο τραγούδι από την καταμέτρηση των ψήφων

        // Ο δισδιάστατος πίνακας με τις ψήφους. Στην 1η στήλη είναι το fileID, στην 2η ο αριθμός των ψήφων
        $votesArray = OWMP::getVotes();

        // Παίρνει τα fileID που έχουν τις περισσότερες ψήφους
        $fileIDsWithMaxVotes=getArrayMax($votesArray);
        
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
        RoceanDB::deleteTable('votes');


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