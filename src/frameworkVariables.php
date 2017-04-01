<?php
/**
 *
 * File: frameworkVariables.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 24/03/17
 * Time: 22:40
 *
 * Γενικές μεταβλητές και options του Framework
 *
 */

use apps4net\framework\MyDB;
use apps4net\framework\Language;

$conn = new MyDB();

define ('APP_VERSION', '0.3.4');
define ('APP_NAME','Parrot Tunes : Open Web Media Library & Player');     // ονομασία της εφαρμογής

define ('LANG_PATH',$_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'lang/');      // το path του καταλόγου των γλωσσών. Να μην πειραχτεί
define ('LANG_PATH_HTTP',$_SERVER["HTTP_HOST"] . PROJECT_PATH . 'lang/');      // το path του καταλόγου των γλωσσών σε http. Να μην πειραχτεί

define ('AJAX_PATH', 'AJAX/');

define ('WEB_PAGE_URL', 'http://apps4net.eu');
define ('CHANGE_LOG_URL', 'http://apps4net.eu/?page_id=41');

if (isset($_SERVER['HTTPS'])) define ('HTTP_TEXT', 'https://');  // αν είναι https
else define ('HTTP_TEXT', 'http://');

// Παίρνει ολόκληρο το url του project με την εσωτερική ip του server
define ('LOCAL_SERVER_IP_WITH_PORT', HTTP_TEXT.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].PROJECT_PATH);

define ('NAV_LIST_ITEMS', '5'); // Ο αριθμός των επιλογών στo Nav Menu

$adminNavItems = array(3,4);  // Οι αριθμοί των items που είναι μόνο για τον admin

$languages = array (    // Οι γλώσσες που υποστηρίζονται
    array ('language' => 'Ελληνικά',
        'lang_id' => 'gr'),
    array ('language' => 'English',
        'lang_id' => 'en')
);

define('DEFAULT_LANG', $conn->getOption('default_language'));  // Η default γλώσσα της εφαρμογής

$UserGroups = array (     // Τα user groups που υπάρχουν
    array ('id' => '1',
        'group_name' => 'admin'),
    array ('id' => '2',
        'group_name' => 'user')
);

// Από εδώ τραβάει την τρέχουσα έκδοση της εφαρμογής
define ('APP_VERSION_FILE', 'http://www.apps4net.eu/dev/ParrotTunesVersion.php');

$lang = new Language();