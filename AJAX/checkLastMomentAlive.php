<?php
/**
 * File: checkLastMomentAlive.php
 * Created by rocean
 * Date: 07/11/16
 * Time: 01:55
 * Ελέγχει την ώρα που πέρασε από το τελευταίο timestamp που καταχωρήθηκε
 * ώστε να ξέρει αν τρέχει ακόμη το script
 */



require_once ('../libraries/common.inc.php');

Page::checkValidAjaxRequest();

//Page::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
$lastMomentAlive=Page::getLastMomentAlive();  // παίρνει την τιμή του lastMomentAlive

if(!$lastMomentAlive=='') { // Αν η τιμή δεν είναι κενό την υπολογίζουμε
    $TimeDifference = time() - $lastMomentAlive;

//    trigger_error($TimeDifference);
    
    if ($TimeDifference > 5) // Αν έχει να δώσει σημία ζωής πάνω από 5 δευτερόλεπτα, τότε ο συγχρονισμός έχει λήξει
        $jsonArray = array('success' => false);
    else $jsonArray = array('success' => true);
} else $jsonArray = array('success' => true);  // Αν είναι κενό σημαίνει ότι τρέχει ακόμη τα πρώτα στάδια του
                                                // συγχρονισμού που δεν μπορεί να στείλει τιμές

echo json_encode($jsonArray);