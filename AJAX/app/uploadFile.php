<?php
/**
 *
 * File: uploadFile.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 06/04/17
 * Time: 00:46
 *
 * Σώζει ένα αρχείο που κάναμε upload
 *
 */


use apps4net\framework\Page;
use apps4net\framework\FilesIO;

require_once('../../src/boot.php');

session_start();
Page::checkValidAjaxRequest(true);

$myFile = $_POST['myFile'];

$file = new FilesIO(OUTPUT_FOLDER, TEMP_RESTORE_DATABASE_FILE, 'write');

$file->insertRow($myFile);






