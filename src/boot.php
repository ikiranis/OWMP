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
use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\Options;

require_once ('autoload.php'); // Η autoload function που φορτώνει αυτόματα τα αρχεία των κλάσεων
require_once ('config.inc.php');  // Τα στοιχεία εισόδου στην βάση
require_once ('functions.php');  // Public functions

// Αρχικοποίηση του Session class
ini_set('session.gc_maxlifetime',60);
ini_set('session.gc_divisor',100);
ini_set('session.gc_probability',100);
$handler = new Session();
session_set_save_handler($handler, true);

require_once('mySQLSchema.php'); // To schema της βάσης σε array

// Ελέγχει και εισάγει τις αρχικές τιμές στον πίνακα options
//Page::startBasicOptions();

$conn = new MyDB();
$options = new Options();

// Τα default options της εφαρμογής που θα καταχωρηθούν στην βάση, αν δεν υπάρχουν
$options->defaultOptions = array(
    array('option_name' => 'convert_alac_files', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'playlist_limit', 'option_value' => '150', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'dir_prefix', 'option_value' => '/', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'syncItunes', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'date_format', 'option_value' => 'Y-m-d', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_server', 'option_value' => '0.0.0.0:8000', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_mount', 'option_value' => 'listen', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_user', 'option_value' => 'user', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_pass', 'option_value' => 'pass', 'setting' => 1, 'encrypt' => 1),
    array('option_name' => 'icecast_enable', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'default_language', 'option_value' => 'en', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'youtube_api', 'option_value' => 'AIzaSyArMqCdw1Ih1592YL96a2Vdo5sGo6vsS4A', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'play_percentage', 'option_value' => '20', 'setting' => 1, 'encrypt' => 0)
);

//$options->startBasicOptions();

// Ο πίνακας με τα options
$optionsArray = $options->getOptionsArray();


require_once('frameworkVariables.php');  // Γενικές μεταβλητές και options του Framework
require_once('appVariables.php');  // Γενικές μεταβλητές και options της εφαρμογής
