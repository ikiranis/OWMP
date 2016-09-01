<?php
/**
 * File: updateFile.php
 * Created by rocean
 * Date: 02/09/16
 * Time: 00:02
 * Ενημερώνει την βάση με τα νέα filepath και filename
 */

require_once('../libraries/common.inc.php');

session_start();

if(isset($_GET['filename']))
    $filename=ClearString($_GET['filename']);

if(isset($_GET['path']))
    $path=ClearString($_GET['path']);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);




$update = RoceanDB::updateTableFields('files', 'id=?',
    array('path', 'filename'),
    array($path, $filename, $id));

if($update) {
    echo '<p>Το αρχείο ' . $filename . ' άλλαξε θέση</p>';

    $jsonArray = array('success' => true, 'id' => $id);

    RoceanDB::insertLog('File ' . $filename . ' change path.'); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=> false);


echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);


?>