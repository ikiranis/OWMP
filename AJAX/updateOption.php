<?php
/**
 * File: updateOption.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 10/06/16
 * Time: 01:23
 * Ενημερώνει μία εγγραφή στο options
 */

use apps4net\framework\Page;
use apps4net\framework\Options;
use apps4net\framework\Logs;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['option_name']))
    $option_name=ClearString($_GET['option_name']);

if(isset($_GET['option_value']))
    $option_value=ClearString($_GET['option_value']);


$options = new Options();

if($options->changeOption($option_name, $option_value)) {
    $jsonArray=array( 'success'=>'true');
    Logs::insertLog('Option '.$option_name.'changed'); // Προσθήκη της κίνησης στα logs
}
else $jsonArray=array( 'success'=>'false');

echo json_encode($jsonArray);