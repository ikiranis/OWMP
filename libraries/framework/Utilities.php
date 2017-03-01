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

}