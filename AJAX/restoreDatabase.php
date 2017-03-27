<?php
/**
 *
 * File: restoreDatabase.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 18/03/17
 * Time: 23:45
 *
 */

use apps4net\framework\Page;
use apps4net\framework\Logs;
use apps4net\framework\MyDB;
use apps4net\framework\BackupDB;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

// H λίστα με τις manual playlists
$manualPlaylists = MyDB::clearArray(MyDB::getTableArray('manual_playlists', 'table_name', null, null, null, null, null));

// Τα επιλεγμένα tables
$chozenTables = array('album_arts', 'options', 'files', 'logs', 'manual_playlists',
    'music_tags', 'paths', 'salts', 'user', 'user_details');

// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup. Ενώνει τα 2 παραπάνω arrays
$backup = new BackupDB();
$backup->tables = array_merge($chozenTables, $manualPlaylists);
$backup->sqlFile=OUTPUT_FOLDER.'backup_20170327232920.sql';

if ($backup->restoreDatabase()) {
    $jsonArray = array('success' => true);
    Logs::insertLog('Restore database from backup file with success'); // Προσθήκη της κίνησης στα logs
} else {
    $jsonArray = array('success' => false);
}

echo json_encode($jsonArray);