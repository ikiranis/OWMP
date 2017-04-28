<?php
/**
 * File: getVideoMetadata.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 20/06/16
 * Time: 19:58
 * Επιστρέφει τα metadata του αρχείου (video)
 */

use apps4net\framework\Page;
use apps4net\framework\MyDB;
use apps4net\parrot\app\OWMP;
use apps4net\parrot\app\OWMPElements;

require_once('../src/boot.php');

session_start();

// TODO να κάνω έναν worker και να κάνει update το session από εκεί
Page::updateUserSession();

Page::checkValidAjaxRequest(true);

$conn = new MyDB();


if(isset($_GET['id']))
    $id=ClearString($_GET['id']);

if(isset($_GET['onlyGiphy']))
    $onlyGiphy=ClearString($_GET['onlyGiphy']);
else $onlyGiphy=null;

if(isset($_GET['tabID']))
    $tabID=ClearString($_GET['tabID']);


$file=MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);


$filesArray=array('path'=>$file[0]['path'],
                    'filename'=>$file[0]['filename'],
                    'kind'=>$file[0]['kind']);

if($metadata=MyDB::getTableArray('music_tags','*', 'id=?', array($id),null, null, null)) {

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
        // το Album cover
        $albumCoverPath = OWMPElements::getAlbumImagePath($metadata[0]['album_artwork_id'], 'big');

        if(!$iconImagePath = OWMPElements::getAlbumImagePath($metadata[0]['album_artwork_id'], 'ico')) {
            $iconImagePath=null;
        }


        // Χρησιμοποιεί το itunes ή giphy api για να πάρει artwork όταν δεν υπάρχει artwork στο τραγούδι
        if($metadata[0]['album_artwork_id']==DEFAULT_ARTWORK_ID) {
            
            // Από itunes API
            if ($iTunesArtwork = OWMPElements::getItunesCover(htmlspecialchars_decode($metadata[0]['album']) . ' ' . htmlspecialchars_decode($metadata[0]['artist']))) {
                $fromAPI = $iTunesArtwork;
                $apiSource='iTunes';
            }
            else if ($giphy = OWMPElements::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                $fromAPI = $giphy;
                $apiSource='Giphy';
            }
        }

        // Αν έχουμε επιλέξει πάντα να εμφανίζει από giphy
        if($onlyGiphy=='true') {
            if ($giphy = OWMPElements::getGiphy(htmlspecialchars_decode($metadata[0]['song_name']))) { // Από Giphy API
                $fromAPI = $giphy;
                $albumCoverPath=null;
                $apiSource='Giphy';
            }
            else $fromAPI=null;
        }
                
    }
    else {
        $albumCoverPath=null;
        $iconImagePath=null;
    }

    $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
    $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

    // Εισάγει το συγκεκριμένο τραγούδι που παίζει στο Played Queue Playlist
    OWMPElements::insertIntoTempPlaylist($tempPlayedQueuePlaylist, $id);

    $playlistID=MyDB::getTableFieldValue($tempUserPlaylist, 'file_id=?', $id, 'id');
    
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
        'iconImagePath' => $iconImagePath,
        'fromAPI'=>$fromAPI,
        'apiSource'=>$apiSource,
        'playlist_id' => $playlistID,
        'playlist_count' => $_SESSION['$countThePlaylist']);




}
else $jsonArray = array('success' => false);


echo json_encode(array('tags'=>$jsonArray,'file'=>$filesArray), JSON_UNESCAPED_UNICODE);