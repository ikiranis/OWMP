<?php
/**
 * File: updateTimePlayed.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 01/07/16
 * Time: 21:39
 * Ενημερώνει τo date_last_played και το play_count ενός βίντεο
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);


if(isset($_GET['id']))
    $id=ClearString($_GET['id']);


$date_last_played=date('Y-m-d H:i:s');
$play_count=MyDB::getTableFieldValue('music_tags', 'id=?', $id, 'play_count');

$play_count++;

$update=MyDB::updateTableFields('music_tags', 'id=?',
    array('date_last_played', 'play_count'),
    array($date_last_played, $play_count, $id));

if($update) {
    // Επιστρέφει το νέο play_count και το νέο date_last_played
    $jsonArray=array( 'success'=>true, 'play_count'=>$play_count, 'date_last_played'=>$date_last_played);
}
else {
    $jsonArray=array( 'success'=>false);
}



echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);