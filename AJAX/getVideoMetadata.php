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

$conn = new RoceanDB();



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);






$file=RoceanDB::getTableArray('files','*', 'id=?', array($id),null);

$filesArray=array('path'=>$file[0]['path'],
                    'filename'=>$file[0]['filename']);

if($metadata=RoceanDB::getTableArray('music_tags','*', 'id=?', array($id),null)) {

    if (isset($metadata[0]['rating'])) {
        $rating = ($metadata[0]['rating'] / 10) / 2;

    }
    else $rating='';

    if ($metadata[0]['song_year']==0)
        $song_year='';
    else $song_year=$metadata[0]['song_year'];

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
        'rating' => $rating);


}
else $jsonArray = array('success' => false);


echo json_encode(array('tags'=>$jsonArray,'file'=>$filesArray), JSON_UNESCAPED_UNICODE);