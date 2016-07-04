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

if(isset($_GET['search_text']))
    $search_text=ClearString($_GET['search_text']);

if(isset($_GET['search_genre']))
    $search_genre=ClearString($_GET['search_genre']);

if(isset($_GET['offset']))
    $offset=ClearString($_GET['offset']);
else $offset=0;

if(isset($_GET['step']))
    $step=ClearString($_GET['step']);
else $step=1000;


if(isset($_GET['firstTime']))
    $firstTime=ClearString($_GET['firstTime']);


if($firstTime=='true')
    $_SESSION['PlaylistCounter']=0;

OWMP::getPlaylist($search_text,$search_text,$search_genre,$offset,$step);

