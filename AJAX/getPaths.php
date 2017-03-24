<?php
/**
 * File: getPaths.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 15/12/16
 * Time: 00:45
 * 
 * Επιστρέφει τα directories που βρίσκονται μέσα σε ένα path
 * 
 */

use apps4net\framework\Page;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

if(isset($_GET['path']))
    $path=ClearString($_GET['path']);

$paths=scandir($path);


$onlyDirectories = array();

if($paths) {
    foreach ($paths as $item) {
        if (is_dir($path . $item)) {
            $onlyDirectories[] = $item;
        }
    }
}

echo json_encode($onlyDirectories, JSON_UNESCAPED_UNICODE);