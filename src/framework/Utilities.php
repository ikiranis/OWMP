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

    // Επιστρέφει τον browser του χρήστη
    static function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }


    // Επιστρέφει την μετατροπή των δευτερολέπτων σε λεπτά:δευτερόλεπτα
    static function seconds2MinutesAndSeconds($timeInSeconds) {
        $timeInMinutes=(int)($timeInSeconds/60);
        $newTimeInSeconds=(int)($timeInSeconds%60);

        if($timeInMinutes<10) $timeInMinutes='0'.$timeInMinutes;
        if($newTimeInSeconds<10) $newTimeInSeconds='0'.$newTimeInSeconds;

        $timeArray= $timeInMinutes.' : '.$newTimeInSeconds;

        return $timeArray;
    }


    //year    = $diff->format('%y');
    //month    = $diff->format('%m');
    //day      = $diff->format('%d');
    //hour     = $diff->format('%h');
    //min      = $diff->format('%i');
    //sec      = $diff->format('%s');
    // Επιστρέφει την διαφορά της $endDate με την $startDate και επιστρέφει τιμή αναλόγως το $returnedFormat
    static function dateDifference($startDate, $endDate, $returnedFormat) {
        $d_start    = new \DateTime($startDate);
        $d_end      = new \DateTime($endDate); // Τα παίρνουμε σε αντικείμενα
        $diff = $d_start->diff($d_end);   // Υπολογίζουμε την διαφορά

        $difference      = $diff->format($returnedFormat);    // στο format βάζουμε αναλόγως σε τι θέλουμε να πάρουμε την διαφορά

        return $difference;
    }


    // Κόβει το $cut_string που βρίσκεται στο τέλος του $main_string
    static function cutLastString($main_string, $cut_string) {
        $result=substr($main_string,0,-strlen($cut_string));

        return $result;
    }

    // Αφαίρει τα διπλά slashes(/) από ένα url
    // @param: string $url = Το url που θα μετατραπεί
    // @return: string $url = Επιστρέφει το url μετά την μετατροπή
    static function removeURLDoubleSlashes($url) {
        return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    }

}