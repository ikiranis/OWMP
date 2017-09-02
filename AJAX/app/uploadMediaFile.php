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

ini_set('memory_limit','1024M');

// Τα row data που έρχονται από javascript
$results = file_get_contents ("php://input");

// Separate out the data
$results = explode(',', $results); // Σπάει σε array όταν βρει (,)
$uploadedFilename = urldecode($results[2]); // Το όνομα του αρχείου
$myMime = $results[3]; // Ο τύπος του αρχείου

//trigger_error($uploadedFilename. ' ' . $myMime.' '.$results[0]);

// Encode it correctly
$encodedData = str_replace(' ','+',$results[1]);
$myFile = base64_decode($encodedData);

// Παράγει το file path από το έτος και τον μήνα
$uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();

// Σώσιμο του αρχείου
$file = new FilesIO(OUTPUT_FOLDER, $uploadedFilename, 'write');
$file->insertRow($myFile);