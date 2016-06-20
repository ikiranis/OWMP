<?php
/**
 * File: common.inc.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 22:36
 */


require_once ('Page.php');
require_once ('Session.php');
require_once ('RoceanDB.php');
require_once ('Crypto.php');
require_once ('Language.php');

// Κλάση ειδικά για την συγκεκριμένη εφαρμογή
require_once ('OWMP.php');
require_once ('scanDir.php');

define (PROJECT_PATH,'/OpenWebMediaPlayer/');   // αν το project είναι σε κάποιον υποκατάλογο

define(CONNSTR, 'mysql:host=localhost;dbname=OWMP');
define(DBUSER, 'root');
define(DBPASS, 'documents2015');

define(PAGE_TITTLE,'Open Web Media Player');     // ονομασία της εφαρμογής που θα φαίνεται στον τίτλο της σελίδας

define (LANG_PATH,PROJECT_PATH.'lang/');      // το path του καταλόγου των γλωσσών. Να μην πειραχτεί

define (NAV_LIST_ITEMS, '2'); // Ο αριθμός των επιλογών στo Nav Menu

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



// Public functions

// Καθαρίζει τα data που έδωσε ο χρήστης από περίεργο κώδικα
function ClearString($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}





