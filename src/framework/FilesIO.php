<?php

/**
 *
 * File: FilesIO.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 12/03/17
 * Time: 18:58
 *
 * Κλάση που περιέχει μεθόδους για χειρισμό αρχείων
 *
 */

namespace apps4net\framework;

use apps4net\parrot\app\OWMP;

class FilesIO
{
    private $filename;  // Το filename του αρχείου με το πλήρες path
    public $handle;  // Το αρχείο που ανοίγουμε

    // Αρχικοποίηση της κλάσης. Ανοίγει ένα κενό αρχείο στην τοποθεσία $this->filename
    // @param: string $path = το path που θα ανοίξει το αρχείο
    // @param: string $filename = το όνομα του αρχείου
    // @param: string $operation = Παίρνει τιμές 'write' ή 'read'
    // @return: string $this->filename =  Το αρχείο για χειρισμό από την κλάση
    function __construct($path, $filename, $operation) {
        if($operation=='write') {
            // Ελέγχει και δημιουργεί το $path
            $createOutputFolder = OWMP::createDirectory($path);

            // Αν το directory υπάρχει δημιουργεί ένα κενό αρχείο $filename
            if ($createOutputFolder['result']) {
                file_put_contents($path . $filename, '');
                $this->filename = $path . $filename;
            } else {  // Αλλιώς σταματάει την εκτέλεση του script και εμφανίζει μήνυμα
                die($createOutputFolder['message']);
            }
        }

        // Ελέγχει αν υπάρχει το αρχείο, το ανοίγει και θέτει την τιμή στο $this->filename
        if($operation=='read') {
            $fullFilename = $path . $filename;
            if(file_exists($fullFilename)) {
                $this->handle = fopen($fullFilename, "rb");
                $this->filename = $fullFilename;
            } else {
                die('File not exist');
            }
        }

    }

    // Προσθέτει ένα string στο $this->filename
    // @param string $fileRow: Το string που θα προστεθεί
    // @return void
    public function insertRow($fileRow) {
        file_put_contents($this->filename, $fileRow, FILE_APPEND);
    }


    // Κλείνει το αρχείο που έχει ανοίξει στο $this->handle
    public function closeTheFile()
    {
        fclose($this->handle);
    }


    // μετράει τις γραμμές που έχει ένα text αρχείο
    // @return: integer $lines = το σύνολο των γραμμών
    // @source from http://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
    public function getLines()
    {
        $file = fopen($this->filename, 'rb');
        $lines = 0;

        // μετράει μία-μία τις γραμμές
        while (!feof($file)) {
            $lines += substr_count(fread($file, 8192), "\n");
        }

        fclose($file);

        return $lines;
    }

}