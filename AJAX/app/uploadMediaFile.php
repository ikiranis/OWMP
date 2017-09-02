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
use apps4net\parrot\app\SyncFiles;

require_once('../../src/boot.php');

session_start();
Page::checkValidAjaxRequest(true);

ini_set('memory_limit','1024M');

// Τα row data που έρχονται από javascript
$results = file_get_contents ("php://input");

// Separate out the data
$results = explode(',', $results); // Σπάει σε array όταν βρει (,)
$uploadedFilename = urldecode($results[2]); // Το όνομα του αρχείου
$fileType = $results[3]; // Ο τύπος του αρχείου

//trigger_error($uploadedFilename. ' ' . $myMime.' '.$results[0]);

// Encode it correctly
$encodedData = str_replace(' ','+',$results[1]);
$theFile = base64_decode($encodedData);

$syncFile = new SyncFiles();

// Παράγει το file path από το έτος και τον μήνα και ελέγχει το είδος του αρχείου
if (strpos(strtolower($fileType), 'video')!==false) {
    $syncFile->mediaKind = 'Music Video';
    $uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
} else {
    $syncFile->mediaKind = 'Music';
    $uploadDir = MUSIC_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
}

// Σώσιμο του αρχείου
$file = new FilesIO($uploadDir, $uploadedFilename, 'write');
$file->insertRow($theFile);


if(file_exists($uploadDir.$uploadedFilename)) {
    // Εγγραφή στην βάση του τραγουδιού που κατέβηκε ανέβηκε
    $syncFile->file = str_replace(DIR_PREFIX, '', $uploadDir.$uploadedFilename);
    $syncFile->searchIDFiles = true;
    $syncFile->name = $uploadedFilename;

    $syncFile->writeTrack();

    $jsonArray = array('success' => true, 'result' => $uploadDir.$uploadedFilename,
        'filesToDelete' => $syncFile->deleteFilesString);
} else {
    $jsonArray=array( 'success'=> false);
}

echo json_encode($jsonArray);