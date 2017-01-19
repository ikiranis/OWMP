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


$myImage='/media/Spartacus/Music/album_covers/2017/01/20170118235555.jpeg';

OWMP::createSmallerImage($myImage, 'ico');





