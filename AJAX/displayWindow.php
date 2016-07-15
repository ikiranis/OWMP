<?php
/**
 * File: displayWindow.php
 * Created by rocean
 * Date: 24/06/16
 * Time: 22:36
 * Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου
 */


require_once('../libraries/common.inc.php');

session_start();


if(isset($_GET['page']))
    $page=ClearString($_GET['page']);

if(isset($_GET['offset']))
    $offset=ClearString($_GET['offset']);
else $offset=0;

if(isset($_GET['step']))
    $step=ClearString($_GET['step']);
else $step=PLAYLIST_LIMIT;

if(isset($_GET['search_text']))
    $search_text=ClearString($_GET['search_text']);

switch ($page) {
    case 1: OWMP::showPlaylistWindow($offset,$step,null); break;
    case 2: OWMP::showConfiguration(); break;
    case 3: OWMP::showSynchronization(); break;
}

