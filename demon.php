<?php
/**
 * File: demon.php
 * Created by rocean
 * Date: 27/05/16
 * Time: 19:44
 * Συνεχής έλεγχος για την κατάσταση διάφορων πραγμάτων για alerts κτλ
 * Τρέχει συνεχώς στο crontab
 */


require_once ('libraries/common.inc.php');
require_once ('libraries/SyncFiles.php');

$sync = new SyncFiles();

$sync->syncTheFiles();