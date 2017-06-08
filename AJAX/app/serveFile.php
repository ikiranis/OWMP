<?php
/**
 *
 * File: serveFile.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 08/06/2017
 * Time: 07:28
 *
 * Επιστρέφει ένα αρχείο σε binary μορφή
 *
 */

use apps4net\framework\MyDB;

require_once('../../src/boot.php');

//session_start();

//Page::checkValidAjaxRequest(true);


if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

$file=MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);

$fullPathFilename = DIR_PREFIX.$file[0]['path'].$file[0]['filename'];

//trigger_error($fullPathFilename);

$fileType = mime_content_type($fullPathFilename);

//trigger_error($fileType);

$myFile = fopen($fullPathFilename, 'rb');

// send the right headers
header("Content-Type: " . $fileType);
header("Content-Length: " . filesize($fullPathFilename));

fpassthru($myFile);
exit;

