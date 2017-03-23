<?php

/**
 *
 * File: Utilities.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 26/02/17
 * Time: 23:28
 *
 * Κλάση με διάφορες μεθόδους εργαλεία γενικού ενδιαφέροντος
 *
 */

namespace apps4net\framework;

class Utilities
{

    // Έλεγχος αν είναι εγκατεστημένη μια linux εφαρμογή
    static function checkIfLinuxProgramInstalled($program) {
        $output= shell_exec('which '.$program);

        if($output) {
            return true;
        } else {
            return false;
        }
    }

    static function runGitUpdate($sudoPass) {
        $crypt = new Crypto();

        $shellScript = 'cd '.$_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.' && sudo -S \''.$crypt->DecryptText($sudoPass).'\' mkdir paok';

        trigger_error($shellScript);

        $output= shell_exec($shellScript);

        trigger_error($output);
    }


    // Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη κι επιστρέφει πίνακα με τις τιμές της πρώτης στήλης που έχουν την μέγιστη τιμή
    static function getArrayMax($myArray) {
        $myMax=0;

        // Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη
        foreach ($myArray as $row) {
            if($row[1]>$myMax) {
                $myMax=$row[1];
            }
        }

        // Επιστρέφει τις τιμές της πρώτης στήλης που έχουν την μεγαλύτερη τιμή
        foreach ($myArray as $row) {
            if($row[1]==$myMax) {
                $newArray[]=$row[0];
            }
        }

        return $newArray;

    }

}