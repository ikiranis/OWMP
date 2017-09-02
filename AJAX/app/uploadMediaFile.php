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

$results = file_get_contents ("php://input");

$myFile = $results;

//
//if(isset($results['uploadedFilename'])){
//    $uploadedFilename=ClearString($results['uploadedFilename']);
//}
//
//if(isset($results['myMime'])){
//    $myMime=ClearString($results['myMime']);
//}


// Separate out the data
$data = explode(',', $myFile);

// Encode it correctly
$encodedData = str_replace(' ','+',$data[1]);
$decodedData = base64_decode($encodedData);

// Παράγει το file path από το έτος και τον μήνα
$uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
$myFilename = $uploadedFilename;


//$fp = fopen(OUTPUT_FOLDER . 'something.mp4', 'wb');
//fwrite($fp, $decodedData);
//fclose($fp);

$file = new FilesIO(OUTPUT_FOLDER, 'something.mp4', 'write');

$file->insertRow($decodedData);