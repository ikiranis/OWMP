<?php
/**
 * File: updateTags.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 01/07/16
 * Time: 19:22
 * Ενημέρωση των tags ενός βίντεο
 *
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\framework\Logs;
use apps4net\parrot\app\OWMP;

require_once('../src/boot.php');

session_start();

Page::updateUserSession();

Page::checkValidAjaxRequest(true);

$conn = new MyDB();


$UserGroup = $conn->getUserGroup($conn->getSession('username'));

if ($UserGroup==1) { // Αν ο χρήστης είναι admin

    $fieldsArray = array();  // το array των ονομάτων των πεδίων
    $valuesArray = array();  // το array με τις τιμές των πεδίων

    if (isset($_POST['id']))
        $id = ClearString($_POST['id']);

    if (isset($_POST['song_name']) && !$_POST['song_name']=='') {
        $song_name = ClearString($_POST['song_name']);
        $fieldsArray[]='song_name';
        $valuesArray[]=$song_name;
    }

    if (isset($_POST['artist']) && !$_POST['artist']=='') {
        $artist = ClearString($_POST['artist']);
        $fieldsArray[]='artist';
        $valuesArray[]=$artist;
    }

    if (isset($_POST['genre']) && !$_POST['genre']=='') {
        $genre = ClearString($_POST['genre']);
        $fieldsArray[]='genre';
        $valuesArray[]=$genre;
    }

    if (isset($_POST['song_year']) && !$_POST['song_year']=='') {
        $song_year = intval($_POST['song_year']);
        $fieldsArray[]='song_year';
        $valuesArray[]=$song_year;
    }

    if (isset($_POST['album']) && !$_POST['album']=='') {
        $album = ClearString($_POST['album']);
        $fieldsArray[]='album';
        $valuesArray[]=$album;
    }

    if (isset($_POST['rating']) && !$_POST['rating']=='') {
        $rating = intval($_POST['rating']);
        $rating = $rating * 20;
        $fieldsArray[]='rating';
        $valuesArray[]=$rating;
    }

    if (isset($_POST['live']) && !$_POST['live']=='') {
        $live = intval($_POST['live']);
        $fieldsArray[]='live';
        $valuesArray[]=$live;
    }



    if (isset($_POST['coverImage']) && !$_POST['coverImage']=='') {
        $coverMime = $_POST['coverMime'];

        // Απαραίτητες μετατροπές του dataurl για να σωθεί σε αρχείο
        $coverImage = str_replace(' ','+',$_POST['coverImage']);
        $coverImage =  substr($coverImage,strpos($coverImage,",")+1);
        $coverImage = base64_decode($coverImage);

        $albumCoverID=OWMP::uploadAlbumImage($coverImage,$coverMime); // Ανεβάζει το αρχείο της εικόνας και παίρνει το album_artwork_id
        $fieldsArray[]='album_artwork_id';
        $valuesArray[]=$albumCoverID;
    }

    $valuesArray[]=$id;

    $update = MyDB::updateTableFields('music_tags', 'id=?', $fieldsArray, $valuesArray);

    if ($update) {
        $jsonArray = array('success' => true, 'id' => $id);
        Logs::insertLog('Changed tags for song id: '.$id); // Προσθήκη της κίνησης στα logs
    } else {
        $jsonArray = array('success' => false);
    }

} else
    $jsonArray = array('success' => false);

echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);