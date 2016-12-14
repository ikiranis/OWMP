<?php
/**
 * File: updatePath.php
 * Created by rocean
 * Date: 13/10/16
 * Time: 01:54
 * 
 * Προσθέτει ή ενημερώνει μια γραμμή στον πίνακα paths
 */



require_once ('../libraries/common.inc.php');

session_start();

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['file_path']))
    $file_path=ClearString($_GET['file_path']);

if(isset($_GET['kind']))
    $kind=ClearString($_GET['kind']);

if(isset($_GET['main']))
    $main=ClearString($_GET['main']);

$conn = new RoceanDB();
$conn->CreateConnection();

if ($id==0) {  // Αν το id είναι 0 τότε κάνει εισαγωγή
    $sql = 'INSERT INTO paths (file_path, kind, main) VALUES (?,?,?)';
    $SQLparams=array($file_path, $kind, $main);
}

else {   // αλλιώς κάνει update
    $sql = 'UPDATE paths SET file_path=?, kind=?, main=? WHERE id=?';
    $SQLparams=array($file_path, $kind, $main, $id);
}

$stmt = RoceanDB::$conn->prepare($sql);

if($stmt->execute($SQLparams)) {
    if($id==0) {
        $inserted_id=RoceanDB::$conn->lastInsertId();
        $jsonArray=array( 'success'=>true, 'lastInserted'=>$inserted_id, 'id'=>$id);

        RoceanDB::insertLog('Insert of new Path: '.$file_path); // Προσθήκη της κίνησης στα logs
    }
    else  {
        $jsonArray=array( 'success'=>true, 'id'=>$id);

        RoceanDB::insertLog('Path updated with id '.$id); // Προσθήκη της κίνησης στα logs
    }

}
else $jsonArray=array( 'success'=>false, 'id'=>$id);

echo json_encode($jsonArray);

$stmt->closeCursor();
$stmt = null;

?>