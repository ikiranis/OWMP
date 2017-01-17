<?php
/**
 * File: createSmallAlbumCovers.php
 * Created by rocean
 * Date: 15/01/17
 * Time: 00:49
 * 
 * Δημιουργεί μικρότερες εκδόσεις και thumbnails για όλα τα covers 
 * 
 */

// TODO να γραφεί ο κώδικας

require_once('../libraries/common.inc.php');

function exception_handler($exception) {
    echo "Uncaught exception: " ;
}

set_exception_handler('exception_handler');


$myImage='/media/Spartacus/Music/album_covers/2016/09/20160918235156.jpg';


try {



    $imageString = file_get_contents($myImage);
//    $imageString = base64_decode($imageString);

    echo $imageString;

    $image = imagecreatefromstring($imageString);



    header('Content-Type: image/jpeg');
    imagejpeg($image);
    throw new Exception('Uncaught Exception');

}
catch (Exception $e) {
    echo $e->getMessage();
    
    
    
    
    
}

echo 'ante kai gamisou';