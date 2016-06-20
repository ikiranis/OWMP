<?php
/**
 * File: getVideoMetadata.php
 * Created by rocean
 * Date: 20/06/16
 * Time: 19:58
 */

require_once('../libraries/getid3/getid3.php');
require_once ('../libraries/common.inc.php');


if(isset($_GET['filename']))
    $FullFileName=ClearString($_GET['filename']);




// Initialize getID3 engine
$getID3 = new getID3;


$ThisFileInfo = $getID3->analyze($FullFileName);

getid3_lib::CopyTagsToComments($ThisFileInfo);

//echo $ThisFileInfo['comments_html']['artist'][0];
//
//echo '<br><br>';
//
//print_r($ThisFileInfo);

$jsonArray=array( 'success'=>true,
    'artist'=>$ThisFileInfo['comments_html']['artist'][0],
    'title'=>$ThisFileInfo['comments_html']['title'][0]) ;

echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);