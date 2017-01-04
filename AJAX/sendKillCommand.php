<?php
/**
 * File: sendKillCommand.php
 * Created by rocean
 * Date: 07/11/16
 * Time: 01:14
 * Στέλνει kill command στο σχετικό πεδίο στην βάση
 */

require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

if(Page::setKillCommand('1'))
    $jsonArray = array('success' => true);
else $jsonArray = array('success' => false);

echo json_encode($jsonArray);