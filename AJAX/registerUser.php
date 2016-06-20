<?php
/**
 * File: registerUser.php
 * Created by rocean
 * Date: 08/06/16
 * Time: 17:35
 * Εισαγωγή αρχικού χρήστη
 */



require_once('../libraries/common.inc.php');


$conn = new RoceanDB();
$lang = new Language();

if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

if(isset($_GET['password']))
    $password=ClearString($_GET['password']);

if (isset($_GET['email']))
    $email=$_GET['email'];



$register=$conn->CreateUser($username, $email, $password, '1', 'local', null, null);

if($register['success']) {
    $jsonArray=array( 'success'=>true);
}
else {
    $jsonArray=array( 'success'=>false);
}


$dbstatus=$conn->getOption('dbstatus');

if(!$dbstatus) {  // αρχικοποίηση options
    $conn->createOption('interval_value','5',1,0);
    $conn->createOption('mail_host','smtp.gmail.com',1,0);
    $conn->createOption('mail_username','username',1,0);
    $conn->createOption('mail_password','',1,1);
    $conn->createOption('mail_from','username@mail.com',1,0);
    $conn->createOption('mail_from_name','name',1,0);
}



echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);

?>