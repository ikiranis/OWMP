<?php
/**
 * File: getPaths.php
 * Created by rocean
 * Date: 15/12/16
 * Time: 00:45
 * 
 * Επιστρέφει τα directories που βρίσκονται μέσα σε ένα path
 * 
 */


require_once ('../libraries/common.inc.php');

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