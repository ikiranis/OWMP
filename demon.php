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

$files=scanDir::scan($dirs, 'mp4', true);


echo '<p>Σύνολο αρχείων: '.count($files).'</p>';

$sql = 'INSERT INTO files(dir_path) VALUES(?)';  // Εισάγει στον πίνακα user_details

foreach ($files as $key => $value){

    if (strpos($value,'._')==false) {
        $filesArray = array($value);

        if($conn->ExecuteSQL($sql, $filesArray)) echo ' ok ';
        else echo ' not ok ';
    }


}

?>