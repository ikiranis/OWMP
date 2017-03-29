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
$chozenTables = array('files', 'album_arts', 'music_tags', 'options', 'logs',
    'paths', 'manual_playlists');

// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup
$backup = new BackupDB();
$backup->tables = array_merge($chozenTables, $manualPlaylists);
$backup->sqlFilePath=OUTPUT_FOLDER;
$backup->sqlFile='backup_20170329205529.sql';

if ($backup->restoreDatabase()) {
    $jsonArray = array('success' => true);
    Logs::insertLog('Restore database from backup file with success'); // Προσθήκη της κίνησης στα logs
} else {
    $jsonArray = array('success' => false);
}

echo json_encode($jsonArray);