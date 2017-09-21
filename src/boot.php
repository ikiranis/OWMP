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

// Get Project Path
$projectPath = dirname(__DIR__) . '/';
$projectPath = str_replace($_SERVER["DOCUMENT_ROOT"], '', $projectPath);
define ('PROJECT_PATH', $projectPath);

use apps4net\framework\Session;
use apps4net\framework\MyDB;
use apps4net\framework\Options;

require_once ('autoload.php'); // Η autoload function που φορτώνει αυτόματα τα αρχεία των κλάσεων
require_once ('config.inc.php');  // Τα στοιχεία εισόδου στην βάση
require_once ('functions.php');  // Public functions
require_once ('mySQLSchema.php'); // To schema της βάσης σε array και οι αλλαγές που χρειάζονται

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

require_once('frameworkVariables.php');  // Γενικές μεταβλητές και options του Framework
require_once('appVariables.php');  // Γενικές μεταβλητές και options της εφαρμογής
