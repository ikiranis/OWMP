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
use apps4net\framework\Progress;
use apps4net\framework\BackupDB;

require_once('../src/boot.php');

session_start();


if(file_exists(OUTPUT_FOLDER . TEMP_RESTORE_DATABASE_FILE)) {

    Page::checkValidAjaxRequest(true);

// Τα επιλεγμένα tables
    $chozenTables = array('manual_playlists', 'salts', 'user_details', 'user',
        'music_tags', 'album_arts', 'files', 'options', 'paths');

// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup
    $backup = new BackupDB();
    $backup->tables = $chozenTables;

    $backup->sqlFilePath = OUTPUT_FOLDER;
    $backup->sqlFile = TEMP_RESTORE_DATABASE_FILE;

    Progress::updateRestoreRunning('1');

    if ($backup->restoreDatabase()) {
        $jsonArray = array('success' => true);
        Progress::updateRestoreRunning('0');
        unlink(OUTPUT_FOLDER . TEMP_RESTORE_DATABASE_FILE);  // Σβήνει το προσωρινό αρχείο
        Logs::insertLog('Restore database from backup file with success'); // Προσθήκη της κίνησης στα logs
    } else {
        $jsonArray = array('success' => false);
    }

} else {
    $jsonArray = array('success' => false);
    trigger_error('DEN YPARXEI ARXEIO');
}

echo json_encode($jsonArray);

