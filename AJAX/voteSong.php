<?php
/**
 * File: voteSong.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 25/12/16
 * Time: 17:21
 * 
 * Προσθέτει μία ψήφο για το τραγούδι
 * 
 */

use apps4net\framework\Page;
use apps4net\parrot\app\OWMP;

require_once('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(false);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


if(OWMP::voteSong($id)) {
    $jsonArray = array('success' => true, 'id' => $id);
}
else $jsonArray=array( 'success'=> false);



echo json_encode($jsonArray);