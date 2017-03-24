<?php
/**
 *
 * File: autoload.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 21/03/17
 * Time: 18:14
 *
 * Φορτώνει αυτόματα τα αρχεία των κλάσεων, αναλόγως το namespace που βρίσκονται
 *
 */


// Φορτώνει αυτόματα τα αρχεία των κλάσεων, αναλόγως το namespace που βρίσκονται
// @source https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
// @param string $class The fully-qualified class name.
// @return void
spl_autoload_register(function ($class) {

    $prefixes = array (
        array('prefix' => 'apps4net\\framework\\',
            'base_dir' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'src/framework/'),
        array('prefix' => 'apps4net\\parrot\\app\\',
            'base_dir' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'src/app/')
    );

    foreach ($prefixes as $prefix) {
        // does the class use the namespace prefix?
        $len = strlen($prefix['prefix']);
//        if (strncmp($prefix['prefix'], $class, $len) !== 0) {
//            // no, move to the next registered autoloader
//            return;
//        }

        // get the relative class name
        $relative_class = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $prefix['base_dir'] . str_replace('\\', '/', $relative_class) . '.php';

        // if the file exists, require it
        if (file_exists($file)) {
//            trigger_error($file);
            require ($file);
        }
    }


});

//spl_autoload_register(function ($class) {
//
//    // project-specific namespace prefix
//    $prefix = 'apps4net\\framework\\';
//
//    // base directory for the namespace prefix
//    $base_dir = $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'src/framework/';
//
//    // does the class use the namespace prefix?
//    $len = strlen($prefix);
//    if (strncmp($prefix, $class, $len) !== 0) {
//        // no, move to the next registered autoloader
//        return;
//    }
//
//    // get the relative class name
//    $relative_class = substr($class, $len);
//
//    // replace the namespace prefix with the base directory, replace namespace
//    // separators with directory separators in the relative class name, append
//    // with .php
//    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
//
//    // if the file exists, require it
//    if (file_exists($file)) {
//        require $file;
//    }
//});