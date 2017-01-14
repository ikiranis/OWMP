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

require_once('libraries/common.inc.php');

$id=252725;

$file=RoceanDB::getTableArray('music_tags','*', 'id=?', array($id),null, null, null);

$myImage = OWMP::getAlbumImagePath($file[0]['album_artwork_id']);


// GD install http://php.net/manual/en/image.installation.php
if(function_exists('gd_info')) {
    trigger_error('GD EXIST');
} else {
    trigger_error('GD NOT EXIST');
}

OWMP::createSmallerImage(ALBUM_COVERS_DIR.$myImage,'thumb');