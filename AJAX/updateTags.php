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
if(!$UserGroup=$conn->getUserGroup($conn->getSession('username')))  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
    if($conn->CheckCookiesForLoggedUser()) {
        $UserGroup=$conn->getUserGroup($conn->getSession('username'));
    }


if ($UserGroup==1) { // Αν ο χρήστης είναι admin


    if (isset($_GET['id']))
        $id = ClearString($_GET['id']);

    if (isset($_GET['song_name']))
        $song_name = ClearString($_GET['song_name']);

    if (isset($_GET['artist']))
        $artist = ClearString($_GET['artist']);

    if (isset($_GET['genre']))
        $genre = ClearString($_GET['genre']);

    if (isset($_GET['song_year']))
        $song_year = intval($_GET['song_year']);

    if (isset($_GET['album']))
        $album = ClearString($_GET['album']);

    if (isset($_GET['rating']))
        $rating = intval($_GET['rating']);

    if (isset($_GET['live']))
        $live = intval($_GET['live']);

    $rating = ($rating * 10) * 2;


    $update = RoceanDB::updateTableFields('music_tags', 'id=?',
        array('song_name', 'artist', 'genre', 'song_year', 'album', 'rating', 'live'),
        array($song_name, $artist, $genre, $song_year, $album, $rating, $live, $id));

    if ($update) {
        $jsonArray = array('success' => true);
    } else {
        $jsonArray = array('success' => false);
    }

} else
    $jsonArray = array('success' => false);

echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);