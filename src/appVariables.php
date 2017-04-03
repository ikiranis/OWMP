<?php
/**
 *
 * File: appVariables.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 24/03/17
 * Time: 22:39
 *
 * Γενικές μεταβλητές και options της συγκεκριμένης εφαρμογής
 *
 */

use apps4net\framework\MyDB;

// ********* DEFINES



$mediaKinds = array ('Music Video', 'Music');    // Τα media kind που υποστηρίζονται
// Η διεύθυνση του checkValidImage script. Πρέπει να είναι ολόκληρο το url της εσωτερικής ip του server που τρέχει η εφαρμογή
// π.χ. http://192.168.1.19:9999/arduino
// αν το script τρέχει στον σερβερ της εφαρμογής, αφήνουμε αυτή την γραμμή όπως είναι, αλλιώς χρησιμοποιούμε τα παρακάτω παραδείγματα
define ('VALID_IMAGE_SCRIPT_ADDRESS', LOCAL_SERVER_IP_WITH_PORT.AJAX_PATH.'checkValidImage.php');
define ('JSON_FILENAME', 'playlist.json');
define ('ITUNES_FILENAME', 'Library.xml');
// Το αρχείο του itunes library
define ('ITUNES_LIBRARY_FILE', $_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. ITUNES_FILENAME);
// To json file της playlist για import
define ('JSON_PLAYLIST_FILE', $_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. JSON_FILENAME);
define ('WEB_FOLDER_PATH',$_SERVER['DOCUMENT_ROOT'].'/');  // To path του web folder
define ('DIR_PREFIX',$optionsArray['dir_prefix']);   // Το αρχικό κομμάτι του path.
// Το ποσοστό που θεωρείται ότι ένα τραγούδι έχει παιχτεί
define ('PLAY_PERCENTAGE',$optionsArray['play_percentage']);
define ('PLAYLIST_LIMIT',intval($optionsArray['playlist_limit']));   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα
define ('DATE_FORMAT',$optionsArray['date_format']);  // To format των ημερομηνιών που εμφανίζονται στο site
// το path που μετατρέπει τα ALAC
define ('INTERNAL_CONVERT_PATH', $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'ConvertedMusic/');
define ('CUR_PLAYLIST_STRING', 'crPl_'); // Το string που προσθέτει στο όνομα temp playlist
define ('MANUAL_PLAYLIST_STRING', 'mnPl_');  // To string που προσθέτει στο όνομα ενός manual playlist
define ('PLAYED_QUEUE_PLAYLIST_STRING', 'pqPl_');  // To string που προσθέτει στο όνομα ενός played queue playlist
define ('BACKUP_FILE_PREFIX', 'backup_');
define ('JUKEBOX_LIST_NAME', 'jukebox');  // Το όνομα του jukebox table

// *********  Τραβάει τιμές από την βάση για τα options


// Τα paths των αρχείων
$MusicMainDir=MyDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music'), 'file_path');
if($MusicMainDir) {
    define ('ALBUM_COVERS_DIR', $MusicMainDir.'/album_covers/');  // Ο φάκελος που ανεβαίνουν τα covers
    define ('MUSIC_UPLOAD', $MusicMainDir.'/Converted/');  // O φάκελος που μετατρέπονται τα mp3
}
else {
    define ('ALBUM_COVERS_DIR', null);  // Ο φάκελος που ανεβαίνουν τα covers
    define ('MUSIC_UPLOAD', null);  // O φάκελος που μετατρέπονται τα mp3
}

$MusicVideoMainDir=MyDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music Video'), 'file_path');
if($MusicVideoMainDir) {
    define ('VIDEO_FILE_UPLOAD', $MusicVideoMainDir.'/Download/');
    define ('OUTPUT_FOLDER', $MusicVideoMainDir.'/output/');
}
else  {
    define ('VIDEO_FILE_UPLOAD', null);
    define ('OUTPUT_FOLDER', null);
}

$MusicMainDir=MyDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music'), 'file_path');
if($MusicMainDir) {
    define ('MUSIC_FILE_UPLOAD', $MusicMainDir.'/Download/');
}
else  {
    define ('MUSIC_FILE_UPLOAD', null);
}



// Τα options
$convertALACOption= $optionsArray['convert_alac_files'];
if ($convertALACOption=='true')
    define ('CONVERT_ALAC_FILES', true); // true για να μετατρέπει τα ALAC
else define ('CONVERT_ALAC_FILES', false);

if ($optionsArray['syncItunes']=='true')
    define ('SYNC_ITUNES', true); // true για να μετατρέπει συγχρονίζει με itunes
else define ('SYNC_ITUNES', false);

if ($optionsArray['jukebox_enable']=='true')
    define ('JUKEBOX_ENABLE', true); // true για το αν θα εμφανίζεται η σελίδα για ψηφοφορίες
else define ('JUKEBOX_ENABLE', false);

// Τα settings του icecast server
if ($optionsArray['icecast_enable']=='true')
    define ('ICECAST_ENABLE', true); // true για το αν θα στέλνει τα songs info στον icecast server
else define ('ICECAST_ENABLE', false);

define ('ICECAST_SERVER', $optionsArray['icecast_server']);
define ('ICECAST_MOUNT', $optionsArray['icecast_mount']);
define ('ICECAST_USER', $optionsArray['icecast_user']);
define ('ICECAST_PASS', $optionsArray['icecast_pass']);


// Δημιουργεί την αρχική εγγραφή στο album_arts και παίρνει το id της, αν υπάρχει ήδη
$defaultArtwork=MyDB::getTableFieldValue('album_arts', 'filename=?', 'default.gif', 'id');
if($defaultArtwork)
    define ('DEFAULT_ARTWORK_ID', $defaultArtwork);
else {
    $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts
    $artsArray = array('', 'default.gif', '');
    if ($coverID = $conn->insertInto($sql, $artsArray)) // Παίρνουμε το id της εγγραφής που έγινε
        define('DEFAULT_ARTWORK', $coverID);
}


// ********* API keys
define ('YOUTUBE_API', $optionsArray['youtube_api']);
define ('GIPHY_API', 'dc6zaTOxFJmzC'); // default