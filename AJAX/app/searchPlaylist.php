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
use apps4net\parrot\app\PlaylistSearch;


require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);

$playlist = new PlaylistSearch();


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

$playlist->fieldsArray = $jsonArray;
$playlist->offset = $offset;
$playlist->step = $step;
$playlist->duplicates = null;
$playlist->mediaKind = $mediaKind;
$playlist->tabID = $tabID;
$playlist->loadPlaylist = null;
$playlist->votePlaylist = false;

if($duplicates==false && $playedQueue==false && $loadPlaylist==false && $votePlaylist==false) {
    $playlist->getPlaylist();
}
else {
    if ($loadPlaylist == true) {
        $playlist->loadPlaylist = $loadPlaylist;
    }
    if($duplicates==true) {
        $playlist->duplicates = $duplicates;
    }
    if($votePlaylist==true) {
        $playlist->fieldsArray = null;
        $playlist->mediaKind = null;
        $playlist->tabID = null;
        $playlist->votePlaylist = $votePlaylist;
    }
//    if($playedQueue==true) {
//        $OWMPElements->getPlaylist();
//    }

    $playlist->getPlaylist();
}


