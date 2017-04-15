<?php
/**
 * File: updatePath.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 13/10/16
 * Time: 01:54
 * 
 * Προσθέτει ή ενημερώνει μια γραμμή στον πίνακα paths
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\Logs;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['file_path']))
    $file_path=ClearString($_GET['file_path']);

if(isset($_GET['kind']))
    $kind=ClearString($_GET['kind']);

$conn = new MyDB();
$conn->CreateConnection();

if ($id==0) {  // Αν το id είναι 0 τότε κάνει εισαγωγή
    $sql = 'INSERT INTO paths (file_path, kind) VALUES (?,?)';
    $SQLparams=array($file_path, $kind);
}

else {   // αλλιώς κάνει update
    $sql = 'UPDATE paths SET file_path=?, kind=? WHERE id=?';
    $SQLparams=array($file_path, $kind, $id);
}

$stmt = MyDB::$conn->prepare($sql);

if($stmt->execute($SQLparams)) {
    if($id==0) {
        $inserted_id=MyDB::$conn->lastInsertId();
        $jsonArray=array( 'success'=>true, 'lastInserted'=>$inserted_id, 'id'=>$id);

        Logs::insertLog('Insert of new Path: '.$file_path); // Προσθήκη της κίνησης στα logs
    }
    else  {
        $jsonArray=array( 'success'=>true, 'id'=>$id);

        Logs::insertLog('Path updated with id '.$id); // Προσθήκη της κίνησης στα logs
    }

}
else $jsonArray=array( 'success'=>false, 'id'=>$id);

echo json_encode($jsonArray);

$stmt->closeCursor();
$stmt = null;