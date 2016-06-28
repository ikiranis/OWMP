<?php
/**
 * File: getVideoMetadata.php
 * Created by rocean
 * Date: 20/06/16
 * Time: 19:58
 * Επιστρέφει τα metadata του αρχείου (video)
 */


require_once ('../libraries/common.inc.php');

$conn = new RoceanDB();



if(isset($_GET['id']))
    $id=ClearString($_GET['id']);






$file=RoceanDB::getTableArray('files','*', 'id=?', array($id),null);

$filesArray=array('path'=>$file[0]['path'],
                    'filename'=>$file[0]['filename']);

if($metadata=RoceanDB::getTableArray('music_tags','*', 'id=?', array($id),null)) {

    if ($metadata[0]['rating']) {
        $rating = ($metadata[0]['rating'] / 10) / 2;

        switch ($rating) {
            case 0:
                $stars = '';
                break;
            case 1:
                $stars = '*';
                break;
            case 2:
                $stars = '**';
                break;
            case 3:
                $stars = '***';
                break;
            case 4:
                $stars = '****';
                break;
            case 5:
                $stars = '*****';
                break;

        }
    }


    $jsonArray = array('success' => true,
        'artist' => $metadata[0]['artist'],
        'title' => $metadata[0]['name'],
        'genre' => $metadata[0]['genre'],
        'year' => $metadata[0]['year'],
        'album' => $metadata[0]['album'],
        'play_count' => $metadata[0]['play_count'],
        'date_played' => $metadata[0]['date_last_played'],
        'date_added' => $metadata[0]['date_added'],
        'track_time' => $metadata[0]['track_time'],
        'rating' => $stars);


}
else $jsonArray = array('success' => false);


echo json_encode(array('tags'=>$jsonArray,'file'=>$filesArray), JSON_UNESCAPED_UNICODE);