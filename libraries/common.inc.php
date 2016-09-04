<?php
/**
 * File: common.inc.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 22:36
 */


define ('PROJECT_PATH','/OpenWebMediaPlayer/');   // αν το project είναι σε κάποιον υποκατάλογο

require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Session.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Page.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/RoceanDB.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Crypto.php');
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/Language.php');

// Κλάση ειδικά για την συγκεκριμένη εφαρμογή
require_once ($_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'libraries/OWMP.php');





define('CONNSTR', 'mysql:host=localhost;dbname=OWMP');
define('DBUSER', 'root');
define('DBPASS', 'documents2015');

define('PAGE_TITTLE','Open Web Media Player');     // ονομασία της εφαρμογής που θα φαίνεται στον τίτλο της σελίδας

define ('LANG_PATH',$_SERVER["DOCUMENT_ROOT"]  .PROJECT_PATH.'lang/');      // το path του καταλόγου των γλωσσών. Να μην πειραχτεί
define ('LANG_PATH_HTTP',$_SERVER["HTTP_HOST"]  .PROJECT_PATH.'lang/');      // το path του καταλόγου των γλωσσών σε http. Να μην πειραχτεί

if (isset($_SERVER['HTTPS'])) define ('HTTP_TEXT', 'https://');
else define ('HTTP_TEXT', 'http://');

define ('NAV_LIST_ITEMS', '4'); // Ο αριθμός των επιλογών στo Nav Menu

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

define ('DIR_PREFIX','/media/');   // Το αρχικό κομμάτι του path.
define ('PLAYLIST_LIMIT',150);   // Τα κομμάτια που θα εμφανίζονται ανα σελίδα


// Public functions

// Καθαρίζει τα data που έδωσε ο χρήστης από περίεργο κώδικα
function ClearString($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


$conn = new RoceanDB();
$lang = new Language();




