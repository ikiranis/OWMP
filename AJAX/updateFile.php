<?php
/**
 * File: updateFile.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 02/09/16
 * Time: 00:02
 * Ενημερώνει την βάση με τα νέα filepath και filename
 */

use apps4net\framework\Page;
use apps4net\framework\RoceanDB;

require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['filename']))
    $filename=$_GET['filename'];

if(isset($_GET['path']))
    $path=$_GET['path'];

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);




$update = RoceanDB::updateTableFields('files', 'id=?',
    array('path', 'filename'),
    array($path, $filename, $id));

if($update) {
    echo '<p>'.__('the_file').' '. $filename . ' '.__('changed_path').'</p>';

    $jsonArray = array('success' => true, 'id' => $id);

    trigger_error($id.'  File ' . $filename . ' change path.');

    RoceanDB::insertLog('File ' . $filename . ' change path.'); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=> false);


echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);