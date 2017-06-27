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
 * Κάνει χρήση της κλάσης MediaStream
 *
 */

use apps4net\framework\MyDB;
use apps4net\parrot\app\MediaStream;

require_once('../../src/boot.php');

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['path']))
    $path=ClearString($_GET['path']);

if(isset($id)) {
    $file=MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);
    $fullPathFilename = DIR_PREFIX.$file[0]['path'].$file[0]['filename'];
} else {
    if(isset($path)) {
        $fullPathFilename = $path;
    }
}

$streamFile = new MediaStream($fullPathFilename);
$streamFile->start();

