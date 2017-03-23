<?php
/**
 * File: registerUser.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 08/06/16
 * Time: 17:35
 * 
 * Εισαγωγή αρχικού χρήστη
 * 
 */

use apps4net\framework\Page;
use apps4net\framework\RoceanDB;
use apps4net\framework\Language;

require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(false);

$conn = new RoceanDB();
$lang = new Language();

if(isset($_GET['username']))
    $username=ClearString($_GET['username']);

if(isset($_GET['password']))
    $password=ClearString($_GET['password']);

if (isset($_GET['email']))
    $email=ClearString($_GET['email']);

// Ελέγχει αν υπάρχει admin χρήστης ήδη.
if(!$conn->CheckIfThereIsAdminUser()) {
    $register = $conn->CreateUser($username, $email, $password, '1', 'local', null, null);

    if ($register['success']) {
        $jsonArray = array('success' => true);
        RoceanDB::insertLog('User ' . $username . ' registered'); // Προσθήκη της κίνησης στα logs
    } else {
        $jsonArray = array('success' => false);
    }



    // ελέγχει και εισάγει τις αρχικές τιμές στον πίνακα options
    Page::startBasicOptions();

    // Δημιουργεί event που σβήνει logs που είναι παλιότερα των 30 ημερών και τρέχει κάθε μέρα
    $eventQuery='DELETE FROM logs WHERE log_date<DATE_SUB(NOW(), INTERVAL 30 DAY)';
    RoceanDB::createMySQLEvent('logsManage', $eventQuery, '1 DAY');

    //Page::createCrontab(); // Προσθέτει τον demon στο crontab

} else {
    $jsonArray = array('success' => false);
}


echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);