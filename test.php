<?php
/**
 * File: test.php
 * Created by rocean
 * Date: 22/09/16
 * Time: 19:29
 */

// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);

//Flush (send) the output buffer and turn off output buffering
//ob_end_flush();
while (@ob_end_flush());

// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);

//prevent apache from buffering it for deflate/gzip
header("Content-type: text/plain");
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.


for($i = 0; $i < 100000; $i++)
{
    echo ' '.$i;
}
//
ob_flush();
flush();

/// Now start the program output

echo "Program Output";

ob_flush();
flush();