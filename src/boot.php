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
Page::startBasicOptions();

require_once('frameworkVariables.php');  // Γενικές μεταβλητές και options του Framework
require_once('appVariables.php');  // Γενικές μεταβλητές και options της εφαρμογής
