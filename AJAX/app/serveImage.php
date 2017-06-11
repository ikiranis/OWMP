<?php
/**
 *
 * File: serveImage.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 12/06/2017
 * Time: 01:07
 *
 * Επιστρέφει το album cover image σε binary
 *
 */

use apps4net\parrot\app\MediaStream;

require_once('../../src/boot.php');

if(isset($_GET['imagePath']))
    $imagePath=ClearString($_GET['imagePath']);

$streamFile = new MediaStream($imagePath);
$streamFile->start();