<?php
/**
 * File: sendKillCommand.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 07/11/16
 * Time: 01:14
 * Στέλνει kill command στο σχετικό πεδίο στην βάση
 */

use apps4net\framework\Page;
use apps4net\framework\Progress;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(Progress::setKillCommand('1'))
    $jsonArray = array('success' => true);
else $jsonArray = array('success' => false);

echo json_encode($jsonArray);