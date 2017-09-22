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
use apps4net\framework\FileUpload;
use apps4net\parrot\app\SyncFiles;

require_once('../../src/boot.php');

// TODO να δω πως μπορεί να γίνει πιο γρήγορο το upload

session_start();
Page::checkValidAjaxRequest(true);

// Τα row data που έρχονται από javascript
$results = file_get_contents ('php://input');
$results = json_decode($results, TRUE);

if($results['uploadKind']=='slice') {
    $fileUpload = new FileUpload($results['file_data'], $results['file_type'], $results['file']);

    $fileUpload->ajaxUploadFile();
} else {
    trigger_error($results['fullPathFilename']);

    $syncFile = new SyncFiles();

// Παράγει το file path από το έτος και τον μήνα και ελέγχει το είδος του αρχείου
    if (strpos(strtolower($results['file_type']), 'video')!==false) {
        $syncFile->mediaKind = 'Music Video';
    } else {
        $syncFile->mediaKind = 'Music';
    }

    if(file_exists($results['fullPathFilename'])) {
        // Εγγραφή στην βάση του τραγουδιού που κατέβηκε ανέβηκε
        $syncFile->file = str_replace(DIR_PREFIX, '', $results['fullPathFilename']);
        $syncFile->searchIDFiles = true;
        $syncFile->name = $results['fileName'];

        $syncFile->writeTrack();

        $jsonArray = array('success' => true, 'result' => $results['fullPathFilename'],
            'filesToDelete' => $syncFile->deleteFilesString);
    } else {
        $jsonArray=array( 'success'=> false, 'fileName' => $results['file']);
    }

    echo json_encode($jsonArray);
}

