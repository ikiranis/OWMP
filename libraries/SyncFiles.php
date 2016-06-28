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

class SyncFiles
{


    static $files = array();
    static $tracks = array();
    static $tags = array();


        public function getItunesLibrary()
        {
            global $tracks;
            global $tags;

            $parser = new plistParser();
            $plist = $parser->parseFile($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. "Library.xml");

            self::$tracks = self::$tags = $plist['Tracks'];

            $trimTracks = array();
            foreach (self::$tracks as $track) {
                $replace_text = array('file:///Volumes/', 'file://localhost/Volumes/');

                $location = urldecode(str_replace($replace_text, '', $track['Location']));

                $trimTracks[$track['Track ID']] = $location;
            }
            self::$tracks = $trimTracks;


        }

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

        }

        public function writeTracks()
        {


            $conn = new RoceanDB();

            $conn->CreateConnection();

            $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

            $sql_insert_tags = 'INSERT INTO music_tags (id, name, artist, genre, date_added, play_count, 
                          date_last_played, rating, album, album_artwork_id, video_width, video_height, size, track_time) 
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

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

                $full_path = DIR_PREFIX . $path . $filename;


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

//                if ($stmt_file->execute($sqlParamsFile))
//                    $inserted_id = RoceanDB::$conn->lastInsertId();
//                else {
//                    $inserted_id = 0;
//                    echo '<p>problem</p>';
//                }


                $key = array_search($file, self::$tracks);


                if (($key) && (!$inserted_id == 0)) {   // Αν υπάρχει στην itunes library
                    $track_id = $key;
    //            echo $counter . ' ' . $file . ' βρέθηκε στο ' . $key . ' | name: ' . $tags[$track_id]['Name'] . ' artist=' . $tags[$track_id]['Artist'] . '<br>';
                    echo 'found ' . $file . ' βρέθηκε στο ' . $key . '<br>';


                    $name = $artist = $genre = $date_added = $play_date = $album = '';

                    $play_count = $rating = $size = $track_time = $video_width = $video_height = $album_artwork_id = 0;
    // TODO να προσθέσω και year και αν είναι live ή όχι
                    if (self::$tags[$track_id]['Name'])
                        $name = ClearString(self::$tags[$track_id]['Name']);

                    if (self::$tags[$track_id]['Artist'])
                        $artist = ClearString(self::$tags[$track_id]['Artist']);

                    if (self::$tags[$track_id]['Genre'])
                        $genre = ClearString(self::$tags[$track_id]['Genre']);

                    if (self::$tags[$track_id]['Date Added'])
                        $date_added = date('Y-m-d H:i:s', strtotime(self::$tags[$track_id]['Date Added']));

                    if (self::$tags[$track_id]['Play Count'])
                        $play_count = intval(self::$tags[$track_id]['Play Count']);

                    if (self::$tags[$track_id]['Play Date'])
                        $play_date = date('Y-m-d H:i:s', strtotime(self::$tags[$track_id]['Play Date UTC']));

                    if (self::$tags[$track_id]['Rating'])
                        $rating = intval(self::$tags[$track_id]['Rating']);

                    $sqlParamsTags = array($inserted_id, $name, $artist, $genre, $date_added, $play_count, $play_date, $rating,
                        $album, $album_artwork_id, $video_width, $video_height, $size, $track_time
                    );

//                    if ($stmt_tags->execute($sqlParamsTags))
//                        echo $general_counter . ' ' . $inserted_id . ' ' . $name . ' ' . $artist . ' ' . $genre . ' ' . $date_added . ' ' . $play_count . ' ' . $play_date . ' ' . $rating . ' ' .
//                            $video_width . ' ' . $video_height . ' ' . $size . ' ' . $track_time . '<br>';
//                    else echo '<p>problem</p>';


                    $counter++;

                } else echo '<p>not found ' . $file . '</p>';

                $general_counter++;


            }

            echo '<p>Συγχρονίστηκαν με το itunes ' . $counter . " βίντεο. </p>";
        }

    public function syncTheFiles() {
        set_time_limit(0);

        $this->scanFiles();
        $this->getItunesLibrary();
        $this->writeTracks();
    }


}





//    echo'<pre>';
//    print_r($tracks);
//    echo'</pre>';

