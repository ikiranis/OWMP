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
trigger_error('SOMETHING');

use apps4net\framework\Page;

require_once('../src/boot.php');

Page::checkValidAjaxRequest(true);



$myFile = $_POST['file'];

trigger_error($myFile);





