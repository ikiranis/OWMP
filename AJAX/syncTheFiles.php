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

$sync = new SyncFiles();

$sync->syncTheFiles();