<?php
/**
 *
 * File: backupDatabase.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 12/03/17
 * Time: 00:34
 *
 * Παίρνει backup της βάσης
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\BackupDB;
use apps4net\framework\Logs;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

// H λίστα με τις manual playlists
$manualPlaylists = MyDB::clearArray(MyDB::getTableArray('manual_playlists', 'table_name', null, null, null, null, null));

// Τα επιλεγμένα tables
$chozenTables = array('files', 'album_arts', 'music_tags', 'options', 'paths', 'manual_playlists',
    'user', 'user_details', 'salts');

// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup. Ενώνει τα 2 παραπάνω arrays
$backup = new BackupDB();
$backup->tables = array_merge($chozenTables, $manualPlaylists);

if ($backup->backupDatabase()) {
    $jsonArray = array('success' => true);
    Logs::insertLog('Backup of the database with success'); // Προσθήκη της κίνησης στα logs
} else {
    $jsonArray = array('success' => false);
}


echo json_encode($jsonArray);