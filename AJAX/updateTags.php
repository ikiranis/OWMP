<?php
/**
 * File: updateTags.php
 * Created by rocean
 * Date: 01/07/16
 * Time: 19:22
 * Ενημέρωση των tags ενός βίντεο
 *
 */



require_once('../libraries/common.inc.php');

session_start();

$conn = new RoceanDB();

// Έλεγχος αν έχει λήξει το session. Αλλιώς ψάχνει για coockie
if (!isset($_SESSION["username"])) {
    if ($conn->CheckCookiesForLoggedUser()) {
        $conn->setSession('username', $_COOKIE["username"]);
        $UserGroup = $conn->getUserGroup($conn->getSession('username'));
    }
}
else $UserGroup=$conn->getUserGroup($conn->getSession('username'));


if ($UserGroup==1) { // Αν ο χρήστης είναι admin

    $fieldsArray = array();  // το array των ονομάτων των πεδίων
    $valuesArray = array();  // το array με τις τιμές των πεδίων

    if (isset($_GET['id']))
        $id = ClearString($_GET['id']);

    if (isset($_GET['song_name']) && !$_GET['song_name']=='') {
        $song_name = ClearString($_GET['song_name']);
        $fieldsArray[]='song_name';
        $valuesArray[]=$song_name;
    }

    if (isset($_GET['artist']) && !$_GET['artist']=='') {
        $artist = ClearString($_GET['artist']);
        $fieldsArray[]='artist';
        $valuesArray[]=$artist;
    }

    if (isset($_GET['genre']) && !$_GET['genre']=='') {
        $genre = ClearString($_GET['genre']);
        $fieldsArray[]='genre';
        $valuesArray[]=$genre;
    }

    if (isset($_GET['song_year']) && !$_GET['song_year']=='') {
        $song_year = intval($_GET['song_year']);
        $fieldsArray[]='song_year';
        $valuesArray[]=$song_year;
    }

    if (isset($_GET['album']) && !$_GET['album']=='') {
        $album = ClearString($_GET['album']);
        $fieldsArray[]='album';
        $valuesArray[]=$album;
    }

    if (isset($_GET['rating']) && !$_GET['rating']=='') {
        $rating = intval($_GET['rating']);
        $rating = $rating * 20;
        $fieldsArray[]='rating';
        $valuesArray[]=$rating;
    }

    if (isset($_GET['live']) && !$_GET['live']=='') {
        $live = intval($_GET['live']);
        $fieldsArray[]='live';
        $valuesArray[]=$live;
    }

    $valuesArray[]=$id;

    $update = RoceanDB::updateTableFields('music_tags', 'id=?', $fieldsArray, $valuesArray);

    if ($update) {
        $jsonArray = array('success' => true, 'id' => $id);
        RoceanDB::insertLog('Changed tags for song id: '.$id); // Προσθήκη της κίνησης στα logs
    } else {
        $jsonArray = array('success' => false);
    }

} else
    $jsonArray = array('success' => false);

echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);