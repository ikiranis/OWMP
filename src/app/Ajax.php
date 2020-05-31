<?php
/**
 *
 * File: Ajax.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 27/09/2017
 * Time: 12:30
 *
 * Όλες οι ajax μέθοδοι της AjaxRouting
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\Page;
use apps4net\framework\User;
use apps4net\framework\Utilities;
use apps4net\framework\Controller;
use apps4net\framework\Logs;
use apps4net\framework\FilesIO;
use apps4net\framework\Progress;
use apps4net\framework\ExternalAPI;
use apps4net\framework\FileUpload;


class Ajax extends Controller
{

    /**
     * Εισάγει ένα κομμάτι στην playlist
     */
    public function addToPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistID']))
            $playlistID=ClearString($_GET['playlistID']);

        if(isset($_GET['fileID']))
            $fileID=ClearString($_GET['fileID']);

        $conn = new myDB();

        // Παίρνει το όνομα του table για την συγκεκριμένο playlistID
        $playlistTableName = MyDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');

        if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

            // Ο τίτλος του τραγουδιού
            $songName = MyDB::getTableFieldValue('music_tags', 'id=?', array($fileID), 'song_name');

            if(!MyDB::getTableFieldValue($playlistTableName, 'file_id=?', array($fileID), 'id')) {
                $sql = 'INSERT INTO ' . $playlistTableName . ' (file_id) VALUES(?)';   // Εισάγει στον πίνακα $playlistTableName
                $playlistArray = array($fileID);

                if ($conn->insertInto($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στο $playlistTableName
                    $jsonArray = array('success' => true, 'song_name' => $songName);

                } else {
                    $jsonArray = array('success' => false, 'errorID'=> 1);   // Δεν έγινε η εγγραφή στην βάση
                }
            }
            else $jsonArray = array('success' => false, 'errorID'=> 2, 'song_name' => $songName); // υπάρχει ήδη το συγκεκριμένο fileID στην playlist
        }
        else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Ελέγχει ένα image αν είναι valid. Επιστρέφει true αν είναι εντάξει
     */
    public function checkValidImage()
    {
//        session_start();

        //Page::checkValidAjaxRequest(true);
        $images = new Images();

        if(isset($_GET['imagePath']))
            $imagePath=$_GET['imagePath'];

        if($myImage=$images->openImage($imagePath)) {
            $jsonArray = array('success' => true);
            imagedestroy($myImage);
        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Δημιουργεί μια playlist
     */
    public function createPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistName']))
            $playlistName=ClearString($_GET['playlistName']);

        $user = new User();
        $conn = new myDB();

        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        $playlistTableName = MANUAL_PLAYLIST_STRING.date('YmdHis');   // Το όνομα που θα πάρει το table του manual playlist

        if(OWMPElements::createPlaylistTempTable($playlistTableName)) {  // Αν δημιουργηθεί κανονικά το table του manual playlist
            $sql = 'INSERT INTO manual_playlists (table_name, playlist_name, user_id) VALUES(?,?,?)';   // Εισάγει στον πίνακα manual_playlists
            $playlistArray = array($playlistTableName, $playlistName, $userID);

            if($playlistID=$conn->insertInto($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στην manual_playlists
                $jsonArray = array('success' => true, 'playlistID' => $playlistID, 'playlistName' => $playlistName);

            }
            else {
                $jsonArray = array('success' => false, 'playlistName' => $playlistName);
            }
        }
        else {
            $jsonArray = array('success' => false, 'playlistName' => $playlistName);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Δημιουργεί μια smart playlist
     */
    public function createSmartPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistName']))
            $playlistName=ClearString($_GET['playlistName']);

        $user = new User();
        $conn = new myDB();

        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        $sql = 'INSERT INTO smart_playlists (playlist_name, user_id) VALUES(?,?)';   // Εισάγει στον πίνακα manual_playlists
        $playlistArray = array($playlistName, $userID);

        // Ψάχνει αν υπάρχει ήδη λίστα με συγκεκριμένο όνομα
        $searchPlaylist = MyDB::getTableArray('smart_playlists', 'playlist_name', 'playlist_name=?', array($playlistName), null, null, null);

        if(!$searchPlaylist) { // Αν δεν υπάρχει την εισάγουμε
            if($playlistID=$conn->insertInto($sql, $playlistArray)) {  // Αν γίνει κανονικά η εισαγωγή στην smart_playlists
                $jsonArray = array('success' => true, 'playlistID' => $playlistID, 'playlistName' => $playlistName);
            }
            else {
                $jsonArray = array('success' => false, 'playlistName' => $playlistName);
            }
        } else {
            $jsonArray = array('success' => false, 'playlistName' => $playlistName);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Σβήνει το αρχείο, μαζί με την αντίστοιχη εγγραφή στην βάση
     */
    public function deleteFile()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(OWMPElements::deleteFile($id)==true) {
            $jsonArray = array('success' => true, 'id' => $id);
            Logs::insertLog('Deleted song with id: '.$id); // Προσθήκη της κίνησης στα logs
        }
        else $jsonArray=array( 'success'=> false);

        echo json_encode($jsonArray);
    }

    /**
     * Σβήνει μόνο το αρχείο στον δίσκο και όχι και εγγραφή στην βάση
     */
    public function deleteOnlyTheFile()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['filename']))
            $filename=$_GET['filename'];

        if(isset($_GET['fullpath']))
            $fullpath=$_GET['fullpath'];

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if (FilesIO::deleteFile($fullpath)) {  // Αν υπάρχει ήδη στην βάση σβήνει το αρχείο στον δίσκο και βγάζει μήνυμα
            $jsonArray = array('success' => true, 'id' => $id);

            Logs::insertLog('File ' . $filename . ' deleted.'); // Προσθήκη της κίνησης στα logs
        }
        else $jsonArray=array( 'success'=> false);

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Σβήνει μια εγγραφή από τον πίνακα paths
     */
    public function deletePath()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        $conn = new MyDB();

        if($conn->deleteRowFromTable('paths','id',$id)) {
            $jsonArray=array( 'success'=>true);

            Logs::insertLog('Path deleted with id '. $id); // Προσθήκη της κίνησης στα logs

        }
        else $jsonArray=array( 'success'=>false);

        echo json_encode($jsonArray);
    }

    /**
     * Σβήνει μια manual playlist
     */
    public function deletePlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        $conn = new MyDB();

        if(isset($_GET['playlistID']))
            $playlistID=ClearString($_GET['playlistID']);

        // Παίρνει το όνομα του table για την συγκεκριμένο playlistID
        $playlistTableName = MyDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');

        if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

            // Σβήνει το table $playlistTableName
            if(MyDB::dropTable($playlistTableName)) {

                // Σβήνει το συγκεκριμένο row της playlist από το manual_playlists
                if ($conn->deleteRowFromTable('manual_playlists', 'id', $playlistID) ) {
                    $jsonArray = array('success' => true);
                } else $jsonArray = array('success' => false, 'errorID' => 1); // Δεν έγινε η διαγραφή του row

            } else $jsonArray = array('success' => false, 'errorID' => 2); // Δεν έγινε η διαγραφή του $playlistTableName

        }
        else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Σβήνει μια smart playlist
     */
    public function deleteSmartPlaylist()
    {
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
    }

    /**
     * Κάνει export την τρέχουσα playlist
     * Αντιγράφει τα αρχεία σε ένα directory και κάνει export την playlist σε json
     */
    public function exportPlaylist()
    {
        set_time_limit(0);

        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        Progress::setLastMomentAlive(false);

        $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;

        $playlistTable=MyDB::getTableArray($tempUserPlaylist, 'file_id', null, null, null, null, null);

        //trigger_error(OUTPUT_FOLDER);

        $checkOutputFolder = FilesIO::createDirectory(OUTPUT_FOLDER);
        if(!$checkOutputFolder['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
            exit($checkOutputFolder['message']);
        }

        SyncFiles::exportPlaylistJsonFile($tempUserPlaylist);

        Progress::setProgress(0);
        $general_counter=0;

        $totalFiles = count($playlistTable);

        foreach ($playlistTable as $item) {
            Progress::setLastMomentAlive(false);

            $file=MyDB::getTableArray('files','*', 'id=?', array($item['file_id']),null, null, null);
            $sourceFile=DIR_PREFIX.$file[0]['path'].$file[0]['filename'];
            $destFile=OUTPUT_FOLDER.$file[0]['filename'];

            //    trigger_error('SOURCE: '.$sourceFile.' DEST: '.$destFile);

            copy($sourceFile, $destFile);

            $progressPercent = intval(($general_counter / $totalFiles) * 100);

            Progress::setLastMomentAlive(true);

            Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

            $general_counter++;
        }
    }

    /**
     * Επιστρέφει το επόμενο τραγούδι για να παίξει. Το file id του
     */
    public function getNextVideo()
    {
        session_start();
        Page::checkValidAjaxRequest(true);

        $user = new User();
        $conn = new myDB();

        //trigger_error(TAB_ID);

        if(isset($_GET['currentPlaylistID']))
            $currentPlaylistID=intval($_GET['currentPlaylistID']);

        if(isset($_GET['playMode']))
            $playMode=ClearString($_GET['playMode']);

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        if(isset($_GET['operation']))
            $operation=ClearString($_GET['operation']);

        $tempUserPlaylist = CUR_PLAYLIST_STRING . $tabID;
        $tempPlayedQueuePlaylist = PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

        // Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
        $theDate = date('Y-m-d H:i:s');
        MyDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));

        $UserGroup = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        // Αν δεν είναι admin γίνεται true το $cantPlayVotes και δεν μπορεί να παίξει votes
        if($UserGroup!=='1') {
            $cantPlayVotes = true;
        } else {
            $cantPlayVotes = false;
        }

        $songKind = null;

        if($operation=='next') { // όταν θέλουμε να παίξει το επόμενο

            if(!MyDB::countTable('votes') || $cantPlayVotes) {  // Αν δεν υπάρχουν ψήφοι στο votes
                if ($playMode == 'shuffle') {
                    $tableCount = MyDB::countTable($tempUserPlaylist);
                    $randomRow = rand(0, $tableCount);
                    $return = OWMPElements::getRandomPlaylistID($tempUserPlaylist, $randomRow);
                    $playlistID = $return['playlist_id'];
                    $fileID = $return['file_id'];
                    $songKind = MyDB::getTableFieldValue('files', 'id=?', $fileID, 'kind');
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
                'operation' => $operation,
                'songKind' => $songKind);

            // Σετάρει στο currentSong στην βάση, πιο ειναι το τρέχον τραγούδι
            Progress::setCurrentSong($fileID);

            // Στέλνει στον icecast server
            if(ICECAST_ENABLE) {
                $songInfo = OWMPElements::getSongInfo($fileID);
                OWMPElements::sendToIcecast($songInfo[0]['song_name'] . ' : ' . $songInfo[0]['artist']);
            }
        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Επιστρέφει τα directories που βρίσκονται μέσα σε ένα path
     */
    public function getPaths()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['path']))
            $path=ClearString($_GET['path']);

        $paths = scandir($path);

        $onlyDirectories = array();

        if($paths) {
            foreach ($paths as $item) {
                if (is_dir($path . $item)) {
                    $onlyDirectories[] = $item;
                }
            }
        }

        echo json_encode($onlyDirectories, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Επιστρέφει τα στοιχεία του τραγουδιού που παίζει αυτή την στιγμή
     */
    public function getSongInfo()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        if($currentSong = OWMPElements::getSongInfo(null)) { // Τα στοιχεία του τραγουδιού
            $jsonArray = array('success' => true,
                'songName' => $currentSong[0]['song_name'],
                'artist' => $currentSong[0]['artist'],
                'fileID' => $currentSong[0]['id']);
        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Εμφανίζει τα votes
     */
    public function getSongVotes()
    {
            session_start();

            Page::checkValidAjaxRequest(false);

            // Ο δισδιάστατος πίνακας με τις ψήφους. Στην 1η στήλη είναι το fileID, στην 2η ο αριθμός των ψήφων
            $votesArray = OWMPElements::getVotes();

            ?>

            <ul>

                <?php
                foreach ($votesArray as $vote) {
                    $songInfo = OWMPElements::getSongInfo($vote['file_id']);

                    ?>

                    <li>
                        <input type="button" class="vote_button playlist_button_img"
                               title="<?php echo __('vote_song'); ?>"
                               onclick="voteSong(<?php echo $vote['file_id']; ?>);">
                        <span id="currentSongName"><?php echo $songInfo[0]['song_name']; ?></span>
                        <span id="currentSongArtist"><?php echo $songInfo[0]['artist']; ?></span> ::
                        <span id="numberOfVotes"><?php echo $vote['numberOfVotes']; ?></span>
                    </li>

                    <?php
                }

                ?>

            </ul>

            <?php

    }

    /**
     * Επιστρέφει τα metadata του αρχείου (video)
     */
    public function getVideoMetadata()
    {
        session_start();

        // TODO να κάνω έναν worker και να κάνει update το session από εκεί
        Page::updateUserSession();

        Page::checkValidAjaxRequest(true);

        $images = new Images();

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['onlyGiphy']))
            $onlyGiphy=ClearString($_GET['onlyGiphy']);
        else $onlyGiphy=null;

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        if(isset($_GET['getSmall']))
            $getSmall = ClearString($_GET['getSmall']);

        $file = MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);

        $filesArray=array('path'=>$file[0]['path'],
            'filename'=>$file[0]['filename'],
            'kind'=>$file[0]['kind']);

        if($metadata=MyDB::getTableArray('music_tags','*', 'id=?', array($id),null, null, null)) {

            if (isset($metadata[0]['rating'])) {
                $rating = ($metadata[0]['rating'] / 10) / 2;

            }
            else $rating='';

            if ($metadata[0]['song_year']==0)
                $song_year='';
            else $song_year=$metadata[0]['song_year'];

            $fromAPI=null;
            $apiSource='';

            if($file[0]['kind']=='Music') {
                // το Album cover, στο μέγεθος που ζητάει
                if($getSmall=='true') {
                    $albumCoverPath = $images->getAlbumImagePath($metadata[0]['album_artwork_id'], 'small');
                } else {
                    $albumCoverPath = $images->getAlbumImagePath($metadata[0]['album_artwork_id'], 'big');
                }

                //        if(!$iconImagePath = OWMPElements::getAlbumImagePath($metadata[0]['album_artwork_id'], 'ico')) {
                //            $iconImagePath=null;
                //        }


                // Χρησιμοποιεί το itunes ή giphy api για να πάρει artwork όταν δεν υπάρχει artwork στο τραγούδι
                if($metadata[0]['album_artwork_id']==DEFAULT_ARTWORK_ID) {

                    // Από itunes API
                    if ($iTunesArtwork = ExternalAPI::getItunesCover(htmlspecialchars_decode($metadata[0]['album']) . ' ' . htmlspecialchars_decode($metadata[0]['artist']), $getSmall)) {
                        $fromAPI = $iTunesArtwork;
                        $apiSource='iTunes';
                    }
                    else if ($giphy = ExternalAPI::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                        $fromAPI = $giphy;
                        $apiSource='Giphy';
                    }
                }

                // Αν έχουμε επιλέξει πάντα να εμφανίζει από giphy
                if($onlyGiphy=='true') {
                    if ($giphy = ExternalAPI::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                        $fromAPI = $giphy;
                        $albumCoverPath=null;
                        $apiSource='Giphy';
                    }
                    else $fromAPI=null;
                }

            }
            else {
                $albumCoverPath=null;
//                $iconImagePath=null;
            }

            $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
            $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

            // Εισάγει το συγκεκριμένο τραγούδι που παίζει στο Played Queue Playlist
            OWMPElements::insertIntoTempPlaylist($tempPlayedQueuePlaylist, $id);

            $playlistID=MyDB::getTableFieldValue($tempUserPlaylist, 'file_id=?', $id, 'id');

            $jsonArray = array('success' => true,
                'songID' => $metadata[0]['id'],
                'artist' => htmlspecialchars_decode($metadata[0]['artist']),
                'title' => htmlspecialchars_decode($metadata[0]['song_name']),
                'genre' => htmlspecialchars_decode($metadata[0]['genre']),
                'year' => $song_year,
                'album' => htmlspecialchars_decode($metadata[0]['album']),
                'play_count' => $metadata[0]['play_count'],
                'date_played' => $metadata[0]['date_last_played'],
                'date_added' => $metadata[0]['date_added'],
                'track_time' => $metadata[0]['track_time'],
                'live' => $metadata[0]['live'],
                'rating' => $rating,
                'albumCoverPath' => $albumCoverPath,
//        'iconImagePath' => $iconImagePath,
                'fromAPI'=>$fromAPI,
                'apiSource'=>$apiSource,
                'playlist_id' => $playlistID,
                'playlist_count' => $_SESSION['countThePlaylist']);

        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode(array('tags'=>$jsonArray,'file'=>$filesArray), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Φορτώνει την λίστα με τα τραγούδια που παίξανε
     */
    public function loadPlayedQueue()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
        $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

        if($tempPlayedQueuePlaylist) {  // Αν υπάρχει το συγκεκριμένο $tempPlayedQueuePlaylist

            // Σβήνει πρώτα τα περιεχόμενα του $tempUserPlaylist
            if(MyDB::deleteTable($tempUserPlaylist)) {

                // Αντιγράφει τον $tempPlayedQueuePlaylist στον $tempUserPlaylist
                if (MyDB::copyTable($tempPlayedQueuePlaylist, $tempUserPlaylist)) {
                    $jsonArray = array('success' => true);
                } else {
                    $jsonArray = array('success' => false, 'errorID' => 1);
                } // Δεν έγινε η αντιγραφή

            } else {
                $jsonArray = array('success' => false, 'errorID' => 2);
            } // Δεν έγινε η διαγραφή του $tempUserPlaylist

        } else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Αντιγραφή της manual playlist στην current playlist
     */
    public function loadPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistID']))
            $playlistID=ClearString($_GET['playlistID']);

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        $tempUserPlaylist = CUR_PLAYLIST_STRING . $tabID;

        // Παίρνει το όνομα του table για την συγκεκριμένο playlistID
        $playlistTableName = MyDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');

        if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

            // Σβήνει πρώτα τα περιεχόμενα του $tempUserPlaylist
            if(MyDB::deleteTable($tempUserPlaylist)) {

                // Αντιγράφει τον $playlistTableName στον $tempUserPlaylist
                if (MyDB::copyTable($playlistTableName, $tempUserPlaylist)) {
                    $jsonArray = array('success' => true);
                } else {
                    $jsonArray = array('success' => false, 'errorID' => 1);
                } // Δεν έγινε η αντιγραφή

            } else {
                $jsonArray = array('success' => false, 'errorID' => 2);
            } // Δεν έγινε η διαγραφή του $tempUserPlaylist

        }
        else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Φορτώνει μία smart playlist και επιστρέφει το json string
     */
    public function loadSmartPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistID'])) {
            $playlistID = ClearString($_GET['playlistID']);
        }

        // Ψάχνει αν υπάρχει η συγκεκριμένη λίστα
        $smartPlaylist = MyDB::getTableArray('smart_playlists', 'id, playlist_data, playlist_name', 'id=?', array($playlistID), null, null, null);

        if($smartPlaylist) {  // Αν υπάρχει η συγκεκριμένη εγγραφή
            $jsonArray = array('success' => true,
                'searchJsonArray' => $smartPlaylist[0]['playlist_data'],
                'playlistName' => $smartPlaylist[0]['playlist_name']);

        } else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο row στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Αφαίρεση κομματιού από την playlist
     */
    public function removeFromPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistID']))
            $playlistID=ClearString($_GET['playlistID']);

        if(isset($_GET['fileID']))
            $fileID=ClearString($_GET['fileID']);

        $conn = new myDB();

        // Παίρνει το όνομα του table για την συγκεκριμένο playlistID
        $playlistTableName = MyDB::getTableFieldValue('manual_playlists', 'id=?', array($playlistID), 'table_name');

        if($playlistTableName) {  // Αν υπάρχει το συγκεκριμένο $playlistTableName

            // Ο τίτλος του τραγουδιού
            $songName = MyDB::getTableFieldValue('music_tags', 'id=?', array($fileID), 'song_name');

            // Αν υπάρχει η συγκεκριμένη εγγραφή στο $playlistTableName
            if(MyDB::getTableFieldValue($playlistTableName, 'file_id=?', array($fileID), 'id')) {

                if($conn->deleteRowFromTable($playlistTableName, 'file_id', $fileID)) {
                    $jsonArray = array('success' => true, 'song_name' => $songName, 'fileID' => $fileID);

                } else {
                    $jsonArray = array('success' => false, 'errorID'=> 1);   // Δεν έγινε η διαγραφή του row από τον πίνακα $playlistTableName
                }
            } else {
                $jsonArray = array('success' => false, 'errorID'=> 2, 'song_name' => $songName);
            } // δεν υπάρχει το συγκεκριμένο fileID στην playlist
        } else {
            $jsonArray = array('success' => false, 'errorID'=> 3);  // Δεν υπάρχει το συγκεκριμένο $playlistTableName στην βάση
        }

        echo json_encode($jsonArray);
    }

    /**
     * Σώζει το search query σε smart playlist, σε μορφή json
     */
    public function saveSmartPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['playlistID']))
            $playlistID=ClearString($_GET['playlistID']);

        if(isset($_GET['searchJsonString']))
            $searchJsonString=urldecode($_GET['searchJsonString']);

        $update=MyDB::updateTableFields('smart_playlists', 'id=?',
            array('playlist_data'),
            array($searchJsonString, $playlistID));

        if ($update) { // Ενημερώνει την εγγραφή με το $searchJsonString
            $jsonArray = array('success' => true);
        } else { // Δεν έγινε το update του row
            $jsonArray = array('success' => false, 'errorID' => 1);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Κάνει το search στην playlist
     */
    public function searchPlaylist()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        $playlist = new PlaylistSearch();

        if(isset($_GET['offset']))
            $offset=ClearString($_GET['offset']);
        else $offset=0;

        if(isset($_GET['step']))
            $step=ClearString($_GET['step']);
        else $step=PLAYLIST_LIMIT;

        if(isset($_GET['jsonArray']))  // Παίρνει τα δεδομένα σε πίνακα από JSON
            $jsonArray=json_decode($_GET['jsonArray'],true);
        else $jsonArray=null;

        if(isset($_GET['mediaKind']))
            if(!$_GET['mediaKind']=='')
                $mediaKind = ClearString($_GET['mediaKind']);
            else $mediaKind=null;
        else $mediaKind=null;

        if(isset($_GET['firstTime'])) {
            $firstTime = ClearString($_GET['firstTime']);

            if($firstTime=='true') {
                $_SESSION['PlaylistCounter'] = 0;
            }
        }

        if(isset($_GET['duplicates']))
            $duplicates=true;
        else $duplicates=false;

        if(isset($_GET['queue']))
            $playedQueue=true;
        else $playedQueue=false;

        if(isset($_GET['loadPlaylist']))
            $loadPlaylist=true;
        else $loadPlaylist=null;

        if(isset($_GET['votePlaylist']))
            $votePlaylist=true;
        else $votePlaylist=null;

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);
        else $tabID = null;

        if(isset($_GET['sort_by']))
            $sort_by = ClearString($_GET['sort_by']);
        else $sort_by = 'date_added';

        if(isset($_GET['order']))
            $order = ClearString($_GET['order']);
        else $order = 'DESC';

        if(isset($_GET['currentBrowsePage']))
            $currentBrowsePage = ClearString($_GET['currentBrowsePage']);
        else $currentBrowsePage = 0;

        $playlist->fieldsArray = $jsonArray;
        $playlist->offset = $offset;
        $playlist->step = $step;
        $playlist->duplicates = null;
        $playlist->mediaKind = $mediaKind;
        $playlist->tabID = $tabID;
        $playlist->loadPlaylist = null;
        $playlist->votePlaylist = false;
        $playlist->currentBrowsePageNo = $currentBrowsePage;
        $playlist->sort_by = $sort_by;
        $playlist->order = $order;

        if($duplicates==false && $playedQueue==false && $loadPlaylist==false && $votePlaylist==false) {
            $playlist->getPlaylist();
        }
        else {
            if ($loadPlaylist == true) {
                $playlist->loadPlaylist = $loadPlaylist;
            }
            if($duplicates==true) {
                $playlist->duplicates = $duplicates;
            }
            if($votePlaylist==true) {
                $playlist->fieldsArray = null;
                $playlist->mediaKind = null;
                $playlist->tabID = null;
                $playlist->votePlaylist = $votePlaylist;
            }
            //    if($playedQueue==true) {
            //        $OWMPElements->getPlaylist();
            //    }

            $playlist->getPlaylist();
        }
    }

    /**
     * Αντιγράφει την τρέχουσα playlist στην jukebox list
     */
    public function sendToJukeBox()
    {
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
    }

    /**
     * Επιστρέφει ένα αρχείο σε binary μορφή
     * Κάνει χρήση της κλάσης MediaStream
     */
    public function serveFile()
    {
        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['path']))
            $path=ClearString($_GET['path']);

        if(isset($id)) {
            $file=MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);
            $fullPathFilename = DIR_PREFIX.$file[0]['path'].$file[0]['filename'];
        } else {
            if(isset($path)) {
                $fullPathFilename = $path;
            }
        }

        $streamFile = new MediaStream($fullPathFilename);
        $streamFile->start();
    }

    /**
     * Επιστρέφει το album cover image σε binary
     */
    public function serveImage()
    {
        if(isset($_GET['imagePath']))
            $imagePath=ClearString($_GET['imagePath']);

        $streamFile = new MediaStream($imagePath);
        $streamFile->start();
    }

    /**
     * Κάνει τον συγχρονισμό των αρχείων
     */
    public function syncTheFiles()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        // TODO όλα τα data σε όλες τις μεθόδους να παίρνονται row
        // Τα row data που έρχονται από javascript
        $results = file_get_contents ('php://input');
        $results = json_decode($results, TRUE);

        if(isset($results['operation']))
            $operation=ClearString($results['operation']);

        if(isset($results['mediakind']))
            $mediaKind=ClearString($results['mediakind']);

        $sync = new SyncFiles();

        if($operation=='sync')
            $sync->syncTheFiles($mediaKind);

        if($operation=='clear')
            $sync->clearTheFiles();

        if($operation=='hash')
            $sync->hashTheFiles($mediaKind);

        if($operation=='metadata')
            $sync->filesMetadata();

        if($operation=='json_import')
            $sync->importPlaylistToDB();

        if($operation=='coverConvert')
            $sync->convertCovers();
    }

    /**
     * Ενημερώνει το download path στο table download_paths
     */
    public function updateDownloadPath()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['pathName'])) {
            $pathName = ClearString($_GET['pathName']);
        }

        if(isset($_GET['filePath'])) {
            $filePath = ClearString($_GET['filePath']);
        }

        MyDB::createConnection();

        $sql = 'UPDATE download_paths SET file_path=? WHERE path_name=?';
        $SQLparams=array($filePath, $pathName);

        $stmt = MyDB::$conn->prepare($sql);

        if($stmt->execute($SQLparams)) {
            $jsonArray=array( 'success'=>true, 'pathName'=>$pathName);

            Logs::insertLog('Download path updated with name '.$pathName); // Προσθήκη της κίνησης στα logs
        }
        else $jsonArray=array( 'success'=>false, 'pathName'=>$pathName);

        echo json_encode($jsonArray);

        $stmt->closeCursor();
        $stmt = null;
    }

    /**
     * Ενημερώνει την βάση με τα νέα filepath και filename
     */
    public function updateFile()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['filename']))
            $filename=$_GET['filename'];

        if(isset($_GET['path']))
            $path=$_GET['path'];

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['newID']))
            $newID=ClearString($_GET['newID']);

        $update = MyDB::updateTableFields('files', 'id=?',
            array('path', 'filename'),
            array($path, $filename, $id));

        if($update) {

            $conn = new MyDB();

            // Διαγραφή της νέας εγγραφής που έγινε για το ίδιο αρχείο
            if($deleteMusicTags=$conn->deleteRowFromTable ('music_tags','id',$newID)) {
                if ($deleteFile = $conn->deleteRowFromTable('files', 'id', $newID)) {
                    echo '<p>' . __('the_file') . ' ' . $filename . ' ' . __('changed_path') . '</p>';

                    $jsonArray = array('success' => true, 'id' => $id);

                    trigger_error($id . '  File ' . $filename . ' change path.');

                    Logs::insertLog('File ' . $filename . ' change path.'); // Προσθήκη της κίνησης στα logs
                } else {
                    $jsonArray = array('success' => false);
                }
            } else {
                $jsonArray = array( 'success'=> false);
            }

        } else {
            $jsonArray = array( 'success'=> false);
        }

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Προσθέτει ή ενημερώνει μια γραμμή στον πίνακα paths
     */
    public function updatePath()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['file_path']))
            $file_path=ClearString($_GET['file_path']);

        if(isset($_GET['kind']))
            $kind=ClearString($_GET['kind']);

        $conn = new MyDB();
        MyDB::createConnection();

        if ($id==0) {  // Αν το id είναι 0 τότε κάνει εισαγωγή
            $sql = 'INSERT INTO paths (file_path, kind) VALUES (?,?)';
            $SQLparams=array($file_path, $kind);
        }

        else {   // αλλιώς κάνει update
            $sql = 'UPDATE paths SET file_path=?, kind=? WHERE id=?';
            $SQLparams=array($file_path, $kind, $id);
        }

        $stmt = MyDB::$conn->prepare($sql);

        if($stmt->execute($SQLparams)) {
            if($id==0) {
                $inserted_id=MyDB::$conn->lastInsertId();
                $jsonArray=array( 'success'=>true, 'lastInserted'=>$inserted_id, 'id'=>$id);

                Logs::insertLog('Insert of new Path: '.$file_path); // Προσθήκη της κίνησης στα logs
            } else  {
                $jsonArray=array( 'success'=>true, 'id'=>$id);

                Logs::insertLog('Path updated with id '.$id); // Προσθήκη της κίνησης στα logs
            }

        } else {
            $jsonArray=array( 'success'=>false, 'id'=>$id);
        }

        echo json_encode($jsonArray);

        $stmt->closeCursor();
        $stmt = null;
    }

    /**
     * Ενημέρωση των tags ενός βίντεο
     */
    public function updateTags()
    {
        session_start();

        Page::updateUserSession();

        Page::checkValidAjaxRequest(true);

        $user = new User();
        $conn = new myDB();
        $images = new Images();

        $UserGroup = $user->getUserGroup($conn->getSession('username'));

        if ($UserGroup==1) { // Αν ο χρήστης είναι admin

            $fieldsArray = array();  // το array των ονομάτων των πεδίων
            $valuesArray = array();  // το array με τις τιμές των πεδίων

            if (isset($_POST['id']))
                $id = ClearString($_POST['id']);

            if (isset($_POST['song_name']) && !$_POST['song_name']=='') {
                $song_name = ClearString($_POST['song_name']);
                $fieldsArray[]='song_name';
                $valuesArray[]=$song_name;
            }

            if (isset($_POST['artist']) && !$_POST['artist']=='') {
                $artist = ClearString($_POST['artist']);
                $fieldsArray[]='artist';
                $valuesArray[]=$artist;
            }

            if (isset($_POST['genre']) && !$_POST['genre']=='') {
                $genre = ClearString($_POST['genre']);
                $fieldsArray[]='genre';
                $valuesArray[]=$genre;
            }

            if (isset($_POST['song_year']) && !$_POST['song_year']=='') {
                $song_year = intval($_POST['song_year']);
                $fieldsArray[]='song_year';
                $valuesArray[]=$song_year;
            }

            if (isset($_POST['album']) && !$_POST['album']=='') {
                $album = ClearString($_POST['album']);
                $fieldsArray[]='album';
                $valuesArray[]=$album;
            }

            if (isset($_POST['rating']) && !$_POST['rating']=='') {
                $rating = intval($_POST['rating']);
                $rating = $rating * 20;
                $fieldsArray[]='rating';
                $valuesArray[]=$rating;
            }

            if (isset($_POST['live']) && !$_POST['live']=='') {
                $live = intval($_POST['live']);
                $fieldsArray[]='live';
                $valuesArray[]=$live;
            }

            if (isset($_POST['coverImage']) && !$_POST['coverImage']=='') {
                $coverMime = $_POST['coverMime'];

                // Απαραίτητες μετατροπές του dataurl για να σωθεί σε αρχείο
                $coverImage = str_replace(' ','+',$_POST['coverImage']);
                $coverImage =  substr($coverImage,strpos($coverImage,",")+1);
                $coverImage = base64_decode($coverImage);

                $albumCoverID = $images->uploadAlbumImage($coverImage,$coverMime); // Ανεβάζει το αρχείο της εικόνας και παίρνει το album_artwork_id
                $fieldsArray[]='album_artwork_id';
                $valuesArray[]=$albumCoverID;
            }

            $valuesArray[] = $id;

            $update = MyDB::updateTableFields('music_tags', 'id=?', $fieldsArray, $valuesArray);

            if ($update) {
                $jsonArray = array('success' => true, 'id' => $id);
                Logs::insertLog('Changed tags for song id: '. $id); // Προσθήκη της κίνησης στα logs
            } else {
                $jsonArray = array('success' => false);
            }

        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ενημερώνει τo date_last_played και το play_count ενός βίντεο
     */
    public function updateTimePlayed()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        $date_last_played=date('Y-m-d H:i:s');
        $play_count=MyDB::getTableFieldValue('music_tags', 'id=?', $id, 'play_count');

        $play_count++;

        $update=MyDB::updateTableFields('music_tags', 'id=?',
            array('date_last_played', 'play_count'),
            array($date_last_played, $play_count, $id));

        if($update) {
            // Επιστρέφει το νέο play_count και το νέο date_last_played
            $jsonArray=array( 'success'=>true, 'play_count'=>$play_count, 'date_last_played'=>$date_last_played);
        } else {
            $jsonArray=array( 'success'=>false);
        }

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Σώζει ένα αρχείο που κάναμε upload
     */
    public function uploadFile()
    {
        session_start();
        Page::checkValidAjaxRequest(true);

        $myFile = $_POST['myFile'];

        $file = new FilesIO(OUTPUT_FOLDER, TEMP_RESTORE_DATABASE_FILE, 'write');

        $file->insertRow($myFile);
    }

    /**
     * Ανεβάζει ένα αρχείο
     */
    public function uploadMediaFile()
    {
        // TODO να δω πως μπορεί να γίνει πιο γρήγορο το upload

        // TODO να μπαίνουν οι παρακάτω 2 γραμμές σε όλες τις μεθόδους αυτόματα
        session_start();
        Page::checkValidAjaxRequest(true);

        // TODO όλα τα data σε όλες τις μεθόδους να παίρνονται row
        // Τα row data που έρχονται από javascript
        $results = file_get_contents ('php://input');
        $results = json_decode($results, TRUE);

        if($results['uploadKind']=='slice') {
            $fileUpload = new FileUpload($results['file_data'], $results['file_type'], $results['file']);

            $fileUpload->ajaxUploadFile();
        } else {

            $syncFile = new SyncFiles();

            // Παράγει το file path από το έτος και τον μήνα και ελέγχει το είδος του αρχείου
            if (strpos(strtolower($results['file_type']), 'video')!==false) {
                $syncFile->mediaKind = 'Music Video';
            } else {
                $syncFile->mediaKind = 'Music';
            }

            if(file_exists($results['fullPathFilename'])) {
                // Εγγραφή στην βάση του τραγουδιού που κατέβηκε
                DIR_PREFIX == '/' ?
                    $syncFile->file = $results['fullPathFilename'] :
                    $syncFile->file = str_replace(DIR_PREFIX, '', $results['fullPathFilename']);
                $syncFile->searchIDFiles = true;
                $syncFile->name = $results['fileName'];

                $syncFile->writeTrack();

                $jsonArray = array('success' => true, 'result' => $results['fullPathFilename'],
                    'filesToDelete' => $syncFile->deleteFilesString);
            } else {
                $jsonArray=array( 'success'=> false, 'fileName' => $results['fullPathFilename']);
            }

            echo json_encode($jsonArray);
        }
    }

    /**
     * Προσθέτει μία ψήφο για το τραγούδι
     */
    public function voteSong()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(OWMPElements::voteSong($id)) {
            $jsonArray = array('success' => true, 'id' => $id);
        } else {
            $jsonArray=array( 'success'=> false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Προσθέτει το τραγούδι στην ουρά
     */
    public function queueSong()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(OWMPElements::queueSong($id)) {
            $jsonArray = array('success' => true, 'id' => $id);
        } else {
            $jsonArray=array( 'success'=> false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Convert an audio file to lower bitrate
     */
    public function convertAudioToLowerBitRate()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        if(isset($_GET['id'])){
            $id = ClearString($_GET['id']);
        }

        if(isset($_GET['tabID'])) {
            $tabID=ClearString($_GET['tabID']);
        }

        // Παίρνουμε το full path του συγκεκριμένου αρχείου
        $file = MyDB::getTableArray('files','path, filename', 'id=?', array($id),null, null, null);

        $fullPath = DIR_PREFIX . $file[0]['path'] . $file[0]['filename'];

        $tempPath = LOW_BITRATE_TEMP_FOLDER . $tabID . '/';
        $filename = time() . '.mp3';

        FilesIO::createDirectory($tempPath);

        $execCommand = 'lame -f --mp3input -b '. LOW_AUDIO_BITRATE . ' "' . $fullPath . '" "' . $tempPath . $filename . '" 2>&1';

        $output = array();
        $result = -1;

        $script_start = microtime(true);
        exec($execCommand, $output, $result);
        $script_time_elapsed_secs = microtime(true) - $script_start;

        if($result === 0) {
            $finalOutput = $output[count($output)-2];

            $jsonArray = array(
                    'success' => true,
                    'result' => $finalOutput,
                    'time' => $script_time_elapsed_secs,
                    'fullPath' => $fullPath,
                    'tempFile' => $tempPath . $filename
            );
        } else {
            $jsonArray = array('success' => false, 'errorCode' => $result);
        }

        echo json_encode($jsonArray);
    }

}
