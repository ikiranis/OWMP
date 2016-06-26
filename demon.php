<?php
/**
 * File: demon.php
 * Created by rocean
 * Date: 27/05/16
 * Time: 19:44
 * Συνεχής έλεγχος για την κατάσταση διάφορων πραγμάτων για alerts κτλ
 * Τρέχει συνεχώς στο crontab
 */

set_time_limit(0);

$general_start = microtime(true);

require_once('libraries/common.inc.php');

require_once ('libraries/scanDir.php');

// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once ('libraries/PlistParser.inc');


$files=array();
$tracks=array();
$tags=array();


function getItunesLibrary()
{
    global $tracks;
    global $tags;

    $parser = new plistParser();
    $plist = $parser->parseFile(dirname(__FILE__) . "/Library.xml");

    $tracks = $tags = $plist['Tracks'];

    $trimTracks=array();
    foreach ($tracks as $track) {
        $replace_text=array('file:///Volumes/', 'file://localhost/Volumes/');

        $location=urldecode(str_replace($replace_text,'',$track['Location']));

        $trimTracks[$track['Track ID']]=$location;
    }
    $tracks=$trimTracks;


}

function scanFiles ()
{
    global $files;

    $dirs = array('/media/Dalek/Videoclips', '/media/Dalek/New', '/media/Therion/videoclips');

//    $dirs = array('/media/Therion/videoclips');

    $extensions = array('mp4', 'm4v');

    $files = scanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs

    $files=array_unique($files);
    $trimFiles=array();

    foreach ($files as $file) {
        if (strpos($file, '._') == false)
            $trimFiles[]=urldecode(str_replace(DIR_PREFIX,'',$file));
    }

    $files=$trimFiles;

    $trimFiles='';

}

function writeTracks ()
{
    global $tracks;
    global $tags;
    global $files;


    $conn = new RoceanDB();

    $conn->CreateConnection();

    $sql_insert_file = 'INSERT INTO files (path, filename, hash, kind) VALUES (?,?,?,?)';

    $sql_insert_tags = 'INSERT INTO music_tags (id, name, artist, genre, date_added, play_count, 
                      date_last_played, rating, album, album_artwork_id, video_width, video_height, size, track_time) 
                      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    $stmt_file = RoceanDB::$conn->prepare($sql_insert_file);
    $stmt_tags = RoceanDB::$conn->prepare($sql_insert_tags);


    $counter = 0;
    $general_counter=0;



    $hash = '';

    $inserted_id=1;


    foreach ($files as $file) {

        $start = microtime(true);

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

        if($stmt_file->execute($sqlParamsFile))
            $inserted_id = RoceanDB::$conn->lastInsertId();
        else {
            $inserted_id=0;
            echo '<p>problem</p>';
        }


        $key = array_search($file, $tracks);


        if ( ($key) && (!$inserted_id==0) ) {   // Αν υπάρχει στην itunes library
            $track_id = $key;
//            echo $counter . ' ' . $file . ' βρέθηκε στο ' . $key . ' | name: ' . $tags[$track_id]['Name'] . ' artist=' . $tags[$track_id]['Artist'] . '<br>';
            echo 'found ' . $file . ' βρέθηκε στο ' . $key . '<br>';


            $name = $artist = $genre = $date_added = $play_date =  $album = '';

            $play_count = $rating = $size = $track_time = $video_width = $video_height = $album_artwork_id = 0;
// TODO να προσθέσω και year και αν είναι live ή όχι
            if ($tags[$track_id]['Name'])
                $name = ClearString($tags[$track_id]['Name']);

            if ($tags[$track_id]['Artist'])
                $artist = ClearString($tags[$track_id]['Artist']);

            if ($tags[$track_id]['Genre'])
                $genre = ClearString($tags[$track_id]['Genre']);

            if ($tags[$track_id]['Date Added'])
                $date_added = date('Y-m-d H:i:s',strtotime($tags[$track_id]['Date Added']));

            if ($tags[$track_id]['Play Count'])
                $play_count = intval($tags[$track_id]['Play Count']);

            if ($tags[$track_id]['Play Date'])
                $play_date =  date('Y-m-d H:i:s',strtotime($tags[$track_id]['Play Date UTC']));

            if ($tags[$track_id]['Rating'])
                $rating = intval($tags[$track_id]['Rating']);

            $sqlParamsTags = array($inserted_id, $name, $artist, $genre, $date_added, $play_count, $play_date, $rating,
                $album, $album_artwork_id, $video_width, $video_height, $size, $track_time
            );

            if($stmt_tags->execute($sqlParamsTags))
                echo $general_counter.' ' . $inserted_id.' '.$name.' '. $artist.' '. $genre.' '. $date_added.' '. $play_count.' '. $play_date.' '. $rating.' '.
                    $video_width.' '. $video_height.' '. $size.' '. $track_time.'<br>';
            else echo '<p>problem</p>';


            $counter++;

        }
        else echo '<p>not found '.$file.'</p>';

        $general_counter++;



    }

    echo '<p>Συγχρονίστηκαν με το itunes ' . $counter . " βίντεο. </p>";
}


scanFiles();
getItunesLibrary();
writeTracks();




//    echo'<pre>';
//    print_r($tracks);
//    echo'</pre>';

$time_elapsed=microtime(true) - $general_start;

echo '<p>Time: '.$time_elapsed.'</p>';

?>