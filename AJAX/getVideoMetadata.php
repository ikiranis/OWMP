<?php
/**
 * File: getVideoMetadata.php
 * Created by rocean
 * Date: 20/06/16
 * Time: 19:58
 * Επιστρέφει τα metadata του αρχείου (video)
 */


require_once ('../libraries/common.inc.php');

session_start();

Page::checkValidAjaxRequest(true);

$conn = new RoceanDB();

// Έλεγχος αν έχει λήξει το session. Αλλιώς ψάχνει για coockie
if (!isset($_SESSION["username"])) {
    if ($conn->CheckCookiesForLoggedUser()) {
        $conn->setSession('username', RoceanDB::getACookie("username"));
    }
}

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['onlyGiphy']))
    $onlyGiphy=ClearString($_GET['onlyGiphy']);
else $onlyGiphy=null;

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);


$file=RoceanDB::getTableArray('files','*', 'id=?', array($id),null, null, null);


$filesArray=array('path'=>$file[0]['path'],
                    'filename'=>$file[0]['filename'],
                    'kind'=>$file[0]['kind']);

if($metadata=RoceanDB::getTableArray('music_tags','*', 'id=?', array($id),null, null, null)) {

    if (isset($metadata[0]['rating'])) {
        $rating = ($metadata[0]['rating'] / 10) / 2;

    }
    else $rating='';

    if ($metadata[0]['song_year']==0)
        $song_year='';
    else $song_year=$metadata[0]['song_year'];

    $fromAPI=null;
    $apiSource='';

    if($file[0]['kind']=='Music') {
        $albumCoverPath = OWMP::getAlbumImagePath($metadata[0]['album_artwork_id']);

        // Χρησιμοποιεί το itunes ή giphy api για να πάρει artwork όταν δεν υπάρχει artwork στο τραγούδι
        if($metadata[0]['album_artwork_id']==DEFAULT_ARTWORK_ID) {
            
            // Από itunes API
            if ($iTunesArtwork = OWMP::getItunesCover(htmlspecialchars_decode($metadata[0]['album']) . ' ' . htmlspecialchars_decode($metadata[0]['artist']))) {
                $fromAPI = $iTunesArtwork;
                $apiSource='iTunes';
            }
            else if ($giphy = OWMP::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                $fromAPI = $giphy;
                $apiSource='Giphy';
            }
        }

        // Αν έχουμε επιλέξει πάντα να εμφανίζει από giphy
        if($onlyGiphy=='true') {
            if ($giphy = OWMP::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                $fromAPI = $giphy;
                $albumCoverPath=null;
                $apiSource='Giphy';
            }
            else $fromAPI=null;
        }
                
    }
    else $albumCoverPath=null;

    $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
    $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

    // Εισάγει το συγκεκριμένο τραγούδι που παίζει στο Played Queue Playlist
    OWMP::insertIntoTempPlaylist($tempPlayedQueuePlaylist, $id);

    $playlistID=RoceanDB::getTableFieldValue($tempUserPlaylist, 'file_id=?', $id, 'id');
    
    $jsonArray = array('success' => true,
        'artist' => htmlspecialchars_decode($metadata[0]['artist']),
        'title' => htmlspecialchars_decode($metadata[0]['song_name']),
        'genre' => htmlspecialchars_decode($metadata[0]['genre']),
        'year' => $song_year,
        'album' => htmlspecialchars_decode($metadata[0]['album']),
        'play_count' => $metadata[0]['play_count'],
        'date_played' => $metadata[0]['date_last_played'],
        'date_added' => $metadata[0]['date_added'],
        'track_time' => $metadata[0]['track_time'],
        'live' => $metadata[0]['live'],
        'rating' => $rating,
        'albumCoverPath'=>$albumCoverPath,
        'fromAPI'=>$fromAPI,
        'apiSource'=>$apiSource,
        'playlist_id' => $playlistID,
        'playlist_count' => $_SESSION['$countThePlaylist']);




}
else $jsonArray = array('success' => false);


echo json_encode(array('tags'=>$jsonArray,'file'=>$filesArray), JSON_UNESCAPED_UNICODE);