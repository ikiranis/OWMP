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
use apps4net\framework\RoceanDB;
use apps4net\framework\BackupDB;

require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

// Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup. Ενώνει τα 2 παραπάνω arrays
$backup = new BackupDB();
$backup->sqlFile=OUTPUT_FOLDER.'backup_20170318212030.sql';

if ($backup->restoreDatabase()) {
    $jsonArray = array('success' => true);
    RoceanDB::insertLog('Restore database from backup file with success'); // Προσθήκη της κίνησης στα logs
} else {
    $jsonArray = array('success' => false);
}


echo json_encode($jsonArray);