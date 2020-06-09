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
use apps4net\framework\Utilities;

// ********* DEFINES

define ('APP_VERSION', '0.18.17');
define ('APP_NAME','Parrot Tunes : Open Web Media Library & Player');     // ονομασία της εφαρμογής

$mediaKinds = array ('Music Video', 'Music');    // Τα media kind που υποστηρίζονται

// Τα tables για το backup
$backupTables = array('user', 'user_details', 'salts', 'options', 'manual_playlists', 'smart_playlists',
    'files', 'album_arts', 'music_tags', 'paths', 'download_paths');
// Τα tables για το restore
$restoreTables = array('manual_playlists', 'smart_playlists', 'salts', 'user_details', 'user',
    'options', 'music_tags', 'album_arts', 'files', 'paths', 'download_paths');

// Η διεύθυνση του checkValidImage script. Πρέπει να είναι ολόκληρο το url της εσωτερικής ip του server που τρέχει η εφαρμογή
// π.χ. http://192.168.1.19:9999/arduino
if(!Utilities::isInDockerContainer()) {
    define ('VALID_IMAGE_SCRIPT_ADDRESS', LOCAL_SERVER_IP_WITH_PORT . AJAX_PATH . 'app/checkValidImage');
} else {
    define ('VALID_IMAGE_SCRIPT_ADDRESS', HTTP_TEXT . $_SERVER['SERVER_ADDR'] . '/' . AJAX_PATH . 'app/checkValidImage');
}

define ('JSON_FILENAME', 'playlist.json');
define ('ITUNES_FILENAME', 'Library.xml');
// Το αρχείο του itunes library
define ('ITUNES_LIBRARY_FILE', $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . ITUNES_FILENAME);
// To json file της playlist για import
define ('JSON_PLAYLIST_FILE', $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . JSON_FILENAME);
define ('WEB_FOLDER_PATH',$_SERVER['DOCUMENT_ROOT'].'/');  // To path του web folder
define ('DIR_PREFIX',$optionsArray['dir_prefix']);   // Το αρχικό κομμάτι του path.
// Το ποσοστό που θεωρείται ότι ένα τραγούδι έχει παιχτεί
define ('PLAY_PERCENTAGE',$optionsArray['play_percentage']);
define ('PLAYLIST_LIMIT',intval($optionsArray['playlist_limit']));   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα
define ('DATE_FORMAT',$optionsArray['date_format']);  // To format των ημερομηνιών που εμφανίζονται στο site
define ('MAX_VIDEO_HEIGHT',$optionsArray['max_video_height']);  // To μέγιστο ύψος του youtube video που θα κατεβάσει
define ('FW_STEP',$optionsArray['fw_step']);  // Το βήμα που κάνει (σε δευτερόλεπτα) όταν πηγαίνεις αριστερά/δεξιά στο τραγούδι
// το path που μετατρέπει τα ALAC
//define ('INTERNAL_CONVERT_PATH', $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'ConvertedMusic/');
define ('CUR_PLAYLIST_STRING', 'crPl_'); // Το string που προσθέτει στο όνομα temp playlist
define ('MANUAL_PLAYLIST_STRING', 'mnPl_');  // To string που προσθέτει στο όνομα ενός manual playlist
define ('PLAYED_QUEUE_PLAYLIST_STRING', 'pqPl_');  // To string που προσθέτει στο όνομα ενός played queue playlist
define ('BACKUP_FILE_PREFIX', 'backup_');
define ('JUKEBOX_LIST_NAME', 'jukebox');  // Το όνομα του jukebox table
define ('TEMP_RESTORE_DATABASE_FILE', 'temp_restore_database.sql');
// Από εδώ τραβάει την τρέχουσα έκδοση της εφαρμογής
define ('APP_VERSION_FILE', 'https://apps4net.eu/dev/ParrotTunesVersion.js');

// *********  Τραβάει τιμές από την βάση για τα options


// Τα paths των αρχείων
define ('ALBUM_COVERS_DIR', $downloadPaths['coverAlbumsFolder'].'/');  // Ο φάκελος που ανεβαίνουν τα covers
define ('INTERNAL_CONVERT_PATH', $downloadPaths['convertedALAC'].'/');  // O φάκελος που μετατρέπονται τα mp3
define ('VIDEO_FILE_UPLOAD', $downloadPaths['musicVideoDownloadPath'].'/'); // Ο φάκελος που κατεβαίνουν τα videoclips
define ('OUTPUT_FOLDER', $downloadPaths['outputFolder'].'/');  // Ο φάκελος που κάνει τις οποιεσδήποτε εξαγωγές η εφαρμογή
define ('MUSIC_FILE_UPLOAD', $downloadPaths['musicDownloadPath'].'/');  // Ο φάκελος που κατεβαίνουν τα audio files

define ('LOW_BITRATE_TEMP_FOLDER', OUTPUT_FOLDER . 'temp/');

// Τα options
$convertALACOption = $optionsArray['convert_alac_files'];
if ($convertALACOption == 'true')
    define ('CONVERT_ALAC_FILES', true); // true για να μετατρέπει τα ALAC
else define ('CONVERT_ALAC_FILES', false);

if ($optionsArray['syncItunes'] == 'true')
    define ('SYNC_ITUNES', true); // true για να μετατρέπει συγχρονίζει με itunes
else define ('SYNC_ITUNES', false);

if ($optionsArray['jukebox_enable'] == 'true')
    define ('JUKEBOX_ENABLE', true); // true για το αν θα εμφανίζεται η σελίδα για ψηφοφορίες
else define ('JUKEBOX_ENABLE', false);

// Τα settings του icecast server
if ($optionsArray['icecast_enable'] == 'true')
    define ('ICECAST_ENABLE', true); // true για το αν θα στέλνει τα songs info στον icecast server
else define ('ICECAST_ENABLE', false);

define ('ICECAST_SERVER', $optionsArray['icecast_server']);
define ('ICECAST_MOUNT', $optionsArray['icecast_mount']);
define ('ICECAST_USER', $optionsArray['icecast_user']);
define ('ICECAST_PASS', $optionsArray['icecast_pass']);

define ('LOW_AUDIO_BITRATE', $optionsArray['low_audio_bitrate']);


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
