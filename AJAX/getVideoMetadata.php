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
// @source https://github.com/JamesHeinrich/getID3/
//require_once('../libraries/getid3/getid3.php');

//
//if(isset($_GET['filename']))
//    $FullFileName=ClearString($_GET['filename']);

if(isset($_GET['id']))
    $id=ClearString($_GET['id']);




// Initialize getID3 engine
//$getID3 = new getID3;
//
//
//$ThisFileInfo = $getID3->analyze($FullFileName);
//
//getid3_lib::CopyTagsToComments($ThisFileInfo);

//echo $ThisFileInfo['comments_html']['artist'][0];
//
//echo '<br><br>';
//
//print_r($ThisFileInfo);

//$jsonArray=array( 'success'=>true,
//    'artist'=>$ThisFileInfo['comments_html']['artist'][0],
//    'title'=>$ThisFileInfo['comments_html']['title'][0]) ;

$metadata=$conn->getTableArray('music_tags','*', 'id=?', array($id),null);

$rating=($metadata[0]['rating']/10)/2;

switch ($rating) {
    case 1: $stars='*'; break;
    case 2: $stars='**'; break;
    case 3: $stars='***'; break;
    case 4: $stars='****'; break;
    case 5: $stars='*****'; break;
    
}

$jsonArray=array( 'success'=>true,
    'artist'=>$metadata[0]['artist'],
    'title'=>$metadata[0]['name'],
    'genre'=>$metadata[0]['genre'],
    'rating'=>$stars ) ;

echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);