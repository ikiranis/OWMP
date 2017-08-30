<?php
/**
 *
 * File: uploadMediaFile.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 30/08/2017
 * Time: 01:35
 *
 * Ανεβάζει ένα αρχείο
 *
 */

use apps4net\framework\Page;
use apps4net\framework\FilesIO;
use apps4net\framework\Utilities;

require_once('../../src/boot.php');

session_start();
Page::checkValidAjaxRequest(true);

$myFile = $_FILES['myFile'];

if(isset($_POST['uploadedFilename'])){
    $uploadedFilename=ClearString($_POST['uploadedFilename']);
}

if(isset($_POST['myMime'])){
    $myMime=ClearString($_POST['myMime']);
}

// Παράγει το file path από το έτος και τον μήνα
//$uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
//$myFilename = $uploadedFilename;
//
//trigger_error($myFilename);

//$file = new FilesIO(OUTPUT_FOLDER, $myFilename, 'write');
//
//$file->insertRow($myFile);