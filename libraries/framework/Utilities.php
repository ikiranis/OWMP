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

    static function runGitUpdate($sudoPass) {
        $crypt = new Crypto();

        $shellScript = 'cd '.$_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.' && sudo -S \''.$crypt->DecryptText($sudoPass).'\' mkdir paok';

        trigger_error($shellScript);

        $output= shell_exec($shellScript);

        trigger_error($output);
    }

}