<?php
/**
 *
 * File: updateDownloadPath.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 15/04/17
 * Time: 18:21
 *
 * Ενημερώνει το download path στο table download_paths
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\Logs;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['pathName'])) {
    $pathName = ClearString($_GET['pathName']);
}

if(isset($_GET['filePath'])) {
    $filePath = ClearString($_GET['filePath']);
}

$conn = new MyDB();
MyDB::createConnection();

$sql = 'UPDATE download_paths SET file_path=? WHERE path_name=?';
$SQLparams=array($filePath, $pathName);

$stmt = MyDB::$conn->prepare($sql);

if($stmt->execute($SQLparams)) {
        $jsonArray=array( 'success'=>true, 'pathName'=>$pathName);

        Logs::insertLog('Download path updated with name '.$pathName); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=>false, 'pathName'=>$pathName);

echo json_encode($jsonArray);

$stmt->closeCursor();
$stmt = null;