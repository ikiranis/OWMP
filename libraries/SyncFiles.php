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

            $dirs = array('/media/Dalek/Videoclips', '/media/Dalek/New', '/media/Therion/videoclips');

    //    $dirs = array('/media/Therion/videoclips');

            $extensions = array('mp4', 'm4v');

            self::$files = scanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs

            self::$files = array_unique(self::$files);
            $trimFiles = array();

            foreach (self::$files as $file) {
                if (strpos($file, '._') == false)
                    $trimFiles[] = urldecode(str_replace(DIR_PREFIX, '', $file));
            }

            self::$files = $trimFiles;


//            echo'<pre>';
//        print_r(self::$files);
//        echo'</pre>';
//
//            echo count(self::$files);

        }

        // Γράφει τα αρχεία που βρίσκει στην βάση
        public function writeTracks($searchItunes)
        {
            $this->scanFiles();

            if($searchItunes)
                $this->getItunesLibrary();

            $conn = new RoceanDB();

            $filesOnDB = $conn->getTableArray('files', 'id, path, filename', null, null, null); // Ολόκληρη η λίστα

            foreach($filesOnDB as $file) {
                $newFilesOnDB[$file['id']]=$file['path'].$file['filename'];
            }
            $filesOnDB=$newFilesOnDB;

            $conn->CreateConnection();

            $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

            $sql_insert_tags = 'INSERT INTO music_tags (id, song_name, artist, genre, date_added, play_count, 
                          date_last_played, rating, album, album_artwork_id, video_width, video_height, filesize, track_time, song_year, live) 
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

            $stmt_file = RoceanDB::$conn->prepare($sql_insert_file);
            $stmt_tags = RoceanDB::$conn->prepare($sql_insert_tags);


            $counter = 0;
            $general_counter = 0;


            $hash = '';

            $inserted_id = 1;


            foreach (self::$files as $file) {


                $string_array = explode('/', $file);
                $filename = $string_array[count($string_array) - 1];
                $path = str_replace($filename, '', $file);


                if($fileKey=array_search($file, $filesOnDB)) {
//                    trigger_error('$filenameKey '.$fileKey.' COUNTER '.$general_counter);
                    $fileAlreadySynced=true;
                } else $fileAlreadySynced=false;

                
                if(!$fileAlreadySynced) {
                    
//                    trigger_error('TRYING TO SYNC '.$file['track_id'].' COUNTER '.$general_counter);


                    $full_path = DIR_PREFIX . $path . $filename;

                    $idtags = $this->getMediaFileTags($full_path);

                    $this->name = $idtags['title'];
                    $this->artist = $idtags['artist'];
                    $this->genre = $idtags['genre'];
                    $this->date_added = date('Y-m-d H:i:s');
                    $this->play_date = null;
                    $this->album = '';

                    $this->play_count = 0;
                    $this->rating = 0;
                    $this->size = $idtags['size'];
                    $this->track_time = $idtags['track_time'];
                    $this->video_width = $idtags['video_width'];
                    $this->video_height = $idtags['video_height'];
                    $this->album_artwork_id = 0;
                    $this->year = 0;
                    $this->live = 0;


                    ////        $full_path='/media/Dalek/Videoclips/Uncategorized/ПРЕМЬЕРА! Dasha Luks ft. Belozerov & Voronov - Raspberry.mp4';
                    //        $start = microtime(true);
                    //
                    ////        $result = explode("  ", exec("md5sum $full_path"));
                    ////        $hash=$result[0];
                    //
                    //
                    //        $hash = sha1_file($full_path);
                    //        $time_elapsed_secs = microtime(true) - $start;
                    //
                    ////        echo 'fullpath: '.$file.' filename: '.$filename.' path: '.$path.'<br>';
                    //
                    //        echo 'fullpath: '.$full_path.'  hash: '.$hash.' time: '.$time_elapsed_secs.'<br>';


                    // Αρχική εγγραφή στο files
                    $sqlParamsFile = array($path, $filename, $hash, 'Music Video');

                    if ($stmt_file->execute($sqlParamsFile)) {
                        $inserted_id = RoceanDB::$conn->lastInsertId();
//                        trigger_error('SUCCESS '.$inserted_id);
                    }
                    else {
                        $inserted_id = 0;
                        trigger_error('PROBLEM!!!!!!!!!!     $path ' . $path . ' $filename ' . $filename);
                    }



                    $status = 'not founded';

                    if ($searchItunes) {
                        $key = array_search($file, self::$tracks);


                        if (($key) && (!$inserted_id == 0)) {   // Αν υπάρχει στην itunes library
                            $track_id = $key;
                            //            echo $counter . ' ' . $file . ' βρέθηκε στο ' . $key . ' | name: ' . $tags[$track_id]['Name'] . ' artist=' . $tags[$track_id]['Artist'] . '<br>';

                            if (isset(self::$tags[$track_id]['Name'])) {
                                $this->name = ClearString(self::$tags[$track_id]['Name']);
                            }

                            if (isset(self::$tags[$track_id]['Artist'])) {
                                $this->artist = ClearString(self::$tags[$track_id]['Artist']);
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

                            $counter++;

                            $status = 'founded';

                        }
//                        else echo 'not found ' . $file;


//                    echo $this->name.'      found ' . $file . ' βρέθηκε στο ' . $key . '<br>';

                    }

                    $sqlParamsTags = array($inserted_id, $this->name, $this->artist, $this->genre, $this->date_added, $this->play_count,
                        $this->play_date, $this->rating, $this->album, $this->album_artwork_id, $this->video_width, $this->video_height,
                        $this->size, $this->track_time, $this->year, $this->live

                    );


                    if ($stmt_tags->execute($sqlParamsTags))
                        trigger_error($general_counter . ' SUCCESS!!!!!!!    ');
                    else trigger_error($general_counter . ' PROBLEM!!!!!!!    ' . $status . '       $inserted_id ' . $inserted_id . ' ' . '$this->name ' . $this->name . ' ' . '$this->artist ' . $this->artist . ' ' . '$this->genre ' . $this->genre . ' ' . '$this->date_added ' . $this->date_added . ' ' . '$this->play_count ' . $this->play_count . ' ' .
                        '$this->play_date ' . $this->play_date . ' ' . '$this->rating ' . $this->rating . ' ' . '$this->album ' . $this->album . ' ' . '$this->album_artwork_id ' . $this->album_artwork_id . ' ' . '$this->video_width ' . $this->video_width . ' ' . '$this->video_height ' . $this->video_height . ' ' .
                        '$this->size ' . $this->size . ' ' . '$this->track_time ' . $this->track_time . ' ' . '$this->year ' . $this->year . ' ' . '$this->live ' . $this->live);


                }

                $general_counter++;

//                if($general_counter>16000) die();
                
            }

            echo '<p>Συγχρονίστηκαν με το itunes ' . $counter . " βίντεο. </p>";
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


        $replace_text = array('.mp4', '.m4v');

        if(isset($ThisFileInfo['comments_html']['title'][0]))
            if($this->detectUTF8($ThisFileInfo['comments_html']['title'][0])) {
                $title = ClearString($ThisFileInfo['comments_html']['title'][0]);
                trigger_error('EINAI UTF-8');
            }
            else $title=str_replace($replace_text,'',$ThisFileInfo['filename']);
        else $title=str_replace($replace_text,'',$ThisFileInfo['filename']);
        
        if(isset($ThisFileInfo['comments_html']['artist'][0]))
            if($this->detectUTF8($ThisFileInfo['comments_html']['artist'][0]))
                $artist=ClearString($ThisFileInfo['comments_html']['artist'][0]);
            else $artist='';
        else $artist='';
        
        if(isset($ThisFileInfo['filesize']))
            $size=intval($ThisFileInfo['filesize']);
        else $size=0;
        
        if(isset($ThisFileInfo['video']['resolution_x']))
            $video_width=intval($ThisFileInfo['video']['resolution_x']);
        else $video_width=0;
        
        if(isset($ThisFileInfo['video']['resolution_y']))
            $video_height=intval($ThisFileInfo['video']['resolution_y']);
        else $video_height=0;
        
        if(isset($ThisFileInfo['comments_html']['genre'][0])) {
            $genre = ClearString($ThisFileInfo['comments_html']['genre'][0]);
            $genre=substr($genre,0,19);
        }
        else $genre='';
        


        if(isset($ThisFileInfo['playtime_seconds']))
            $track_time=floatval($ThisFileInfo['playtime_seconds']);
        else $track_time=0;

        $result=array(
            'artist'=>$artist,
            'title'=>$title,
            'size'=>$size,
            'video_width'=>$video_width,
            'video_height'=>$video_height,
            'genre'=>$genre,
            'track_time'=>$track_time


            ) ;

//        echo'<pre>';
//        print_r($result);
//        echo'</pre>';
//
//        echo'<pre>';
//        print_r($ThisFileInfo);
//        echo'</pre>';

//        unset(self::$getID3);

        return $result;
    }



    // Κεντρική function που κάνει τον συγχρονισμό
    public function syncTheFiles() {
        set_time_limit(0);
//        ini_set('memory_limit','1500M');

//        $getID3 = new getID3;

        $this->writeTracks(true);

//        $this->getMediaFileTags('/media/Therion/videoclips/Pop/Sugababes/Sugababes_ CD_UK 19.11.2005 - Ugly(360p_H.264-AAC).mp4');

//        echo'<pre>';
//        print_r(self::$tags);
//        echo'</pre>';
    }


}







