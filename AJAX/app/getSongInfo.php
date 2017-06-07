<?php
/**
 * File: getSongInfo.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eun
 * Date: 30/12/16
 * Time: 00:01
 * 
 * Επιστρέφει τα στοιχεία του τραγουδιού που παίζει αυτή την στιγμή
 * 
 */

use apps4net\framework\Page;
use apps4net\parrot\app\OWMPElements;

require_once('../../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);

if($currentSong = OWMPElements::getSongInfo(null)) { // Τα στοιχεία του τραγουδιού
    $jsonArray = array('success' => true,
        'songName' => $currentSong[0]['song_name'],
        'artist' => $currentSong[0]['artist'], 
        'fileID' => $currentSong[0]['id']);
} else {
    $jsonArray = array('success' => false);
}

echo json_encode($jsonArray);