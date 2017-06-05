<?php
/**
 * File: searchPlaylist.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 04/07/16
 * Time: 01:06
 * Κάνει το search στην playlist
 */

use apps4net\framework\Page;
use apps4net\parrot\app\OWMPElements;


require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);

$OWMPElements = new OWMPElements();


if(isset($_GET['offset']))
    $offset=ClearString($_GET['offset']);
else $offset=0;

if(isset($_GET['step']))
    $step=ClearString($_GET['step']);
else $step=PLAYLIST_LIMIT;

if(isset($_GET['jsonArray']))  // Παίρνει τα δεδομένα σε πίνακα από JSON
    $jsonArray=json_decode($_GET['jsonArray'],true);
else $jsonArray=null;

if(isset($_GET['mediaKind']))
    if(!$_GET['mediaKind']=='')
        $mediaKind = ClearString($_GET['mediaKind']);
    else $mediaKind=null;
else $mediaKind=null;


if(isset($_GET['firstTime']))
    $firstTime=ClearString($_GET['firstTime']);

if(isset($_GET['duplicates']))
    $duplicates=true;
else $duplicates=false;

if(isset($_GET['queue']))
    $playedQueue=true;
else $playedQueue=false;

if(isset($_GET['loadPlaylist']))
    $loadPlaylist=true;
else $loadPlaylist=null;

if(isset($_GET['votePlaylist']))
    $votePlaylist=true;
else $votePlaylist=null;


if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);
else $tabID = null;


if($firstTime=='true')
    $_SESSION['PlaylistCounter']=0;

$OWMPElements->fieldsArray = $jsonArray;
$OWMPElements->offset = $offset;
$OWMPElements->step = $step;
$OWMPElements->duplicates = null;
$OWMPElements->mediaKind = $mediaKind;
$OWMPElements->tabID = $tabID;
$OWMPElements->loadPlaylist = null;
$OWMPElements->votePlaylist = false;

if($duplicates==false && $playedQueue==false && $loadPlaylist==false && $votePlaylist==false) {
    $OWMPElements->getPlaylist();
}
else {
    if ($loadPlaylist == true) {
        $OWMPElements->loadPlaylist = $loadPlaylist;
    }
    if($duplicates==true) {
        $OWMPElements->duplicates = $duplicates;
    }
    if($votePlaylist==true) {
        $OWMPElements->fieldsArray = null;
        $OWMPElements->mediaKind = null;
        $OWMPElements->tabID = null;
        $OWMPElements->votePlaylist = $votePlaylist;
    }
//    if($playedQueue==true) {
//        $OWMPElements->getPlaylist();
//    }

    $OWMPElements->getPlaylist();
}


