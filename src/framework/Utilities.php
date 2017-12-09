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

    /**
     * Έλεγχος αν είναι εγκατεστημένη μια linux εφαρμογή
     *
     * @param $program
     * @return bool
     */
    static function checkIfLinuxProgramInstalled($program)
    {
        $output = shell_exec('which ' . $program);

        if ($output) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $sudoPass
     */
    static function runGitUpdate($sudoPass)
    {
        $crypt = new Crypto();

        $shellScript = 'cd ' . $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . ' && sudo -S \'' . $crypt->DecryptText($sudoPass) . '\' mkdir paok';

        trigger_error($shellScript);

        $output = shell_exec($shellScript);

        trigger_error($output);
    }

    /**
     * Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη κι επιστρέφει πίνακα με τις τιμές
     * της πρώτης στήλης που έχουν την μέγιστη τιμή
     *
     * @param $myArray
     * @return array
     */
    static function getArrayMax($myArray)
    {
        $myMax = 0;

        // Βρίσκει την μεγαλύτερη τιμή στην δεύτερη στήλη
        foreach ($myArray as $row) {
            if ($row[1] > $myMax) {
                $myMax = $row[1];
            }
        }

        // Επιστρέφει τις τιμές της πρώτης στήλης που έχουν την μεγαλύτερη τιμή
        foreach ($myArray as $row) {
            if ($row[1] == $myMax) {
                $newArray[] = $row[0];
            }
        }

        return $newArray;

    }

    /**
     * Επιστρέφει τον browser του χρήστη
     *
     * @return array
     */
    static function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
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
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /**
     * Επιστρέφει την μετατροπή των δευτερολέπτων σε λεπτά:δευτερόλεπτα
     *
     * @param $timeInSeconds
     * @return string
     */
    static function seconds2MinutesAndSeconds($timeInSeconds)
    {
        $timeInMinutes = (int)($timeInSeconds / 60);
        $newTimeInSeconds = (int)($timeInSeconds % 60);

        if ($timeInMinutes < 10) $timeInMinutes = '0' . $timeInMinutes;
        if ($newTimeInSeconds < 10) $newTimeInSeconds = '0' . $newTimeInSeconds;

        $timeArray = $timeInMinutes . ' : ' . $newTimeInSeconds;

        return $timeArray;
    }


    //year    = $diff->format('%y');
    //month    = $diff->format('%m');
    //day      = $diff->format('%d');
    //hour     = $diff->format('%h');
    //min      = $diff->format('%i');
    //sec      = $diff->format('%s');
    /**
     * Επιστρέφει την διαφορά της $endDate με την $startDate και επιστρέφει τιμή αναλόγως το $returnedFormat
     *
     * @param $startDate
     * @param $endDate
     * @param $returnedFormat
     * @return string
     */
    static function dateDifference($startDate, $endDate, $returnedFormat)
    {
        $d_start = new \DateTime($startDate);
        $d_end = new \DateTime($endDate); // Τα παίρνουμε σε αντικείμενα
        $diff = $d_start->diff($d_end);   // Υπολογίζουμε την διαφορά

        $difference = $diff->format($returnedFormat);    // στο format βάζουμε αναλόγως σε τι θέλουμε να πάρουμε την διαφορά

        return $difference;
    }

    /**
     * Κόβει το $cut_string που βρίσκεται στο τέλος του $main_string
     *
     * @param $main_string
     * @param $cut_string
     * @return bool|string
     */
    static function cutLastString($main_string, $cut_string)
    {
        $result = substr($main_string, 0, -strlen($cut_string));

        return $result;
    }

    // Αφαίρει τα διπλά slashes(/) από ένα url
    // @param: string $url = Το url που θα μετατραπεί
    // @return: string $url = Επιστρέφει το url μετά την μετατροπή
    static function removeURLDoubleSlashes($url)
    {
        return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    }

    /**
     * Μετατροπή Ελληνικών και Κυριλικών χαρακτήρων σε λατινικούς
     *
     * @param $string
     * @return mixed
     */
    static function GrCyr2Latin($string)
    {
        $cyr = array(
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        );
        $lat = array(
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        );
        $greek = array('α', 'ά', 'Ά', 'Α', 'β', 'Β', 'γ', 'Γ', 'δ', 'Δ', 'ε', 'έ', 'Ε', 'Έ', 'ζ', 'Ζ', 'η', 'ή', 'Η', 'θ', 'Θ', 'ι', 'ί', 'ϊ', 'ΐ', 'Ι', 'Ί', 'κ', 'Κ', 'λ', 'Λ', 'μ', 'Μ', 'ν', 'Ν', 'ξ', 'Ξ', 'ο', 'ό', 'Ο', 'Ό', 'π', 'Π', 'ρ', 'Ρ', 'σ', 'ς', 'Σ', 'τ', 'Τ', 'υ', 'ύ', 'Υ', 'Ύ', 'φ', 'Φ', 'χ', 'Χ', 'ψ', 'Ψ', 'ω', 'ώ', 'Ω', 'Ώ', 'ó', "'", "'", ',', ':', '+');
        $english = array('a', 'a', 'A', 'A', 'b', 'B', 'g', 'G', 'd', 'D', 'e', 'e', 'E', 'E', 'z', 'Z', 'i', 'i', 'I', 'th', 'Th', 'i', 'i', 'i', 'i', 'I', 'I', 'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'x', 'X', 'o', 'o', 'O', 'O', 'p', 'P', 'r', 'R', 's', 's', 'S', 't', 'T', 'u', 'u', 'Y', 'Y', 'f', 'F', 'ch', 'Ch', 'ps', 'Ps', 'o', 'o', 'O', 'O', 'o', '', '', '_', '-', '-');
        $string = str_replace($greek, $english, $string);
        $string = str_replace($cyr, $lat, $string);
        return $string;
    }

    /**
     * Σπάει το full file path name, σε filename και path
     *
     * @param $fullFilePathName
     * @return array
     */
    static function splitFilePathName($fullFilePathName)
    {
        $string_array = explode('/', $fullFilePathName);
        $filename = $string_array[count($string_array) - 1];
        $path = str_replace($filename, '', $fullFilePathName);

        return array('filename' => $filename, 'path' => $path);
    }

    /**
     * Επιστρέφει path string με βάση το έτος, τον μήνα και την μέρα. Τύπου "2017/09/01/"
     *
     * @return string
     */
    static function getPathFromYearAndMonth()
    {
        $myYear = date('Y');
        $myMonth = date('m');
        $myDay = date('d');

        return $myYear . '/' . $myMonth . '/' . $myDay . '/';
    }

    /**
     * Έλεγχος αν ένα directory είναι εγγράψιμο
     *
     * @param $dir {string} Το directory προς έλεγχο
     * @return bool True or False για την επιτυχία
     */
    static function isDirWritable($dir)
    {
        $dir = self::removeURLDoubleSlashes($dir . '/');
        $is_writable = @file_put_contents($dir . 'dummy.txt', "test");

        if ($is_writable > 0) {
            unlink($dir . 'dummy.txt');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Έλεγχος αν είναι enabled το Apache Rewrite Module
     *
     * @return bool
     */
    static function checkApacheRewriteModule()
    {

        return in_array('mod_rewrite', apache_get_modules()) ? true : false;

    }

    /**
     * Έλεγχος αν λειτουργεί το htaccess
     *
     * @return bool
     */
    static function checkIfHTaccessWorks()
    {
        $myFile = LOCAL_SERVER_IP_WITH_PORT . AJAX_PATH . 'framework/checkHTaccess';

        trigger_error($myFile);

        return file_get_contents($myFile) ? true : false;

    }

    /**
     * Έλεγχος αν τρέχουν τα routing rules, αναλόγως τον web server
     *
     */
    static function checkWebServerForRoutingRules()
    {
        if(!self::isInDockerContainer()) {
            // Έλεγχος για το τι server είναι. Αν δεν είναι lighttpd, τρέχει τον έλεγχο για apache
            if (isset($_SERVER['SERVER_SOFTWARE'])) {
                if (stripos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
                    // Έλεγχος αν είναι enabled το mod_rewrite
                    if (!Utilities::checkApacheRewriteModule()) {
                        die(__('mod_rewrite_disabled'));
                    }
                }

                // Έλεγχος αν λειτουργεί το htaccess γενικά
                if (!Utilities::checkIfHTaccessWorks()) {
                    die(__('htaccess_cant_work'));
                }
            }
        }
    }

    static function isInDockerContainer()
    {
        $dockerFile = '/.dockerenv';

        return file_exists($dockerFile) ? true : false;

    }

}