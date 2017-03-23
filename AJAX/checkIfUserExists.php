<?php
/**
 * File: checkIfUserExists.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 07/06/16
 * Time: 18:51
 * Ελέγχει αν ο χρήστης υπάρχει στην βάση κι επιστρέφει true or false
 */

use apps4net\framework\RoceanDB;
use apps4net\framework\Page;

require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(false);


if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

$conn = new RoceanDB();
if ($conn->checkIfUserExists($username))
    $jsonArray=array( 'success'=>true);
else $jsonArray=array( 'success'=>false);


echo json_encode($jsonArray);

