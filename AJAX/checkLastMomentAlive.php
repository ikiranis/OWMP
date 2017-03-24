<?php
/**
 * File: checkLastMomentAlive.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 07/11/16
 * Time: 01:55
 * Ελέγχει την ώρα που πέρασε από το τελευταίο timestamp που καταχωρήθηκε
 * ώστε να ξέρει αν τρέχει ακόμη το script
 */

use apps4net\framework\Page;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(true);

//Page::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
$lastMomentAlive=Page::getLastMomentAlive();  // παίρνει την τιμή του lastMomentAlive
$progressInPercent=Page::getPercentProgress(); // Το ποσοστό που βρίσκεται

if(!$lastMomentAlive=='') { // Αν η τιμή δεν είναι κενό την υπολογίζουμε
    $TimeDifference = time() - $lastMomentAlive;

//    trigger_error($TimeDifference);

    // Αν έχει να δώσει σημεία ζωής πάνω από 5 δευτερόλεπτα, τότε ο συγχρονισμός έχει λήξει
    // Ή αν είναι το ποσοστό 0
    if ($TimeDifference > 5 || ($progressInPercent==0  && !$lastMomentAlive=='') )
        $jsonArray = array('success' => false);
    else $jsonArray = array('success' => true);
} else $jsonArray = array('success' => true);  // Αν είναι κενό σημαίνει ότι τρέχει ακόμη τα πρώτα στάδια του
                                                // συγχρονισμού που δεν μπορεί να στείλει τιμές

echo json_encode($jsonArray);