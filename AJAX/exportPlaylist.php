<?php
/**
 * File: exportPlaylist.php
 * Created by rocean
 * Date: 23/11/16
 * Time: 00:03
 *
 * Κάνει export την τρέχουσα playlist
 * Αντιγράφει τα αρχεία σε ένα directory και κάνει export την playlist σε json
 *
 */

set_time_limit(0);

require_once ('../libraries/common.inc.php');
require_once ('../libraries/SyncFiles.php');

session_start();

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);

Page::setLastMomentAlive(false);

$tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;

$playlistTable=RoceanDB::getTableArray($tempUserPlaylist, 'file_id', null, null, null, null, null);

//trigger_error(OUTPUT_FOLDER);

OWMP::createDirectory(OUTPUT_FOLDER);

SyncFiles::exportPlaylistJsonFile($tempUserPlaylist);

SyncFiles::setProgress(0);
$general_counter=0;

$totalFiles = count($playlistTable);

foreach ($playlistTable as $item) {
    Page::setLastMomentAlive(false);

    $file=RoceanDB::getTableArray('files','*', 'id=?', array($item['file_id']),null, null, null);
    $sourceFile=DIR_PREFIX.$file[0]['path'].$file[0]['filename'];
    $destFile=OUTPUT_FOLDER.$file[0]['filename'];
    
//    trigger_error('SOURCE: '.$sourceFile.' DEST: '.$destFile);


    copy($sourceFile, $destFile);

    $progressPercent = intval(($general_counter / $totalFiles) * 100);

    Page::setLastMomentAlive(true);
    
    SyncFiles::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό

    $general_counter++;



}