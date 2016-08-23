<?php
/**
 * File: checkIfUserExists.php
 * Created by rocean
 * Date: 07/06/16
 * Time: 18:51
 * Ελέγχει αν ο χρήστης υπάρχει στην βάση κι επιστρέφει true or false
 */


require_once('../libraries/common.inc.php');

session_start();

if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

$conn = new RoceanDB();
if ($conn->checkIfUserExists($username))
    $jsonArray=array( 'success'=>true);
else $jsonArray=array( 'success'=>false);


echo json_encode($jsonArray);

?>