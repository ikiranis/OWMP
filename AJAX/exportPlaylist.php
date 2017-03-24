<?php
/**
 * File: exportPlaylist.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 23/11/16
 * Time: 00:03
 *
 * Κάνει export την τρέχουσα playlist
 * Αντιγράφει τα αρχεία σε ένα directory και κάνει export την playlist σε json
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\parrot\app\OWMP;
use apps4net\parrot\app\SyncFiles;

set_time_limit(0);

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);



if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

Page::setLastMomentAlive(false);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;

$playlistTable=MyDB::getTableArray($tempUserPlaylist, 'file_id', null, null, null, null, null);

//trigger_error(OUTPUT_FOLDER);

$checkOutputFolder = OWMP::createDirectory(OUTPUT_FOLDER);
if(!$checkOutputFolder['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
    exit($checkOutputFolder['message']);
}

SyncFiles::exportPlaylistJsonFile($tempUserPlaylist);

SyncFiles::setProgress(0);
$general_counter=0;

$totalFiles = count($playlistTable);

foreach ($playlistTable as $item) {
    Page::setLastMomentAlive(false);

    $file=MyDB::getTableArray('files','*', 'id=?', array($item['file_id']),null, null, null);
    $sourceFile=DIR_PREFIX.$file[0]['path'].$file[0]['filename'];
    $destFile=OUTPUT_FOLDER.$file[0]['filename'];
    
//    trigger_error('SOURCE: '.$sourceFile.' DEST: '.$destFile);


    copy($sourceFile, $destFile);

    $progressPercent = intval(($general_counter / $totalFiles) * 100);

    Page::setLastMomentAlive(true);
    
    SyncFiles::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

    $general_counter++;



}