<?php
/**
 * File: common.inc.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 22:36
 * 
 * Αρχικές ρυθμίσεις εφαρμογής
 */

define ('PROJECT_PATH','/OpenWebMediaPlayer/');   // αν το project είναι σε κάποιον υποκατάλογο
define('PAGE_TITTLE','Open Web Media Player');     // ονομασία της εφαρμογής που θα φαίνεται στον τίτλο της σελίδας


require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/config.inc.php');
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

$conn = new RoceanDB();
$lang = new Language();

// Τραβάει τιμές από την βάση
$MusicMainDir=RoceanDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music'), 'file_path');
$MusicVideoMainDir=RoceanDB::getTableFieldValue('paths', 'main=? and kind=?', array(1, 'Music Video'), 'file_path');
$convertALACOption= $conn->getOption('convert_alac_files');
if ($convertALACOption=='true')
    define ('CONVERT_ALAC_FILES', true); // true για να μετατρέπει τα ALAC
else define ('CONVERT_ALAC_FILES', false);

if ($conn->getOption('syncItunes')=='true')
    define ('SYNC_ITUNES', true); // true για να μετατρέπει συγχρονίζει με itunes
else define ('SYNC_ITUNES', false);


define ('DIR_PREFIX',$conn->getOption('dir_prefix'));   // Το αρχικό κομμάτι του path.
define ('PLAYLIST_LIMIT',intval($conn->getOption('playlist_limit')));   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα

// Paths που χρησιμοποιεί η εφαρμογή
define ('ALBUM_COVERS_DIR', $MusicMainDir.'/album_covers/');  // Ο φάκελος που ανεβαίνουν τα covers
define ('MUSIC_UPLOAD', $MusicMainDir.'/Converted/');  // O φάκελος που μετατρέπονται τα mp3
define ('INTERNAL_CONVERT_PATH', $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'ConvertedMusic/');
define ('FILE_UPLOAD', $MusicVideoMainDir.'/Download/');



// Δημιουργεί την αρχική εγγραφή στο album_arts και παίρνει το id της, αν υπάρχει ήδη
$defaultArtwork=RoceanDB::getTableFieldValue('album_arts', 'filename=?', 'default.gif', 'id');
if($defaultArtwork)
    define ('DEFAULT_ARTWORK_ID', $defaultArtwork);
else {
    if(OWMP::createDirectory(ALBUM_COVERS_DIR)) {  // Δημιουργεί το directory αν δεν υπάρχει
        // Αν δεν υπάρχει ήδη εγγραφή, αντιγράφει το default.gif και κάνει την εγγραφή
        if (copy('../img/default.gif', ALBUM_COVERS_DIR . 'default.gif')) {
            $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts
            $artsArray = array('', 'default.gif', '');
            if ($coverID = $conn->ExecuteSQL($sql, $artsArray)) // Παίρνουμε το id της εγγραφής που έγινε
                define('DEFAULT_ARTWORK', $coverID);
        }
    }
}

// API keys

define ('YOUTUBE_API', 'AIzaSyB0EhRlptkV7rZXkgi_WsMf-7x8E0EfJ4Q'); // βάζεις το δικό σου αν θες
define ('GIPHY_API', 'dc6zaTOxFJmzC'); // default






// Public functions

// Καθαρίζει τα data που έδωσε ο χρήστης από περίεργο κώδικα
function ClearString($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}





