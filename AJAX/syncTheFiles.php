<?php
/**
 * File: syncTheFiles.php
 * Created by rocean
 * Date: 13/07/16
 * Time: 23:32
 * Κάνει τον συγχρονισμό των αρχείων
 */



require_once ('../libraries/common.inc.php');
require_once ('../libraries/SyncFiles.php');

session_start();

if(isset($_GET['operation']))
    $operation=ClearString($_GET['operation']);


$sync = new SyncFiles();

if($operation=='sync')
    $sync->syncTheFiles();

if($operation=='clear')
    $sync->clearTheFiles();

if($operation=='hash')
    $sync->hashTheFiles();

if($operation=='metadata')
    $sync->filesMetadata();