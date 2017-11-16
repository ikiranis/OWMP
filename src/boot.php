<?php
/**
 * File: boot.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 04/05/16
 * Time: 22:36
 * 
 * Το βασικό αρχείο με όλα τα settings και εκιννήσεις της εφαρμογής
 *
 */

use apps4net\framework\Session;
use apps4net\framework\MyDB;
use apps4net\framework\Options;

// TODO να δω γιατί έχει πρόβλημα σε no apache εγκατάσταση
// Project Path
define ('PROJECT_PATH', str_replace($_SERVER["DOCUMENT_ROOT"], '', dirname(__DIR__).DIRECTORY_SEPARATOR));

// Τα paths για το autoload
$autoloadPrefixes = array (
    array('prefix' => 'apps4net\\framework\\',
        'base_dir' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'src/framework/'),
    array('prefix' => 'apps4net\\parrot\\app\\',
        'base_dir' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'src/app/')
);

require_once('config/autoload.php'); // Η autoload function που φορτώνει αυτόματα τα αρχεία των κλάσεων
require_once('config/config.inc.php');  // Τα στοιχεία εισόδου στην βάση
require_once('framework/functions.php');  // Public functions
require_once('config/mySQLSchema.php'); // To schema της βάσης σε array και οι αλλαγές που χρειάζονται

//TODO να δω πως να μην φορτώνουν αυτά τα δυο πάντα αφού δεν χρειάζονται
// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once('external/PlistParser.php');
// @source https://github.com/JamesHeinrich/getID3/
require_once('external/getid3/getid3.php');

// Αρχικοποίηση του Session class
ini_set('session.gc_maxlifetime',60);
ini_set('session.gc_divisor',100);
ini_set('session.gc_probability',100);
$handler = new Session();
session_set_save_handler($handler, true);

$conn = new MyDB();
$options = new Options();

// Τα default options της εφαρμογής που θα καταχωρηθούν στην βάση, αν δεν υπάρχουν
$options->defaultOptions = $defaultOptions;

// Τα default progress fields
$options->defaultProgress = $defaultProgress;

// Τα default path names
$options->defaultDownloadPaths = $defaultPathNames;

// Έλεγχος των progress fields και δημιουργία τους όταν δεν υπάρχουν
$options->checkProgressFields();

// TODO Να κάνω τους ελέγχους σε όλα τα directory αν είναι valid και writeable
// Έλεγχος των path names αν υπάρχουν στο table download_paths και επιστροφή των paths σε array
$downloadPaths = $options->getDownloadPaths();

// Ο πίνακας με τα options
$optionsArray = $options->getOptionsArray();

require_once('config/frameworkVariables.php');  // Γενικές μεταβλητές και options του Framework
require_once('config/appVariables.php');  // Γενικές μεταβλητές και options της εφαρμογής
