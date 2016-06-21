<?php
/**
 * File: readItunesXML.php
 * Created by rocean
 * Date: 21/06/16
 * Time: 22:16
 * Διαβάζει την xml library του itunes
 */



require_once('libraries/common.inc.php');


// @source https://github.com/jsjohnst/php_class_lib/tree/master
require_once('libraries/PlistParser.inc');

$parser = new plistParser();
$plist = $parser->parseFile(dirname(__FILE__) . "/Library.xml");

$tracks=$plist['Tracks'];

echo '<p>'.count($tracks).'</p>';

foreach ($tracks as $track){
    echo $track['Location'].'<br>';
}


//echo'<pre>';
//print_r($tracks);
//echo'</pre>';