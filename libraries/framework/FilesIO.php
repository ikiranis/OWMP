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

    // Αρχικοποίηση της κλάσης. Ανοίγει ένα κενό αρχείο στην τοποθεσία $this->filename
    // @param: $path = το path που θα ανοίξει το αρχείο
    // @param: $filename = το όνομα του αρχείου
    function __construct($path, $filename) {
        // Ελέγχει και δημιουργεί το $path
        $createOutputFolder = OWMP::createDirectory($path);

        // Αν το directory υπάρχει δημιουργεί ένα κενό αρχείο $filename
        if($createOutputFolder['result']) {
            file_put_contents($path.$filename, '');
            $this->filename = $path.$filename;
        } else {  // Αλλιώς σταματάει την εκτέλεση του script και εμφανίζει μήνυμα
            die($createOutputFolder['message']);
        }
    }

    // Προσθέτει ένα string στο $this->filename
    public function insertRow($fileRow) {
        file_put_contents($this->filename, $fileRow, FILE_APPEND);
    }

}