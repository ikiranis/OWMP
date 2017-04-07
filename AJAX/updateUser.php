<?php
/**
 * File: updateUser.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 03/06/16
 * Time: 18:25
 * Ενημερώνει μια εγγραφή στους users ή κάνει νέα εγγραφή
 */

use apps4net\framework\Page;
use apps4net\framework\User;
use apps4net\framework\Logs;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

if(isset($_GET['email']))
    $email=ClearString($_GET['email']);

if(isset($_GET['password']))
    $password=ClearString($_GET['password']);
else $password=null;

if(isset($_GET['usergroup']))
    $usergroup=ClearString($_GET['usergroup']);

if(isset($_GET['fname']))
    $fname=ClearString($_GET['fname']);
else $fname='';

if(isset($_GET['lname']))
    $lname=ClearString($_GET['lname']);
else $lname='';

$user = new User();
$conn->CreateConnection();

if ($id==0) {  // Αν το id είναι 0 τότε κάνει εισαγωγή

    $IsUserExist=$user->checkIfUserExists($username);  // Ελέγχει αν χρήστης υπάρχει ήδη
    
    if(!$IsUserExist){
        if($inserted_id=$user->CreateUser($username, $email, $password, $usergroup, 'local', $fname, $lname)) { // Δημιουργεί τον χρήστη
            $jsonArray = array('success' => true, 'lastInserted' => $inserted_id);
            Logs::insertLog('User '.$username.' created'); // Προσθήκη της κίνησης στα logs
        }
        else $jsonArray=array( 'success'=>false);
    }
    else $jsonArray=array( 'success'=>false, 'UserExists'=>true);
    
}

else {   // αλλιώς κάνει update
    if($user->UpdateUser($id, $username, $email, $password, $usergroup, 'local', $fname, $lname))   // Ενημερώνει την εγγραφή
        $jsonArray=array( 'success'=>true);

}

echo json_encode($jsonArray);