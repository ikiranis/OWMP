<?php
/**
 * File: common.inc.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 22:36
 * 
 * Αρχικές ρυθμίσεις εφαρμογής
 */

define ('APP_VERSION', '0.1.105');
define('APP_NAME','Parrot Tunes : Open Web Media Library & Player');     // ονομασία της εφαρμογής 

require_once ('config.inc.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Session.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Page.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/RoceanDB.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Crypto.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Language.php');

// Κλάση ειδικά για την συγκεκριμένη εφαρμογή
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/OWMP.php');


define ('LANG_PATH',$_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'lang/');      // το path του καταλόγου των γλωσσών. Να μην πειραχτεί
define ('LANG_PATH_HTTP',$_SERVER["HTTP_HOST"]  .PROJECT_PATH.'lang/');      // το path του καταλόγου των γλωσσών σε http. Να μην πειραχτεί


define ('AJAX_PATH', 'AJAX/');

if (isset($_SERVER['HTTPS'])) define ('HTTP_TEXT', 'https://');  // αν είναι https
else define ('HTTP_TEXT', 'http://');

// TODO να το αλλάξω. να μην χρειάζεται με χρήση foreach
define ('NAV_LIST_ITEMS', '5'); // Ο αριθμός των επιλογών στo Nav Menu

$adminNavItems = array(3,4);  // Οι αριθμοί των items που είναι μόνο για τον admin

$languages = array (    // Οι γλώσσες που υποστηρίζονται
    array ('language' => 'Ελληνικά',
        'lang_id' => 'gr'),
    array ('language' => 'English',
        'lang_id' => 'en')
);

$UserGroups = array (     // Τα user groups που υπάρχουν
    array ('id' => '1',
        'group_name' => 'admin'),
    array ('id' => '2',
        'group_name' => 'user')
);



// OWMP variables


$mediaKinds = array ('Music Video', 'Music');    // Τα media kind που υποστηρίζονται

// ελέγχει και εισάγει τις αρχικές τιμές στον πίνακα options
OWMP::startBasicOptions();

$conn = new RoceanDB();
$lang = new Language();

// Τραβάει τιμές από την βάση
$MusicMainDir=RoceanDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music'), 'file_path');
if($MusicMainDir) {
    define ('ALBUM_COVERS_DIR', $MusicMainDir.'/album_covers/');  // Ο φάκελος που ανεβαίνουν τα covers
    define ('MUSIC_UPLOAD', $MusicMainDir.'/Converted/');  // O φάκελος που μετατρέπονται τα mp3
}
else {
    define ('ALBUM_COVERS_DIR', null);  // Ο φάκελος που ανεβαίνουν τα covers
    define ('MUSIC_UPLOAD', null);  // O φάκελος που μετατρέπονται τα mp3
}

$MusicVideoMainDir=RoceanDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music Video'), 'file_path');
if($MusicVideoMainDir)
    define ('FILE_UPLOAD', $MusicVideoMainDir.'/Download/');
else define ('FILE_UPLOAD', null);

$convertALACOption= $conn->getOption('convert_alac_files');
if ($convertALACOption=='true')
    define ('CONVERT_ALAC_FILES', true); // true για να μετατρέπει τα ALAC
else define ('CONVERT_ALAC_FILES', false);

if ($conn->getOption('syncItunes')=='true')
    define ('SYNC_ITUNES', true); // true για να μετατρέπει συγχρονίζει με itunes
else define ('SYNC_ITUNES', false);



define ('WEB_FOLDER_PATH',$conn->getOption('web_folder_path'));  // To path του web folder
define ('DIR_PREFIX',$conn->getOption('dir_prefix'));   // Το αρχικό κομμάτι του path.
define ('PLAYLIST_LIMIT',intval($conn->getOption('playlist_limit')));   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα
define ('DATE_FORMAT',$conn->getOption('date_format'));  // To format των ημερομηνιών που εμφανίζονται στο site

// το path που μετατρέπει τα ALAC
define ('INTERNAL_CONVERT_PATH', $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'ConvertedMusic/');

define ('CUR_PLAYLIST_STRING', '_curPlaylist'); // Το string που προσθέτει στο όνομα temp playlist


// Δημιουργεί την αρχική εγγραφή στο album_arts και παίρνει το id της, αν υπάρχει ήδη
$defaultArtwork=RoceanDB::getTableFieldValue('album_arts', 'filename=?', 'default.gif', 'id');
if($defaultArtwork)
    define ('DEFAULT_ARTWORK_ID', $defaultArtwork);
else {
    $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts
    $artsArray = array('', 'default.gif', '');
    if ($coverID = $conn->ExecuteSQL($sql, $artsArray)) // Παίρνουμε το id της εγγραφής που έγινε
        define('DEFAULT_ARTWORK', $coverID);
}

// API keys

define ('YOUTUBE_API', 'AIzaSyB0EhRlptkV7rZXkgi_WsMf-7x8E0EfJ4Q'); // βάζεις το δικό σου αν θες
define ('GIPHY_API', 'dc6zaTOxFJmzC'); // default

define ('PARROT_VERSION_FILE', 'http://www.apps4net.eu/dev/ParrotTunesVersion.php');




// Public functions

// Καθαρίζει τα data που έδωσε ο χρήστης από περίεργο κώδικα
function ClearString($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}





