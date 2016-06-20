<?php
/**
 * File: demon.php
 * Created by rocean
 * Date: 27/05/16
 * Time: 19:44
 * Συνεχής έλεγχος για την κατάσταση διάφορων πραγμάτων για alerts κτλ
 * Τρέχει συνεχώς στο crontab
 * PHP Mailer https://github.com/PHPMailer/PHPMailer
 */



require_once('libraries/common.inc.php');

$conn = new RoceanDB();

$dirs    = array ('/media/Dalek/Videoclips', '/media/Therion/videoclips');

$extensions = array('mp4','m4v');

$files=scanDir::scan($dirs, $extensions, true);   // παίρνει το σύνολο των αρχείων με $extensions από τους φάκελους $dirs


echo '<p>Σύνολο αρχείων: '.count($files).'</p>';

$sql = 'INSERT INTO files(dir_path) VALUES(?)';  // Εισάγει στον πίνακα user_details
$counter=1;
foreach ($files as $value){

    if (strpos($value,'._')==false) {   // αν το αρχείο δεν περιέχει '._'
        $filesArray = array($value);

        if($conn->ExecuteSQL($sql, $filesArray)) echo ' ok ';
        else echo ' not ok ';

        $counter++;
        
    }


}

echo '<p>Συνολο αρχείων '.$counter.'</p>';

?>