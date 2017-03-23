<?php
/**
 * File: syncTheFiles.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 13/07/16
 * Time: 23:32
 * Κάνει τον συγχρονισμό των αρχείων
 */

use apps4net\framework\Page;
use apps4net\parrot\app\SyncFiles;

require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['operation']))
    $operation=ClearString($_GET['operation']);

if(isset($_GET['mediakind']))
    $mediaKind=ClearString($_GET['mediakind']);


$sync = new SyncFiles();

//$operation='sync';
//$mediaKind='Music Video';

if($operation=='sync')
    $sync->syncTheFiles($mediaKind);

if($operation=='clear')
    $sync->clearTheFiles();

if($operation=='hash')
    $sync->hashTheFiles($mediaKind);

if($operation=='metadata')
    $sync->filesMetadata();

if($operation=='json_import')
    $sync->importPlaylistToDB();

if($operation=='coverConvert')
    $sync->convertCovers();