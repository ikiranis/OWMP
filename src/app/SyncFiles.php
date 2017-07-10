<?php

/**
 * File: SyncFiles.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 28/06/16
 * Time: 19:01
 *
 * Κλαση συγχρονισμού αρχείων
 *
 */


namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\Logs;
use apps4net\framework\Progress;
use apps4net\framework\Utilities;
use apps4net\framework\ScanDir;
use apps4net\framework\FilesIO;

// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once('../../src/external/PlistParser.php');

// @source https://github.com/JamesHeinrich/getID3/
require_once('../../src/external/getid3/getid3.php');

class SyncFiles
{
    static $files = array();
    static $tracks = array();
    static $tags = array();
    static $jsonTable = array();
    static $getID3;

    public $name = '';
    public $artist = '';
    public $genre = '';
    public $date_added = '';
    public $play_date = '';
    public $album = '';

    public $play_count = 0;
    public $rating = 0;
    public $size = 0;
    public $track_time = 0;
    public $video_width = 0;
    public $video_height = 0;
    public $album_artwork_id = 0;
    public $year = 0;
    public $live = 0;
    public $codec = '';

    public $stmt_file;  // Prepare files insert
    public $stmt_tags;  // Prepage Music Tags insert
    public $filesOnDB;  // Τα αρχεία που υπάρχουν στην βάση

    public $file; // To αρχείο για εισαγωγή
    public $filename; // Το όνομα του αρχείου
    public $path; // To path του αρχείου
    public $fullPathName; // Το πλήρες path μαζί με το filename
    public $hash; // το hash του αρχείου

    public $mediaKind;  // To media kind του αρχείου
    public $searchItunes;  // Αν θέλουμε να κάνει import από itunes
    public $searchIDFiles;  // Αν θέλουμε να τραβήξει metadata από το αρχείο

    public $inserted_id;  // To id της εγγραφής που έγινε στο files

    public $itunes_counter = 0; // μετρητής με τα αρχεία που βρέθηκαν στο itunes
    public $added_video = 0;  // μετρητής με τα αρχεία που προσθέτηκαν
    public $general_counter = 0;  // γενικός μετρητής

    public $script_start; // Ο μετρητής του χρόνου του process

    public $deleteFilesString;  // Το string που περιέχει τα αρχεία για διαγραφή

    static $filesForDelete = array();
    static $filesForUpdate = array();

    function __construct()
    {
        $this->startingValues();
    }

    // Διάβασμα της library στο itunes
    public function getItunesLibrary()
    {

        $parser = new \plistParser();
        $plist = $parser->parseFile(ITUNES_LIBRARY_FILE);

        self::$tracks = $plist['Tracks'];
        self::$tags = $plist['Tracks'];

        $trimTracks = array();
        foreach (self::$tracks as $track) {
            $replace_text = array('file:///Volumes/', 'file://localhost/Volumes/');

            if(isset($track['Location']))
                $location = urldecode(str_replace($replace_text, '', $track['Location']));

            $trimTracks[$track['Track ID']] = $location;
        }
        self::$tracks = $trimTracks;

//                    echo'<pre>';
//        print_r(self::$tracks);
//        echo'</pre>';
//
//            exit('stop');
//
//            echo count(self::$tracks);


    }
    

    // Διάβασμα των αρχείων στα directory που δίνει ο χρήστης
    public function scanFiles($mediakind)
    {
        $conn= new MyDB();

        // TODO να γίνεται έλεγχος αν υπάρχουν τα paths. Αλλιώς να επιστρέφει false
        $dirs = $conn->getTableArray('paths', 'file_path', 'kind=?', array($mediakind), null, null, null); // Παίρνει τα paths
        $dirs=$conn->clearArray($dirs);

        foreach ($dirs as $dir) {  // Έλεγχος αν υπάρχουν οι φάκελοι

            if(is_dir($dir)) {
                $dirs[]=$dir;
            }
            else {
//                trigger_error($dir);
                echo __('path_does_not_exist').' '.$dir;
            }
        }

        switch ($mediakind) {
            case 'Music Video': $extensions = array('mp4', 'm4v'); break;
            case 'Music': $extensions = array('mp3', 'm4a'); break;
        }

        if($dirs) {
            self::$files = ScanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs

            self::$files = array_unique(self::$files);
            $trimFiles = array();

            foreach (self::$files as $file) {
                if (strpos($file, '._') == false) {
                    if (DIR_PREFIX != '/')
                        $trimFiles[] = urldecode(str_replace(DIR_PREFIX, '', $file));
                    else $trimFiles[] = urldecode(substr($file, 1));
                }
            }

            self::$files = $trimFiles;

            return true;
        } else {
            return false;
        }



    }


    // Αρχικοποίηση τιμών
    public function startingValues($filename=null) {
        // Αρχικοποίηση τιμών
        $replace_text = array('.mp4', '.m4v', '.mp3', 'm4a');

        if($filename) {
            $this->name = str_replace($replace_text, '', $filename);
        }
        $this->artist = '';
        $this->genre = '';
        $this->date_added = date('Y-m-d H:i:s');
        $this->track_time = 0;
        $this->video_width = 0;
        $this->video_height = 0;
        $this->size = 0;

        $this->play_date = null;
        $this->album = '';
        $this->play_count = 0;
        $this->rating = 0;
        $this->album_artwork_id = 1;
        $this->year = 0;
        $this->live = 0;
        $this->codec = '';
    }


    // Παίρνει τις τιμές από την itunes library
    public function getItunesValues($track_id) {
        if (isset(self::$tags[$track_id]['Name'])) {
            $this->name = ClearString(self::$tags[$track_id]['Name']);
        }

        if (isset(self::$tags[$track_id]['Artist'])) {
            $this->artist = ClearString(self::$tags[$track_id]['Artist']);
        }

        if (isset(self::$tags[$track_id]['Album'])) {
            $this->album = ClearString(self::$tags[$track_id]['Album']);
        }

        if (isset(self::$tags[$track_id]['Genre'])) {
            $this->genre = ClearString(self::$tags[$track_id]['Genre']);
            $this->genre=substr($this->genre,0,19);
        }

        if (isset(self::$tags[$track_id]['Date Added']))
            $this->date_added = date('Y-m-d H:i:s', strtotime(self::$tags[$track_id]['Date Added']));

        if (isset(self::$tags[$track_id]['Play Count']))
            $this->play_count = intval(self::$tags[$track_id]['Play Count']);

        if (isset(self::$tags[$track_id]['Play Date']))
            $this->play_date = date('Y-m-d H:i:s', strtotime(self::$tags[$track_id]['Play Date UTC']));

        if (isset(self::$tags[$track_id]['Rating']))
            $this->rating = intval(self::$tags[$track_id]['Rating']);

        if (isset(self::$tags[$track_id]['Year']))
            $this->year = intval(self::$tags[$track_id]['Year']);

        if (isset(self::$tags[$track_id]['Comments']))
            if (self::$tags[$track_id]['Comments'] == 'Live')
                $this->live = 1;
    }

    // δημιουργούμε τους φακέλους που χρειαζόμαστε για την μουσική. Album Arts, Converted files
    public function createDirectories()
    {
        // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
        $checkAlbumCoversDir=FilesIO::createDirectory(ALBUM_COVERS_DIR);
        if(!$checkAlbumCoversDir['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
            exit($checkAlbumCoversDir['message']);
        }

        if(!is_dir(ALBUM_COVERS_DIR . 'default.gif'))   // Αν δεν υπάρχει το default.gif το αντιγράφουμε
            copy('../img/default.gif', ALBUM_COVERS_DIR . 'default.gif');

        if(CONVERT_ALAC_FILES) {
            // Έλεγχοι φακέλων που χρειάζονται
            $ckeckInternalConvertPath = FilesIO::createDirectory(INTERNAL_CONVERT_PATH);
            if(!$ckeckInternalConvertPath['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
                exit($ckeckInternalConvertPath['message']);
            }

            $checkMusicUpload = FilesIO::createDirectory(MUSIC_UPLOAD);
            if(!$checkMusicUpload['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
                exit($checkMusicUpload['message']);
            }
        }

    }

    // Κάνε prepare τα inserts
    public function prepareInserts()
    {
        $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

        $sql_insert_tags = 'INSERT INTO music_tags (id, song_name, artist, genre, date_added, play_count, 
                          date_last_played, rating, album, album_artwork_id, video_width, video_height, filesize, track_time, song_year, live) 
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $this->stmt_file = MyDB::$conn->prepare($sql_insert_file);
        $this->stmt_tags = MyDB::$conn->prepare($sql_insert_tags);
    }

    // Παίρνουμε τις εγγραφές στο table files σε array
    public function getFilesOnDB()
    {
        $conn = new MyDB();
        $conn->CreateConnection();

        if(!$this->filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null, null, null)) // Ολόκληρη η λίστα
            $this->filesOnDB='';
        else {
            foreach ($this->filesOnDB as $file) {
                $newFilesOnDB[$file['id']] = $file['path'] . $file['filename'];
            }
            $this->filesOnDB = $newFilesOnDB;
        }
    }

    // Έλεγχος αν το αρχείο υπάρχει ήδη στην βάση
    public function checkIfFileExistsOnDB($checkOnArray)
    {
        if($checkOnArray) {  // Ψάχνει στο array
            if(is_array($this->filesOnDB)){  // Έλεγχος αν το αρχείο υπάρχει στην βάση
                if($fileKey=array_search($this->file, $this->filesOnDB)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {  // Ψάχνει στην βάση απευθείας
            if($fileInDB = MyDB::getTableArray('files', 'id, path, filename',
                'path=? AND filename=?', array($this->path, $this->filename), null, null, null)) {
                return true;
            } else {
                return false;
            }
        }

    }

    public function checkIfFileExists()
    {
        if(FilesIO::fileExists($this->fullPathName)) { // Αν το αρχείο υπάρχει
            $this->hash = self::hashFile($this->fullPathName);  // Παίρνουμε το hash από το συγκεκριμένο αρχείο

            if ($searchHash = self::searchForHash($this->hash)) { // Έλεγχος στην βάση για to hash

                $oldFullPath = DIR_PREFIX . OWMPElements::getFullPathFromFileID($searchHash);  // To fullpath του αρχείου που βρέθηκε

                if (!FilesIO::fileExists($oldFullPath)) {  // Αν το παλιό αρχείο στο fullpath δεν βρεθεί

                    self::$filesForUpdate[] = [  // Πίνακας με τα id των προς διαγραφή αρχείων
                        'id' => $searchHash,
                        'filename' => $this->filename,
                        'path' => $this->path
                    ];

                    trigger_error('UPDATE ' . $this->hash . ' FILENAME ' . $this->filename);

                } else {  // Αν το παλιό αρχείο στο fullpath βρεθεί, τότε σβήνει το καινούργιο

                    self::$filesForDelete[] = [  // Πίνακας με τα filepath των προς διαγραφή αρχείων
                        'id' => $searchHash,
                        'filename' => $this->filename,
                        'fullpath' => $this->fullPathName
                    ];


                    trigger_error('DIAGRAFH ' . $this->hash . ' FILENAME ' . $this->filename);

                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    // Έλεγχος και μετατροπή του ALAC
    public function checkAndConvertALAC()
    {
        if ($this->codec == 'Apple Lossless Audio Codec') {   // Αν το αρχείο είναι ALAC
            if (CONVERT_ALAC_FILES) { // Αν θέλουμε να μετατραπεί
                //  Έλεγχος αν είναι εγκατεστημένες οι εφαρμογές ffmpeg και lame, που χρειάζονται
                if(Utilities::checkIfLinuxProgramInstalled('ffmpeg') && Utilities::checkIfLinuxProgramInstalled('lame')) {
                    if ($newPath = self::convertALACtoMP3($this->fullPathName, $this->filename, $this->path)) {  // Το μετατρέπουμε και το παίρνουμε
                        $this->path = $newPath['path'];                        //  από την νεά τοποθεσία που έχει δημιουργηθεί
                        $this->hash = self::hashFile(DIR_PREFIX . $this->path . $this->filename);
                    } else {
                        echo '<p>' . __('there_is_a_problem_with_alac') . '. ' . __('special_char_in_path') . ' ' . $this->fullPathName . '</p>';
                        return true;
                    }
                } else {
                    echo '<p>'. __('no_programs_exist_for_alac') . ' ' .  $this->fullPathName . '</p>';
                    return true;
                }
            } else {
                return true;
            }  // Αν δεν θέλουμε να μετατραπεί ή υπάρχει λάθος, τότε θέτουμε τιμή για να μην συνεχίσει η εγγραφή στην βάση
        }

    }

    // Εγγραφή στο table files
    public function writeTheFile() {
        $sqlParamsFile = array($this->path, $this->filename, $this->hash, $this->mediaKind);

        if ($this->stmt_file->execute($sqlParamsFile)) {  // Αν η εγγραφή είναι επιτυχής
            $this->inserted_id = MyDB::$conn->lastInsertId();  // παίρνουμε το id για χρήση αργότερα
        } else {
            $this->inserted_id = 0;
            trigger_error('PROBLEM!!!!!!!!!!     $path ' . $this->path . ' $filename ' . $this->filename);
        }

    }

    // Ψάχνει αν υπάρχει στην λίστα του itunes και τραβάει τα αντίστοιχα data
    public function searchForItunes($file)
    {
        $key = array_search($file, self::$tracks);  // Έλεγχος αν υπάρχει στην λίστα του itunes
        $status = '';

        if (($key) && (!$this->inserted_id == 0)) {   // Αν υπάρχει στην itunes library
            $track_id = $key;
            //            echo $counter . ' ' . $file . ' βρέθηκε στο ' . $key . ' | name: ' . $tags[$track_id]['Name'] . ' artist=' . $tags[$track_id]['Artist'] . '<br>';

            $this->getItunesValues($track_id);  // Παίρνει τις τιμές από την itunes library

            $this->itunes_counter++;

            $status = 'founded';

        }

        return $status;
    }

    public function writeTheMusicTags($displayEcho=null)
    {
        // Εγγραφή στο music_tags
        $sqlParamsTags = array($this->inserted_id, $this->name, $this->artist, $this->genre, $this->date_added, $this->play_count,
            $this->play_date, $this->rating, $this->album, $this->album_artwork_id, $this->video_width, $this->video_height,
            $this->size, $this->track_time, $this->year, $this->live

        );

        if ($this->stmt_tags->execute($sqlParamsTags)) {  // Αν η εγγραφή είναι επιτυχής
            if($displayEcho) {
                echo __('file_added').' ' . $this->name . '<br>';
            }
            $this->added_video++;
        } else {
            if($displayEcho) {
                echo __('file_not_added').' ' . $this->name . '<br>';
            }
            trigger_error($this->general_counter . ' PROBLEM!!!!!!!    ' . '       $inserted_id ' . $this->inserted_id . ' ' . '$this->name ' . $this->name . ' ' . '$this->artist ' . $this->artist . ' ' . '$this->genre ' . $this->genre . ' ' . '$this->date_added ' . $this->date_added . ' ' . '$this->play_count ' . $this->play_count . ' ' .
                '$this->play_date ' . $this->play_date . ' ' . '$this->rating ' . $this->rating . ' ' . '$this->album ' . $this->album . ' ' . '$this->album_artwork_id ' . $this->album_artwork_id . ' ' . '$this->video_width ' . $this->video_width . ' ' . '$this->video_height ' . $this->video_height . ' ' .
                '$this->size ' . $this->size . ' ' . '$this->track_time ' . $this->track_time . ' ' . '$this->year ' . $this->year . ' ' . '$this->live ' . $this->live);
        }
    }


    // Εμφάνιση αρχείων για διαγραφή
    public function displayFilesToDelete()
    {
        if(self::$filesForDelete) {  // Αν υπάρχουν αρχεία προς διαγραφή
            echo '<p>'.__('files_to_delete').': '.count(self::$filesForDelete).' </p>';

            foreach (self::$filesForDelete as $item) {  // Εμφανίζει τα αρχεία προς διαγράφη
                ?>
                <div id=deleteRow<?php echo $item['id']; ?> class="deleteRows"><?php echo $item['id']. ' '. $item['filename']; ?></div>

                <?php
            }

            // Παίρνουμε το array για πέρασμα στην javascript
            $deleteFilesArrayForJavascript = json_encode(self::$filesForDelete, JSON_UNESCAPED_UNICODE);

            ?>

            <br><input type="button" id="AgreeToDeleteFiles" name="AgreeToDeleteFiles" value="<?php echo __('delete_files'); ?>"
                       onclick="deleteFiles(<?php echo htmlentities($deleteFilesArrayForJavascript); ?>);">

            <?php
        }
    }

    // Εμφάνιση αρχείων για μετακίνηση
    public function displayFilesToMove()
    {
        if(self::$filesForUpdate) {  // Αν υπάρχουν αρχεία προς ενημέρωση
            echo '<p>'.__('files_to_update').': '.count(self::$filesForUpdate).' </p>';

            foreach (self::$filesForUpdate as $item) {  // Εμφανίζει τα αρχεία προς ενημέρωση
                ?>
                <div id=updateRow<?php echo $item['id']; ?> class="updateRows"><?php echo $item['id']. ' '. $item['filename']; ?></div>

                <?php
            }

            // Παίρνουμε το array για πέρασμα στην javascript
            $updateFilesArrayForJavascript = json_encode(self::$filesForUpdate, JSON_UNESCAPED_UNICODE);

            ?>

            <br><input type="button" id="AgreeToUpdateFiles" name="AgreeToUpdateFiles" value="<?php echo __('update_files'); ?>"
                       onclick="updateFiles(<?php echo htmlentities($updateFilesArrayForJavascript); ?>);">

            <?php
        }
    }

    // Εμφάνιση των τελικών αποτελεσμάτων του συγχρονισμού
    public function displaySyncResults()
    {
        echo '<p>' . __('files_added') . ' '. $this->added_video . ' ' . __('added_files'). '</p>';


        // Εμφάνιση αρχείων προς διαγραφή
        $this->displayFilesToDelete();

        // Εμφάνιση αρχείων για μετακίνηση
        $this->displayFilesToMove();

        $script_time_elapsed_secs = microtime(true) - $this->script_start;

        echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';

        Logs::insertLog('Added ' . $this->added_video . ' files.'); // Προσθήκη της κίνησης στα logs
    }

    // Σπάει το $file σε $filename και $path
    public function getFilenameAndPath()
    {
        $splitFilename = Utilities::splitFilePathName($this->file);
        $this->filename = $splitFilename['filename'];
        $this->path = $splitFilename['path'];
        $this->fullPathName = DIR_PREFIX . $this->path . $this->filename;
    }

    //  Παίρνει το κείμενο για εμφάνιση αν χρειάζεται να σβηστεί αρχείο που βρέθηκε να υπάρχει
    public function getFileToDelete()
    {
        if(self::$filesForDelete) {  // Αν υπάρχουν αρχεία προς διαγραφή
            $this->deleteFilesString = '<p id="jsFileAlreadyExist'.$this->inserted_id.'">'. __('file_to_delete') .
                        ' <input type="button" class="myButton" value="'. __('delete_file'). '"
                       onclick="deleteExistedFile('. $this->inserted_id . ');"></p>';
        }
    }

    // Γράφει ένα μόνο αρχείο στην βάση
    public function writeTrack()
    {
        // Αν το mediakind είναι μουσική ελέγχουμε και δημιουργούμε τους φακέλους που χρειαζόμαστε
        if($this->mediaKind=='Music') {
            $this->createDirectories();
        }

        // Κάνει τα prepare για τα inserts
        $this->prepareInserts();

        $this->getFilenameAndPath();

        // Έλεγχος αν υπάρχει ήδη το αρχείο στην βάση δεδομένων. True για να ψάξει στο array
        $fileAlreadySynced = $this->checkIfFileExistsOnDB(false);

        $problemInFilePath=false;

        $searchHash='';

        // Αν δεν έχει συγχρονιστεί ήδη το αρχείο κάνουμε ελέγχους αν έχει μεταφερθεί ή αν υπάρχει διπλή εγγραφή
        if(!$fileAlreadySynced) {
            // Έλεγχος στα νέα αρχεία αν λειτουργούν και αν το hash υπάρχει ήδη στην βάση
            if(!$this->checkIfFileExists()) { // Αλλιώς το δηλώνουμε προβληματικό
//                echo '<p>'.__('there_is_a_problem_with_file').' '.$this->fullPathName.'. '.__('special_char_in_path').'</p>';
                $problemInFilePath=true;
            }
        } else {
            $searchHash = false;
        }

        // Αν το αρχείο δεν έχει περαστεί ήδη και δεν υπάρχει το hash του και δεν έχει πρόβλημα το path
        if(!$fileAlreadySynced && !$searchHash && !$problemInFilePath) {

            if ($this->searchIDFiles == true) {  // Αν έχει επιλεγεί να ψάξουμε για tags στο αρχείο
                $this->getMediaFileTags($this->fullPathName); // διαβάζει το αρχείο και παίρνει τα αντίστοιχα file tags
            }

            // Εγγραφή στο files και επιστροφή του $inserted_id
            $this->writeTheFile();

            // Εγγραφή στο music_tags
            $this->writeTheMusicTags();

        }

        // Παίρνει το κείμενο για εμφάνιση αν χρειάζεται να σβηστεί αρχείο που βρέθηκε να υπάρχει
        $this->getFileToDelete();

    }

    // Γράφει τα αρχεία που βρίσκει στην βάση
    public function writeTracks()
    {
        Progress::updatePercentProgress(0);   // Μηδενίζει το progress

        $this->script_start = microtime(true);

        // Αν το mediakind είναι μουσική ελέγχουμε και δημιουργούμε τους φακέλους που χρειαζόμαστε
        if($this->mediaKind=='Music') {
            $this->createDirectories();
        }

        // Διάβασμα των αρχείων στα directory που δίνει ο χρήστης
        if(!$this->scanFiles($this->mediaKind)) {
            Progress::setLastMomentAlive(true);
            Progress::updatePercentProgress(0);   // Μηδενίζει το progress

            die('No Directories to scan');
        }

        if($this->searchItunes) {
            $this->getItunesLibrary();
        }

        $conn = new MyDB();
        $conn->CreateConnection();

        // Παίρνουμε τις εγγραφές στο table files σε array
        $this->getFilesOnDB();

        // Κάνει τα prepare για τα inserts
        $this->prepareInserts();

        $totalFiles=count(self::$files);

        $progressCounter=0;

        foreach (self::$files as $this->file) {  // Έλεγχος κάθε αρχείου που βρέθηκε στο path

            // Σπάει το $this->file σε $this->filename και $this->path
            $this->getFilenameAndPath();

            // Έλεγχος αν υπάρχει ήδη το αρχείο στην βάση δεδομένων. True για να ψάξει στο array
            $fileAlreadySynced = $this->checkIfFileExistsOnDB(true);

            $problemInFilePath=false;

            // Αν δεν έχει συγχρονιστεί ήδη το αρχείο κάνουμε ελέγχους αν έχει μεταφερθεί ή αν υπάρχει διπλή εγγραφή
            if(!$fileAlreadySynced) {

                // Έλεγχος στα νέα αρχεία αν λειτουργούν και αν το hash υπάρχει ήδη στην βάση
                if(!$this->checkIfFileExists()) { // Αλλιώς το δηλώνουμε προβληματικό
                    echo '<p>'.__('there_is_a_problem_with_file').' '.$this->fullPathName.'. '.__('special_char_in_path').'</p>';
                    $problemInFilePath=true;
                }

            } else {
                $searchHash = false;
            }

            // Αν το αρχείο δεν έχει περαστεί ήδη και δεν υπάρχει το hash του και δεν έχει πρόβλημα το path
            if(!$fileAlreadySynced && !$searchHash && !$problemInFilePath) {

                Progress::setLastMomentAlive(false);

                $this->startingValues($this->filename); // Αρχικοποίηση τιμών

                $dontDoRecord = false;

                if ($this->searchIDFiles == true) {  // Αν έχει επιλεγεί να ψάξουμε για tags στο αρχείο
                    $this->getMediaFileTags($this->fullPathName); // διαβάζει το αρχείο και παίρνει τα αντίστοιχα file tags

                    // Ελέγχει και μετατρέπει το alac
                    $dontDoRecord = $this->checkAndConvertALAC();

                }


                if (!$dontDoRecord) {   // Αν είναι ALAC αρχείο και θέλουμε να μετατραπεί και δεν υπάρχει σφάλμα στην μετατροπή

                    // Εγγραφή στο files και επιστροφή του $inserted_id
                    $this->writeTheFile();

                    if ($this->searchItunes) {  // Αν έχει επιλεγεί να κάνουμε συγχρονισμό με itunes
                        $status = $this->searchForItunes($this->file);
                    } else {
                        $status = 'not founded';
                    }

                    // Εγγραφή στο music_tags
                    $this->writeTheMusicTags(true);

                }

            }


            if($progressCounter>100) { // ανα 100 items ενημερώνει το progress
                $progressPercent = intval(($this->general_counter / $totalFiles) * 100);

                Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

                Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                $progressCounter=0;
            }
            else $progressCounter++;

            $this->general_counter++;


        }

        // μετά την ολοκλήρωση τους σκανιαρίσματος των αρχείων
        Progress::setLastMomentAlive(true);
        Progress::updatePercentProgress(0);   // Μηδενίζει το progress

        // Εμφάνιση των αποτελεσμάτων του συγχρονισμού
        $this->displaySyncResults();

    }

    

    // Επιστρέφει true αν το string είναι UTF-8
    public function detectBadEncoding($string)
    {
//        trigger_error(strpos($string,'&#'));

        if(strpos($string,'&amp;#') || strpos($string,';&#')) return true;
    }


    // Επιστρέφει τα ID tags ενός media αρχείου
    public function getMediaFileTags ($FullFileName) {

        Progress::setLastMomentAlive(true);

        if(!self::$getID3) {
            self::$getID3=new \getID3();
        }


        $ThisFileInfo = self::$getID3->analyze($FullFileName);

        \getid3_lib::CopyTagsToComments($ThisFileInfo);

//                           echo'<pre>';
//       print_r($ThisFileInfo);
//        echo'</pre>';
        

        if(isset($ThisFileInfo['filename'])) {
            $replace_text = array('.mp4', '.m4v', '.mp3', '.m4a');


            if (isset($ThisFileInfo['audio']['codec'])){
                $this->codec = ClearString($ThisFileInfo['audio']['codec']);
            }

            if (isset($ThisFileInfo['comments_html']['title'][0]))
                if(!$this->detectBadEncoding($ThisFileInfo['comments_html']['title'][0]))
                    $title = ClearString($ThisFileInfo['comments_html']['title'][0]);
                else $title = str_replace($replace_text, '', $ThisFileInfo['filename']);
            else $title = str_replace($replace_text, '', $ThisFileInfo['filename']);

            if (isset($ThisFileInfo['comments_html']['artist'][0]))
                if(!$this->detectBadEncoding($ThisFileInfo['comments_html']['artist'][0]))
                    $artist = ClearString($ThisFileInfo['comments_html']['artist'][0]);
                else $artist = '';
            else $artist = '';

            if (isset($ThisFileInfo['comments']['picture'][0]['data'])) {
//                $albumCover = 'data:' . $ThisFileInfo['comments']['picture'][0]['image_mime'] . ';charset=utf-8;base64,' . base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
                $albumCoverID=OWMPElements::uploadAlbumImage($ThisFileInfo['comments']['picture'][0]['data'],$ThisFileInfo['comments']['picture'][0]['image_mime']);
//                echo '<img src='.$albumCover.' />';
            }
            else $albumCoverID = 1;
            

            if (isset($ThisFileInfo['filesize']))
                $size = intval($ThisFileInfo['filesize']);
            else $size = 0;

            if (isset($ThisFileInfo['comments_html']['album'][0]))
                if(!$this->detectBadEncoding($ThisFileInfo['comments_html']['album'][0]))
                    $album = ClearString($ThisFileInfo['comments_html']['album'][0]);
                else $album = '';
            else $album = '';

            if (isset($ThisFileInfo['comments_html']['year'][0]))
                $songYear = intval($ThisFileInfo['comments_html']['year'][0]);
            else $songYear = 0;

            if (isset($ThisFileInfo['video']['resolution_x']))
                $video_width = intval($ThisFileInfo['video']['resolution_x']);
            else $video_width = 0;

            if (isset($ThisFileInfo['video']['resolution_y']))
                $video_height = intval($ThisFileInfo['video']['resolution_y']);
            else $video_height = 0;

            if (isset($ThisFileInfo['comments_html']['genre'][0])) {
                $genre = ClearString($ThisFileInfo['comments_html']['genre'][0]);
                $genre = substr($genre, 0, 19);
            } else $genre = '';


            if (isset($ThisFileInfo['playtime_seconds']))
                $track_time = floatval($ThisFileInfo['playtime_seconds']);
            else $track_time = 0;

            $this->name = substr($title,0,255);
            $this->artist = substr($artist,0,255);
            $this->genre = substr($genre,0,19);
            $this->album = substr($album,0,255);
            $this->date_added = date('Y-m-d H:i:s');
            $this->track_time = $track_time;
            $this->video_width = $video_width;
            $this->video_height = $video_height;
            $this->size = $size;
            $this->album_artwork_id = $albumCoverID;
            $this->year = $songYear;


        } else return false;
    }



    // Κεντρική function που κάνει τον συγχρονισμό
    public function syncTheFiles($mediakind) {
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        Progress::setLastMomentAlive(false);

        $this->mediaKind = $mediakind;
        $this->searchItunes = SYNC_ITUNES;
        $this->searchIDFiles = true;
        $this->writeTracks();
    }


    // Ψάχνει για αρχεία που δεν παίζουν και διαγράφει τις αντίστοιχες εγγραφές
    public function clearTheFiles() {
        set_time_limit(0);

        $script_start = microtime(true);

        $conn = new MyDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null, null, null)) // Ολόκληρη η λίστα
        {
            $progressCounter=0;
            $general_counter=0;

            $totalFiles = count($filesOnDB);

            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(!FilesIO::fileExists($full_path)) {
                    OWMPElements::deleteFile($file['id']);
                    echo $full_path.'<br>';
                    $counter++;
                }


                if($progressCounter>100) { // ανα 100 items ενημερώνει το progress
                    $progressPercent = intval(($general_counter / $totalFiles) * 100);

                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
                    
                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                    $progressCounter=0;
                }
                else $progressCounter++;

                $general_counter++;

            }

            Progress::setProgress(0);
            
            echo '<p>'.__('files_founded'). ' ' . $counter. ' '.  __('founded_and_deleted'). '</p>';

            Logs::insertLog('Were found '.$counter. ' problematic files and were erased'); // Προσθήκη της κίνησης στα logs

            $script_time_elapsed_secs = microtime(true) - $script_start;

            echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';
        }


    }

    // Επιστρέφει το hash για το αρχείο $full_path
    static function hashFile($full_path) {

        // Παίρνουμε ένα κομμάτι (string) από το αρχείο και το διαβάζουμε
        if(FilesIO::fileExists($full_path)) {
            Progress::setLastMomentAlive(false);

            $start=filesize($full_path)/2;
            $size=1024;

            $handle   = fopen($full_path, "rb");
            fseek($handle, $start);
            $contents = fread($handle, $size);
            fclose($handle);

            // Παράγουμε το md5 από το συγκεκριμένο string του αρχείου
            $result = md5($contents);

//            trigger_error(filesize($full_path).'   '.$result.'   '.$full_path);

            Progress::setLastMomentAlive(true);
        }
        else $result=false;


        return $result;
    }

    // Επιστρέφει το hash από κομμάτι που είναι στην μέση του $theString
    static function hashString($theString) {

        // Παίρνουμε ένα κομμάτι (string) από το $theString και το διαβάζουμε
        $start=strlen($theString)/2;
        $size=1024;

        $contents=substr($theString, $start, $size);

        // Παράγουμε το md5 από το συγκεκριμένο string του $theString
        $result = md5($contents);

        return $result;
    }

    // Παράγει hash για κάθε αρχείο και ενημερώνει την βάση
    static function hashTheFiles($mediaKind) {
        set_time_limit(0);

        Progress::setProgress(0);

        $script_start = microtime(true);

        $conn = new MyDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', 'kind=?', array($mediaKind), null, null, null)) // Ολόκληρη η λίστα
        {
            $progressCounter=0;
            $general_counter=0;

            $totalFiles = count($filesOnDB);


            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(FilesIO::fileExists($full_path)) {
                            $start = microtime(true);
                    
                            $hash = self::hashFile($full_path);  // Παίρνουμε το hash από το συγκεκριμένο αρχείο

                            $time_elapsed_secs = microtime(true) - $start;

                            // Ενημερώνουμε την βάση με το επιστρεφόμενο hash
                            $update = MyDB::updateTableFields('files', 'id=?',
                                                                    array('hash'),
                                                                    array($hash, $file['id']));

                            if ($update) {
                                echo 'fullpath: ' . $full_path . '  hash: ' . $hash . ' time: ' . $time_elapsed_secs . '<br>';
                                $counter++;
                            }
                            else echo __('problem_with_file').' '.$full_path.'<br>';
                }

                if($progressCounter>10) { // ανα 100 items ενημερώνει το progress
                    $progressPercent = intval(($general_counter / $totalFiles) * 100);

                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                    $progressCounter=0;
                }
                else $progressCounter++;

                $general_counter++;


            }

            $script_time_elapsed_secs = microtime(true) - $script_start;

            Progress::setProgress(0);

            echo '<p>'.$counter. ' '.__('files_to_hash').'</p>';
            echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';

            Logs::insertLog($counter. ' files produced hash'); // Προσθήκη της κίνησης στα logs
        }

    }

    // Ψάχνει αν το συγκεκριμένο $hash υπάρχει ήδη στα τραγούδια
    static function searchForHash($hash) {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql = 'SELECT id FROM files WHERE hash=?';
        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($hash));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['id'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ψάχνει αν το συγκεκριμένο $hash υπάρχει ήδη στις εικόνες
    static function searchForImageHash($hash) {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql = 'SELECT id FROM album_arts WHERE hash=?';
        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($hash));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['id'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Ενημερώνει μαζικά την βάση με τα metadata των αρχείων. filesize, track time, video width, video height
    public function filesMetadata() {
        set_time_limit(0);

        Progress::setProgress(0);

        $script_start = microtime(true);

        $conn = new MyDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null, null, null)) // Ολόκληρη η λίστα
        {

            $progressCounter=0;
            $general_counter=0;

            $totalFiles = count($filesOnDB);

            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(FilesIO::fileExists($full_path)) {

                    self::getMediaFileTags($full_path);  // Παίρνουμε τα metadata του αρχείου

                    // Ενημερώνουμε την βάση με τα αντίστοιχα metadata
                    $update = MyDB::updateTableFields('music_tags', 'id=?',
                        array('track_time', 'video_width', 'video_height', 'filesize'),
                        array($this->track_time, $this->video_width, $this->video_height, $this->size, $file['id']));

                    if ($update) {
                        $counter++;
                    }
                    else echo __('problem_with_file').' '.$full_path.'<br>';
                }


                if($progressCounter>100) { // ανα 100 items ενημερώνει το progress
                    $progressPercent = intval(($general_counter / $totalFiles) * 100);

                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                    $progressCounter=0;
                }
                else $progressCounter++;

                $general_counter++;


            }

            $script_time_elapsed_secs = microtime(true) - $script_start;

            Progress::setProgress(0);

            echo '<p>'.$counter. ' '.__('files_to_metadata').'</p>';
            echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';

            Logs::insertLog($counter. ' files produced metadata'); // Προσθήκη της κίνησης στα logs
        }
    }


    // TODO να κάνει ταυτόχρονα και έλεγχο για τα ορφανά αρχεία
    // Δημιουργεί μαζικά μικρότερες εκδόσεις των cover albums
    public function convertCovers() {
        set_time_limit(0);
        ini_set('memory_limit', '100M'); // Για χειρισμό μεγάλων εικόνων

        Progress::setProgress(0);

        $script_start = microtime(true);

        $conn = new MyDB();

        $counter=0;

        if($artsArray = $conn->getTableArray('album_arts', '*', null, null, null, null, null)) // Ολόκληρη η λίστα
        {

            $progressCounter=0;
            $general_counter=0;

            $totalFiles = count($artsArray);


            foreach ($artsArray as $item) {
                $myImage = ALBUM_COVERS_DIR . $item['path'] . $item['filename'];
                
                if(FilesIO::fileExists($myImage)) {
                    $extension = pathinfo($myImage, PATHINFO_EXTENSION);

                    $thumbnailImage = ALBUM_COVERS_DIR . $item['path'] . 'thumb_' . $item['filename'];
                    $smallImage = ALBUM_COVERS_DIR . $item['path'] . 'small_' . $item['filename'];
                    $icoImage = ALBUM_COVERS_DIR . $item['path'] . str_replace('.' . $extension, '.ico', $item['filename']);


                    if (file_exists($thumbnailImage)) {
                        $thumbExist = true;
                    } else {
                        $thumbExist = false;
                    }

                    if (file_exists($smallImage)) {
                        $smallExist = true;
                    } else {
                        $smallExist = false;
                    }

                    if (file_exists($icoImage)) {
                        $icoExist = true;
                    } else {
                        $icoExist = false;
                    }

                    // Αν δεν υπάρχουν ήδη τα small images
                    if (!$thumbExist || !$smallExist || !$icoExist) {
//                        trigger_error($myImage);
                        // Ελέγχει πρώτα αν είναι valid το Image
                        if (OWMPElements::checkValidImage($myImage)) {
                            if (!$thumbExist) {
                                if (OWMPElements::createSmallerImage($myImage, 'thumb')) {
                                    echo $thumbnailImage . ' CREATED<br>';
                                } else {
                                    echo $myImage . ' CORRUPTED<br>';
                                }
                            }

                            if (!$smallExist) {
                                if (OWMPElements::createSmallerImage($myImage, 'small')) {
                                    echo $smallImage . ' CREATED<br>';
                                } else {
                                    echo $myImage . ' CORRUPTED<br>';
                                }
                            }

                            if (!$icoExist) {
                                if (OWMPElements::createSmallerImage($myImage, 'ico')) {
                                    echo $icoImage . ' CREATED<br>';
                                } else {
                                    echo $myImage . ' CORRUPTED<br>';
                                }
                            } 

                        } else {
                            echo $myImage . ' CORRUPTED IMAGE<br>';
                        }
                    }
                }


                if($progressCounter>100) { // ανα 100 items ενημερώνει το progress
                    $progressPercent = intval(($general_counter / $totalFiles) * 100);

                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                    $progressCounter=0;
                }
                else $progressCounter++;

                $general_counter++;



            }

            $script_time_elapsed_secs = microtime(true) - $script_start;

            Progress::setProgress(0);

            echo '<p>'.$counter. ' '.__('files_to_metadata').'</p>';
            echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';

            Logs::insertLog($counter. ' files produced metadata'); // Προσθήκη της κίνησης στα logs
        }
    }


    // Μετατρέπει ένα ALAC αρχείο σε mp3. Το δημιουργεί σε νέα τοποθεσία την οποία επιστρέφει
    public function convertALACtoMP3($fullPath, $filename, $path) {

        Progress::setLastMomentAlive(true);

        // TODO να κάνω και μία function που να μετατρέπει όλα τα .converted πίσω στο αρχικό τους

        // Μετατροπή ALAC σε απλό mp3. Το δημιουργεί καταρχήν σε temp dir (INTERNAL_CONVERT_PATH)
        OWMPElements::execConvertALAC($fullPath, INTERNAL_CONVERT_PATH . $filename, '320');

//        print shell_exec('ffmpeg -i "'.$fullPath.'" -ac 2 -f wav - | lame -b 320 - "'.INTERNAL_CONVERT_PATH.$filename.'" ');

        if (FilesIO::fileExists(INTERNAL_CONVERT_PATH . $filename)) { // Αν η μετατροπή έχει γίνει
            // μετονομάζει το αρχικό αρχείο σε .converted για να μην ξανασκανιαριστεί
            if (rename(DIR_PREFIX . $path . $filename, DIR_PREFIX . $path . $filename . '.converted')) { // Αν μετονομαστεί με επιτυχία
                // Το αντιγράφει στην τοποθεσία DIR_PREFIX.MUSIC_UPLOAD όπου βάζει όλα τα converted και πρέπει να έχει δικαιώματα
                print shell_exec('cp "' . INTERNAL_CONVERT_PATH . $filename . '" "' . MUSIC_UPLOAD . $filename . '"');
                unlink(INTERNAL_CONVERT_PATH . $filename); // Το σβήνει από την προσωρινή τοποθεσία INTERNAL_CONVERT_PATH

                if (FilesIO::fileExists(MUSIC_UPLOAD . $filename)) // Αν έχει γίνει σωστά η αντιγραφή
                    $result = array('path' => MUSIC_UPLOAD); // Επιστρέφει το νέο path
                else $result = false;
            } else $result = false;

        } else $result = false;

        return $result;
    }


    // Κάνει export ένα αρχείο json με τα data της $tempUserPlaylist 
    static function exportPlaylistJsonFile($tempUserPlaylist) {

        $joinFieldsArray= array('firstField'=>'id', 'secondField'=>'file_id');
        $mainTables= array('music_tags', 'files');

        $exportTable = MyDB::getTableArray($mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
            null, null, null, $tempUserPlaylist, $joinFieldsArray);

        $jsonTable=json_encode($exportTable, JSON_UNESCAPED_UNICODE);

        $libraryFile=OUTPUT_FOLDER.JSON_FILENAME;
        file_put_contents($libraryFile, $jsonTable);
    }



    // Παίρνει το αρχείο JSON_PLAYLIST_FILE και το μετατρέπει σε table self::$jsonTable
    public function getJsonFileToTable() {

        if(!FilesIO::fileExists(JSON_PLAYLIST_FILE))
            exit(__('there_is_no_file').' '.JSON_PLAYLIST_FILE);

        $handle   = fopen(JSON_PLAYLIST_FILE, "rb");
        $contents = fread($handle, filesize(JSON_PLAYLIST_FILE));
        fclose($handle);

        self::$jsonTable = json_decode($contents, true);

    }


    // Εισάγει μια playlist από json αρχείο στην database
    public function importPlaylistToDB() {
        set_time_limit(0);

        $script_start = microtime(true);

        $this->getJsonFileToTable();


        if(self::$jsonTable) // Ολόκληρη η λίστα
        {

            $conn = new MyDB();

            $conn->CreateConnection();

            $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

            $sql_insert_tags = 'INSERT INTO music_tags (id, song_name, artist, genre, date_added, play_count, 
                          date_last_played, rating, album, album_artwork_id, video_width, video_height, filesize, track_time, song_year, live) 
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

            $stmt_file = MyDB::$conn->prepare($sql_insert_file);
            $stmt_tags = MyDB::$conn->prepare($sql_insert_tags);

            $added_video=0;
            $progressCounter=0;
            $general_counter=0;

            $totalFiles = count(self::$jsonTable);

            foreach (self::$jsonTable as $file) {


                // Εγγραφή στο files
                $sqlParamsFile = array($file['path'], $file['filename'], $file['hash'], $file['kind']);

                if ($stmt_file->execute($sqlParamsFile)) {  // Αν η εγγραφή είναι επιτυχής
                    $inserted_id = MyDB::$conn->lastInsertId();  // παίρνουμε το id για χρήση αργότερα
                } else {
                    $inserted_id = 0;
                }

                $this->name = $file['song_name'];
                $this->artist = $file['artist'];
                $this->genre = $file['genre'];
                $this->date_added = $file['date_added'];
                $this->track_time = $file['track_time'];
                $this->video_width = $file['video_width'];
                $this->video_height = $file['video_height'];
                $this->size = $file['filesize'];

//                $this->play_date = $file['date_last_played'];
                $this->play_date = '';
                $this->album = $file['album'];
//                $this->play_count = $file['play_count'];
                $this->play_count = 0;
//                $this->rating = $file['rating'];
                $this->rating = 0;
                $this->album_artwork_id = 1;
                $this->year = $file['song_year'];
                $this->live = $file['live'];


                // Εγγραφή στο music_tags
                $sqlParamsTags = array($inserted_id, $this->name, $this->artist, $this->genre, $this->date_added, $this->play_count,
                    $this->play_date, $this->rating, $this->album, $this->album_artwork_id, $this->video_width, $this->video_height,
                    $this->size, $this->track_time, $this->year, $this->live

                );



                if ($stmt_tags->execute($sqlParamsTags)) {  // Αν η εγγραφή είναι επιτυχής
                    echo __('file_added').' ' . $this->name . '<br>';
                    $added_video++;
                } else {
                    echo __('file_not_added').' ' . $this->name . '<br>';
                    trigger_error($general_counter . ' PROBLEM!!!!!!!      $inserted_id ' . $inserted_id . ' ' . '$this->name ' . $this->name . ' ' . '$this->artist ' . $this->artist . ' ' . '$this->genre ' . $this->genre . ' ' . '$this->date_added ' . $this->date_added . ' ' . '$this->play_count ' . $this->play_count . ' ' .
                        '$this->play_date ' . $this->play_date . ' ' . '$this->rating ' . $this->rating . ' ' . '$this->album ' . $this->album . ' ' . '$this->album_artwork_id ' . $this->album_artwork_id . ' ' . '$this->video_width ' . $this->video_width . ' ' . '$this->video_height ' . $this->video_height . ' ' .
                        '$this->size ' . $this->size . ' ' . '$this->track_time ' . $this->track_time . ' ' . '$this->year ' . $this->year . ' ' . '$this->live ' . $this->live);
                }


                if($progressCounter>1) {
                    $progressPercent = intval(($general_counter / $totalFiles) * 100);

                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

                    $progressCounter=0;
                }
                else {
                    $progressCounter++;
                }

                $general_counter++;

            }

            Progress::setProgress(0);

            echo '<p>'.__('files_added').' '.$added_video. ' '.__('new_records_to_database').'</p>';

            Logs::insertLog(__('files_added').' '.$added_video. ' '.__('new_records_to_database')); // Προσθήκη της κίνησης στα logs

            $script_time_elapsed_secs = microtime(true) - $script_start;

            echo '<p>'.__('total_time').': '.Utilities::seconds2MinutesAndSeconds($script_time_elapsed_secs).'</p>';
        }
    }
    
    
}