<?php

/**
 * File: SyncFiles.php
 * Created by rocean
 * Date: 28/06/16
 * Time: 19:01
 * Κλαση συγχρονισμού αρχείων
 */

require_once ('scanDir.php');

// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once ('PlistParser.inc');

// @source https://github.com/JamesHeinrich/getID3/
require_once('getid3/getid3.php');

class SyncFiles
{


    static $files = array();
    static $tracks = array();
    static $tags = array();
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

    static $filesForDelete = array();
    static $filesForUpdate = array();

        // Διάβασμα της library στο itunes
        public function getItunesLibrary()
        {

            $parser = new plistParser();
            $plist = $parser->parseFile($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. "Library.xml");

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
//            echo count(self::$tracks);


        }
    

    // Διάβασμα των αρχείων στα directory που δίνει ο χρήστης
    public function scanFiles()
    {
        $conn= new RoceanDB();

        $dirs = $conn->getTableArray('paths', 'file_path', null, null, null); // Παίρνει τα paths

        $dirs=$conn->clearArray($dirs);

        $extensions = array('mp4', 'm4v');

        self::$files = scanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs

        self::$files = array_unique(self::$files);
        $trimFiles = array();

        foreach (self::$files as $file) {
            if (strpos($file, '._') == false)
                $trimFiles[] = urldecode(str_replace(DIR_PREFIX, '', $file));
        }

        self::$files = $trimFiles;


    }


    // Αρχικοποίηση τιμών
    public function startingValues($filename) {
        // Αρχικοποίηση τιμών
        $replace_text = array('.mp4', '.m4v');

        $this->name = str_replace($replace_text, '', $filename);
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
        $this->album_artwork_id = 0;
        $this->year = 0;
        $this->live = 0;
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

    // Γράφει τα αρχεία που βρίσκει στην βάση
    public function writeTracks($searchItunes,$searchIDFiles)
    {
        $script_start = microtime(true);

        $this->scanFiles();

        if($searchItunes)
            $this->getItunesLibrary();

        $conn = new RoceanDB();

        // Παίρνουμε τις εγγραφές στο table files σε array
        if(!$filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null)) // Ολόκληρη η λίστα
            $filesOnDB='';
        else {
            foreach ($filesOnDB as $file) {
                $newFilesOnDB[$file['id']] = $file['path'] . $file['filename'];
            }
            $filesOnDB = $newFilesOnDB;
        }

        $conn->CreateConnection();

        $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

        $sql_insert_tags = 'INSERT INTO music_tags (id, song_name, artist, genre, date_added, play_count, 
                          date_last_played, rating, album, album_artwork_id, video_width, video_height, filesize, track_time, song_year, live) 
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $stmt_file = RoceanDB::$conn->prepare($sql_insert_file);
        $stmt_tags = RoceanDB::$conn->prepare($sql_insert_tags);


        $counter = 0;
        $general_counter = 0;
        $added_video = 0;

        $mediaKind='Music Video';


        foreach (self::$files as $file) {  // Έλεγχος κάθε αρχείου που βρέθηκε στο path


            $string_array = explode('/', $file);
            $filename = $string_array[count($string_array) - 1];
            $path = str_replace($filename, '', $file);

            if(is_array($filesOnDB)){  // Έλεγχος αν το αρχείο υπάρχει στην βάση
                if($fileKey=array_search($file, $filesOnDB)) {
                    $fileAlreadySynced=true;
                } else $fileAlreadySynced=false;
            } else $fileAlreadySynced=false;

            $full_path = DIR_PREFIX . $path . $filename;



            if(!$fileAlreadySynced) { // Έλεγχος στα νέα αρχεία αν το hash υπάρχει ήδη στην βάση

                $hash = self::hashFile($full_path);  // Παίρνουμε το hash από το συγκεκριμένο αρχείο

                if($searchHash=self::searchForHash($hash)) { // Έλεγχος στην βάση για to hash

                    $oldFullPath=DIR_PREFIX.OWMP::getFullPathFromFileID($searchHash);  // To fullpath του αρχείου που βρέθηκε

                    if(!OWMP::fileExists($oldFullPath)) {  // Αν το παλιό αρχείο στο fullpath δεν βρεθεί

                        self::$filesForUpdate[]= [  // Πίνακας με τα id των προς διαγραφή αρχείων
                            'id' => $searchHash,
                            'filename' => $filename,
                            'path' => $path
                        ];

                        trigger_error('UPDATE');

                    }
                    else {  // Αν το παλιό αρχείο στο fullpath βρεθεί, τότε σβήνει το καινούργιο

                        self::$filesForDelete[]= [  // Πίνακας με τα filepath των προς διαγραφή αρχείων
                                'id' => $searchHash,
                                'filename' => $filename,
                                'fullpath' => $full_path
                            ];

                        trigger_error('DIAGRAFH');

                    }
                }

            } else $searchHash=false;

            if(!$fileAlreadySynced && !$searchHash ) {  // Αν το αρχείο δεν έχει περαστεί ήδη και δεν υπάρχει το hash του


                $this->startingValues($filename); // Αρχικοποίηση τιμών

                if($searchIDFiles==true)  // Αν έχει επιλεγεί να ψάξουμε για tags στο αρχείο
                    $this->getMediaFileTags($full_path); // διαβάζει το αρχείο και παίρνει τα αντίστοιχα file tags

                // Εγγραφή στο files
                $sqlParamsFile = array($path, $filename, $hash, $mediaKind);

                if ($stmt_file->execute($sqlParamsFile)) {  // Αν η εγγραφή είναι επιτυχής
                    $inserted_id = RoceanDB::$conn->lastInsertId();  // παίρνουμε το id για χρήση αργότερα
                }
                else {
                    $inserted_id = 0;
                    trigger_error('PROBLEM!!!!!!!!!!     $path ' . $path . ' $filename ' . $filename);
                }



                $status = 'not founded';

                if ($searchItunes) {  // Αν έχει επιλεγεί να κάνουμε συγχρονισμό με itunes
                    $key = array_search($file, self::$tracks);  // Έλεγχος αν υπάρχει στην λίστα του itunes


                    if (($key) && (!$inserted_id == 0)) {   // Αν υπάρχει στην itunes library
                        $track_id = $key;
                        //            echo $counter . ' ' . $file . ' βρέθηκε στο ' . $key . ' | name: ' . $tags[$track_id]['Name'] . ' artist=' . $tags[$track_id]['Artist'] . '<br>';

                        $this->getItunesValues($track_id);  // Παίρνει τις τιμές από την itunes library

                        $counter++;

                        $status = 'founded';

                    }
//                        else echo 'not found ' . $file;



                }


                // Εγγραφή στο music_tags
                $sqlParamsTags = array($inserted_id, $this->name, $this->artist, $this->genre, $this->date_added, $this->play_count,
                    $this->play_date, $this->rating, $this->album, $this->album_artwork_id, $this->video_width, $this->video_height,
                    $this->size, $this->track_time, $this->year, $this->live

                );


                if ($stmt_tags->execute($sqlParamsTags)){  // Αν η εγγραφή είναι επιτυχής
                    echo 'added... '.$general_counter.' '.$this->name.'<br>';
                    $added_video++;
                }
                else {
                    echo 'not added... '.$general_counter.' '.$this->name.'<br>';
//                    trigger_error($general_counter . ' PROBLEM!!!!!!!    ' . $status . '       $inserted_id ' . $inserted_id . ' ' . '$this->name ' . $this->name . ' ' . '$this->artist ' . $this->artist . ' ' . '$this->genre ' . $this->genre . ' ' . '$this->date_added ' . $this->date_added . ' ' . '$this->play_count ' . $this->play_count . ' ' .
//                        '$this->play_date ' . $this->play_date . ' ' . '$this->rating ' . $this->rating . ' ' . '$this->album ' . $this->album . ' ' . '$this->album_artwork_id ' . $this->album_artwork_id . ' ' . '$this->video_width ' . $this->video_width . ' ' . '$this->video_height ' . $this->video_height . ' ' .
//                        '$this->size ' . $this->size . ' ' . '$this->track_time ' . $this->track_time . ' ' . '$this->year ' . $this->year . ' ' . '$this->live ' . $this->live);
                }


            }

            $general_counter++;




//                if($general_counter>16000) die();

        }

        echo '<p>Προστέθηκαν ' . $added_video . " βίντεο. </p>";


        // Διαγραφή αρχείων αν χρειάζονται
        if(self::$filesForDelete) {  // Αν υπάρχουν αρχεία προς διαγραφή
            echo '<p>Αρχεία προς διαγραφή: </p>';

            foreach (self::$filesForDelete as $item) {  // Εμφανίζει τα αρχεία προς διαγράφη
                ?>
                    <div id=deleteRow<?php echo $item['id']; ?> class="deleteRows"><?php echo $item['filename']; ?></div>

                <?php
            }

            // Παίρνουμε το array για πέρασμα στην javascript
            $deleteFilesArrayForJavascript = json_encode(self::$filesForDelete, JSON_UNESCAPED_UNICODE);

            ?>

            <br><input type="button" id="AgreeToDeleteFiles" name="AgreeToDeleteFiles" value="Διαγραφή αρχείων"
                   onclick="deleteFiles(<?php echo htmlentities($deleteFilesArrayForJavascript); ?>);">

            <?php
        }

        // Ενημέρωση της βάσης με τα νέα path και filename των αρχείων που έχουν αλλάξει θέση
        if(self::$filesForUpdate) {  // Αν υπάρχουν αρχεία προς ενημέρωση
            echo '<p>Αρχεία προς Ενημέρωση που αλλάξανε θέση: </p>';

            foreach (self::$filesForUpdate as $item) {  // Εμφανίζει τα αρχεία προς ενημέρωση
                ?>
                    <div id=updateRow<?php echo $item['id']; ?> class="updateRows"><?php echo $item['filename']; ?></div>

                <?php
            }

            // Παίρνουμε το array για πέρασμα στην javascript
            $updateFilesArrayForJavascript = json_encode(self::$filesForUpdate, JSON_UNESCAPED_UNICODE);

            ?>

            <br><input type="button" id="AgreeToUpdateFiles" name="AgreeToUpdateFiles" value="Ενημέρωση αρχείων"
                   onclick="updateFiles(<?php echo htmlentities($updateFilesArrayForJavascript); ?>);">

            <?php
        }

        $script_time_elapsed_secs = microtime(true) - $script_start;

        echo '<p>Συνολικός χρόνος: '.$script_time_elapsed_secs;

        RoceanDB::insertLog('Προστέθηκαν ' . $added_video . ' βίντεο.'); // Προσθήκη της κίνησης στα logs
    }

    

    // Επιστρέφει true αν το string είναι UTF-8
    public function detectUTF8($string)
    {
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs',
            $string);
    }


    // Επιστρέφει τα ID tags ενός media αρχείου
    public function getMediaFileTags ($FullFileName) {

        if(!self::$getID3)
            self::$getID3=new getID3();

        $ThisFileInfo = self::$getID3->analyze($FullFileName);

        getid3_lib::CopyTagsToComments($ThisFileInfo);

        if(isset($ThisFileInfo['filename'])) {
            $replace_text = array('.mp4', '.m4v');

            if (isset($ThisFileInfo['comments_html']['title'][0]))
                if ($this->detectUTF8($ThisFileInfo['comments_html']['title'][0])) {
                    $title = ClearString($ThisFileInfo['comments_html']['title'][0]);
                } else $title = str_replace($replace_text, '', $ThisFileInfo['filename']);
            else $title = str_replace($replace_text, '', $ThisFileInfo['filename']);

            if (isset($ThisFileInfo['comments_html']['artist'][0]))
                if ($this->detectUTF8($ThisFileInfo['comments_html']['artist'][0]))
                    $artist = ClearString($ThisFileInfo['comments_html']['artist'][0]);
                else $artist = '';
            else $artist = '';

            if (isset($ThisFileInfo['filesize']))
                $size = intval($ThisFileInfo['filesize']);
            else $size = 0;

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

            $this->name = $title;
            $this->artist = $artist;
            $this->genre = $genre;
            $this->date_added = date('Y-m-d H:i:s');
            $this->track_time = $track_time;
            $this->video_width = $video_width;
            $this->video_height = $video_height;
            $this->size = $size;


        } else return false;
    }



    // Κεντρική function που κάνει τον συγχρονισμό
    public function syncTheFiles() {
        set_time_limit(0);
        ini_set('memory_limit','1024M');



        $this->writeTracks(false,true);

//        echo'<pre>';
//        print_r(self::$tags);
//        echo'</pre>';
    }


    // Ψάχνει για αρχεία που δεν παίζουν και διαγράφει τις αντίστοιχες εγγραφές
    public function clearTheFiles() {
        set_time_limit(0);

        $conn = new RoceanDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null)) // Ολόκληρη η λίστα
        {
            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(!OWMP::fileExists($full_path)) {
                    OWMP::deleteFile($file['id']);
                    echo $full_path.'<br>';
                    $counter++;
                }


            }

            echo '<p>Βρέθηκαν '.$counter. ' προβληματικά αρχεία και διαγράφτηκαν</p>';

            RoceanDB::insertLog('Βρέθηκαν '.$counter. ' προβληματικά αρχεία και διαγράφτηκαν'); // Προσθήκη της κίνησης στα logs
        }


    }

    // Επιστρέφει το hash για το αρχείο $full_path
    static function hashFile($full_path) {

        // Παίρνουμε ένα κομμάτι (string) από το αρχείο και το διαβάζουμε
        $start=filesize($full_path)/2;
        $size=1024;

        $handle   = fopen($full_path, "rb");
        fseek($handle, $start);
        $contents = fread($handle, $size);
        fclose($handle);

        // Παράγουμε το md5 από το συγκεκριμένο string του αρχείου
        $result = md5($contents);

        return $result;
    }

    // Παράγει hash για κάθε αρχείο και ενημερώνει την βάση
    static function hashTheFiles() {
        set_time_limit(0);

        $script_start = microtime(true);

        $conn = new RoceanDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null)) // Ολόκληρη η λίστα
        {
            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(OWMP::fileExists($full_path)) {
                            $start = microtime(true);
                    
                            $hash = self::hashFile($full_path);  // Παίρνουμε το hash από το συγκεκριμένο αρχείο

                            $time_elapsed_secs = microtime(true) - $start;

                            // Ενημερώνουμε την βάση με το επιστρεφόμενο hash
                            $update = RoceanDB::updateTableFields('files', 'id=?',
                                                                    array('hash'),
                                                                    array($hash, $file['id']));

                            if ($update) {
                                echo 'fullpath: ' . $full_path . '  hash: ' . $hash . ' time: ' . $time_elapsed_secs . '<br>';
                                $counter++;
                            }
                            else echo 'Πρόβλημα με το αρχειο '.$full_path;
                }


            }

            $script_time_elapsed_secs = microtime(true) - $script_start;

            echo '<p>'.$counter. ' αρχεία ελέγχθηκαν και παράχτηκαν hash</p>';
            echo '<p>Συνολικός χρόνος: '.$script_time_elapsed_secs;

            RoceanDB::insertLog($counter. ' αρχεία ελέγχθηκαν και παράχτηκαν hash'); // Προσθήκη της κίνησης στα logs
        }

    }

    // Ψάχνει αν το συγκεκριμένο $hash υπάρχει ήδη
    static function searchForHash($hash) {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql = 'SELECT id FROM files WHERE hash=?';
        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($hash));

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))

            $result=$item['id'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Ενημερώνει μαζικά την βάση με τα metadata των αρχείων. filesize, track time, video width, video height
    public function filesMetadata() {
        set_time_limit(0);

        $script_start = microtime(true);

        $conn = new RoceanDB();

        $counter=0;

        if($filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null)) // Ολόκληρη η λίστα
        {
            foreach ($filesOnDB as $file) {
                $full_path = DIR_PREFIX . $file['path'] . urldecode($file['filename']);
                if(OWMP::fileExists($full_path)) {

                    self::getMediaFileTags($full_path);  // Παίρνουμε τα metadata του αρχείου

                    // Ενημερώνουμε την βάση με τα αντίστοιχα metadata
                    $update = RoceanDB::updateTableFields('music_tags', 'id=?',
                        array('track_time', 'video_width', 'video_height', 'filesize'),
                        array($this->track_time, $this->video_width, $this->video_height, $this->size, $file['id']));

                    if ($update) {
                        $counter++;
                    }
                    else echo 'Πρόβλημα με το αρχειο '.$full_path;
                }


            }

            $script_time_elapsed_secs = microtime(true) - $script_start;

            echo '<p>'.$counter. ' αρχεία ελέγχθηκαν και ενημερώθηκαν τα metadata</p>';
            echo '<p>Συνολικός χρόνος: '.$script_time_elapsed_secs;

            RoceanDB::insertLog($counter. ' αρχεία ελέγχθηκαν και ενημερώθηκαν τα metadata'); // Προσθήκη της κίνησης στα logs
        }
    }


}