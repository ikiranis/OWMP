<?php
/**
 * File: searchPlaylist.php
 * Created by rocean
 * Date: 04/07/16
 * Time: 01:06
 * Κάνει το search στην playlist
 */




require_once('../libraries/common.inc.php');

session_start();


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

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);


if($firstTime=='true')
    $_SESSION['PlaylistCounter']=0;


if($duplicates==false)
    OWMP::getPlaylist($jsonArray,$offset,$step,null,$mediaKind,$tabID);
else OWMP::getPlaylist($jsonArray,$offset,$step,$duplicates,$mediaKind,$tabID);

