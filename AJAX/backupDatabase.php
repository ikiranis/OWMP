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


require_once ('../libraries/common.inc.php');
require_once ('../libraries/framework/BackupDB.php');

session_start();

Page::checkValidAjaxRequest(true);


// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup
$backup = new BackupDB();
$backup->tables = array('album_arts', 'options', 'files', 'logs', 'manual_playlists',
    'music_tags', 'paths', 'salts', 'user', 'user_details');

if ($backup->backupDatabase()) {
    $jsonArray = array('success' => true);
    RoceanDB::insertLog('Backup of the database with success'); // Προσθήκη της κίνησης στα logs
} else {
    $jsonArray = array('success' => false);
}


echo json_encode($jsonArray);