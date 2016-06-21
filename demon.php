<?php
/**
 * File: demon.php
 * Created by rocean
 * Date: 27/05/16
 * Time: 19:44
 * Συνεχής έλεγχος για την κατάσταση διάφορων πραγμάτων για alerts κτλ
 * Τρέχει συνεχώς στο crontab
 * PHP Mailer https://github.com/PHPMailer/PHPMailer
 */



require_once('libraries/common.inc.php');

require_once ('libraries/scanDir.php');

// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once ('libraries/PlistParser.inc');


$files=array();
$tracks=array();

function getItunesLibrary()
{
    global $tracks;

    $parser = new plistParser();
    $plist = $parser->parseFile(dirname(__FILE__) . "/Library.xml");

    $tracks = $plist['Tracks'];

    $trimTracks=array();
    foreach ($tracks as $track) {
        $trimTracks[]=urldecode(trim($track['Location'],'file://localhost/Volumes/'));
        // TODO δεν πρέπει να μετατρέπει τα ελληνικά. Να δω για encoding 
    }

    $tracks=$trimTracks;

    //echo '<p>'.count($tracks).'</p>';
    //
    //foreach ($tracks as $track){
    //    echo $track['Location'].'<br>';
    //}


    //echo'<pre>';
    //print_r($tracks);
    //echo'</pre>';

}

function scanFiles ()
{
    global $files;

    $conn = new RoceanDB();

    $dirs = array('/media/Dalek/Videoclips', '/media/Dalek/New', '/media/Therion/videoclips');

//    $dirs = array('/media/Dalek/Videoclips');

    $extensions = array('mp4', 'm4v');

    $files = scanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs

    $trimFiles=array();
    foreach ($files as $file) {
        if (strpos($file, '._') == false)
            $trimFiles[]=trim($file,'/media');
    }

    $files=array_unique($trimFiles);

//    echo '<p>Σύνολο αρχείων: ' . count($files) . '</p>';
//
//    $sql = 'INSERT INTO files(dir_path) VALUES(?)';  // Εισάγει στον πίνακα user_details
//    $counter = 1;
//    foreach ($files as $value) {
//
//        if (strpos($value, '._') == false) {   // αν το αρχείο δεν περιέχει '._'
//            $filesArray = array($value);
//
//            if ($conn->ExecuteSQL($sql, $filesArray)) echo ' ok ';
//            else echo ' not ok ';
//
//            $counter++;
//
//        }
//
//
//    }
//
//    echo '<p>Συνολο αρχείων ' . $counter . '</p>';
}


scanFiles();
getItunesLibrary();

$counter=0;

foreach ($files as $file) {
    if($key=array_search($file,$tracks)) {

        echo $counter.' '.$file . ' βρέθηκε στο ' . $key . ' | ' . $tracks[$key] . '<br>';
        $counter++;
    }

}

echo '<p>Βρέθηκαν '.$counter." κοινα</p>";


//    echo'<pre>';
//    print_r($files);
//    echo'</pre>';

?>