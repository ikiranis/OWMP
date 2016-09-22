<?php
/**
 * File: syncTheFiles.php
 * Created by rocean
 * Date: 13/07/16
 * Time: 23:32
 * Κάνει τον συγχρονισμό των αρχείων
 */

//Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);



// Implicitly flush the buffer(s)
ini_set('output_buffering', 0);
ini_set('implicit_flush', 1);
ob_implicit_flush(true);


//Flush (send) the output buffer and turn off output buffering
//ob_end_flush();
try { while( @ob_end_flush() ); } catch( Exception $e ) {}
ob_start();


//prevent apache from buffering it for deflate/gzip
header("Content-type: text/plain");
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

require_once ('../libraries/common.inc.php');
require_once ('../libraries/SyncFiles.php');

session_start();
if(isset($_GET['operation']))
    $operation=ClearString($_GET['operation']);

if(isset($_GET['mediakind']))
    $mediaKind=ClearString($_GET['mediakind']);


$sync = new SyncFiles();


if($operation=='sync')
    $sync->syncTheFiles($mediaKind);

if($operation=='clear')
    $sync->clearTheFiles();

if($operation=='hash')
    $sync->hashTheFiles();

if($operation=='metadata')
    $sync->filesMetadata();
