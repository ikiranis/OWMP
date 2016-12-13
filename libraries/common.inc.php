<?php
/**
 * File: common.inc.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 22:36
 * 
 * Αρχικές ρυθμίσεις εφαρμογής
 */


define ('APP_VERSION', '0.1.214');
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

$mySqlTables = array (
    array ('table' => 'album_arts', 'sql' => 'CREATE TABLE `album_arts` (
                                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                              `path` varchar(255) DEFAULT NULL,
                                              `filename` varchar(255) DEFAULT NULL,
                                              `hash` varchar(100) DEFAULT NULL,
                                              PRIMARY KEY (`id`)
                                              ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'logs', 'sql' => 'CREATE TABLE `logs` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `message` varchar(255) DEFAULT NULL,
                                          `ip` varchar(15) DEFAULT NULL,
                                          `user_name` varchar(15) DEFAULT NULL,
                                          `log_date` datetime DEFAULT NULL,
                                          `browser` varchar(70) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'options', 'sql' => 'CREATE TABLE `options` (
                                          `option_id` tinyint(4) NOT NULL AUTO_INCREMENT,
                                          `option_name` varchar(20) NOT NULL,
                                          `option_value` varchar(255) NOT NULL,
                                          `setting` tinyint(1) NOT NULL,
                                          `encrypt` tinyint(1) DEFAULT NULL,
                                          PRIMARY KEY (`option_id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'paths', 'sql' => 'CREATE TABLE `paths` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `file_path` varchar(255) DEFAULT NULL,
                                          `kind` varchar(15) DEFAULT NULL,
                                          `main` tinyint(1) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'playlist_tables', 'sql' => 'CREATE TABLE `playlist_tables` (
                                                      `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                                      `table_name` varchar(20) DEFAULT NULL,
                                                      `last_alive` datetime DEFAULT NULL,
                                                      PRIMARY KEY (`id`)
                                                    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'progress', 'sql' => 'CREATE TABLE `progress` (
                                              `progressID` int(11) NOT NULL AUTO_INCREMENT,
                                              `progressName` varchar(20) DEFAULT NULL,
                                              `progressValue` varchar(255) DEFAULT NULL,
                                              PRIMARY KEY (`progressID`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'Session', 'sql' => 'CREATE TABLE `Session` (
                                              `Session_Id` varchar(255) NOT NULL,
                                              `Session_Time` datetime DEFAULT NULL,
                                              `Session_Data` longtext,
                                              PRIMARY KEY (`Session_Id`),
                                              UNIQUE KEY `Session_Id_UNIQUE` (`Session_Id`)
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'user', 'sql' => 'CREATE TABLE `user` (
                                              `user_id` int(11) NOT NULL AUTO_INCREMENT,
                                              `username` varchar(15) NOT NULL,
                                              `email` varchar(255) NOT NULL,
                                              `password` varchar(255) NOT NULL,
                                              `agent` varchar(15) NOT NULL,
                                              `user_group` smallint(6) DEFAULT NULL,
                                              PRIMARY KEY (`user_id`),
                                              UNIQUE KEY `user_id_UNIQUE` (`user_id`),
                                              UNIQUE KEY `username_UNIQUE` (`username`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'salts', 'sql' => 'CREATE TABLE `salts` (
                                          `user_id` int(11) NOT NULL,
                                          `salt` varchar(255) DEFAULT NULL,
                                          `algo` varchar(6) DEFAULT NULL,
                                          `cost` varchar(3) DEFAULT NULL,
                                          PRIMARY KEY (`user_id`),
                                          CONSTRAINT `fk_salts_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'user_details', 'sql' => 'CREATE TABLE `user_details` (
                                          `user_id` int(11) NOT NULL,
                                          `fname` varchar(15) DEFAULT NULL,
                                          `lname` varchar(25) DEFAULT NULL,
                                          PRIMARY KEY (`user_id`),
                                          UNIQUE KEY `user_id_UNIQUE` (`user_id`),
                                          CONSTRAINT `fk_user_details_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'files', 'sql' => 'CREATE TABLE `files` (
                                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                              `path` varchar(255) NOT NULL,
                                              `filename` varchar(255) NOT NULL,
                                              `hash` varchar(100) DEFAULT NULL,
                                              `kind` varchar(20) DEFAULT NULL,
                                              PRIMARY KEY (`id`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'music_tags', 'sql' => 'CREATE TABLE `music_tags` (
                                              `id` bigint(20) NOT NULL,
                                              `song_name` varchar(255) DEFAULT NULL,
                                              `artist` varchar(255) DEFAULT NULL,
                                              `genre` varchar(20) DEFAULT NULL,
                                              `date_added` datetime DEFAULT NULL,
                                              `play_count` int(11) DEFAULT NULL,
                                              `date_last_played` datetime DEFAULT NULL,
                                              `rating` tinyint(4) DEFAULT NULL,
                                              `album` varchar(255) DEFAULT NULL,
                                              `video_height` int(11) DEFAULT NULL,
                                              `filesize` bigint(20) DEFAULT NULL,
                                              `video_width` int(11) DEFAULT NULL,
                                              `track_time` float DEFAULT NULL,
                                              `song_year` int(11) DEFAULT NULL,
                                              `live` tinyint(1) DEFAULT NULL,
                                              `album_artwork_id` bigint(20) NOT NULL,
                                              PRIMARY KEY (`id`),
                                              KEY `fk_music_tags_album_arts1_idx` (`album_artwork_id`),
                                              CONSTRAINT `fk_music_tags_album_arts1` FOREIGN KEY (`album_artwork_id`) REFERENCES `album_arts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                                              CONSTRAINT `fk_music_tags_files1` FOREIGN KEY (`id`) REFERENCES `files` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'manual_playlists', 'sql' => 'CREATE TABLE `manual_playlists` (
                                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                          `table_name` varchar(20) DEFAULT NULL,
                                          `playlist_name` varchar(50) DEFAULT NULL,
                                          `user_id` int(11) NOT NULL,
                                          PRIMARY KEY (`id`,`user_id`),
                                          KEY `fk_manual_playlists_user1_idx` (`user_id`),
                                          CONSTRAINT `fk_manual_playlists_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;')
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
if($MusicVideoMainDir) {
    define ('FILE_UPLOAD', $MusicVideoMainDir.'/Download/');
    define ('OUTPUT_FOLDER', $MusicVideoMainDir.'/output/');
}
else  {
    define ('FILE_UPLOAD', null);
    define ('OUTPUT_FOLDER', null);
}

$convertALACOption= $conn->getOption('convert_alac_files');
if ($convertALACOption=='true')
    define ('CONVERT_ALAC_FILES', true); // true για να μετατρέπει τα ALAC
else define ('CONVERT_ALAC_FILES', false);

if ($conn->getOption('syncItunes')=='true')
    define ('SYNC_ITUNES', true); // true για να μετατρέπει συγχρονίζει με itunes
else define ('SYNC_ITUNES', false);


define ('JSON_FILENAME', 'playlist.json');
define ('ITUNES_LIBRARY_FILE', $_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. JSON_FILENAME);  // Το αρχείο του itunes library
define ('JSON_PLAYLIST_FILE', $_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH. 'playlist.json');  // To json file της playlist για import
define ('WEB_FOLDER_PATH',$_SERVER['DOCUMENT_ROOT'].'/');  // To path του web folder
define ('DIR_PREFIX',$conn->getOption('dir_prefix'));   // Το αρχικό κομμάτι του path.
define ('PLAYLIST_LIMIT',intval($conn->getOption('playlist_limit')));   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα
define ('DATE_FORMAT',$conn->getOption('date_format'));  // To format των ημερομηνιών που εμφανίζονται στο site

// το path που μετατρέπει τα ALAC
define ('INTERNAL_CONVERT_PATH', $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'ConvertedMusic/');

define ('CUR_PLAYLIST_STRING', 'crPl_'); // Το string που προσθέτει στο όνομα temp playlist
define ('MANUAL_PLAYLIST_STRING', 'mnPl_');  // To string που προσθέτει στο όνομα ενός manual playlist


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





