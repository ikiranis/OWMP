<?php
/**
 *
 * File: Progress.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 28/03/17
 * Time: 17:52
 *
 * Μέθοδοι διαχείρισης του progress ενός script
 *
 */

namespace apps4net\framework;


class Progress
{

    // Ελέγχει αν υπάρχει το $progressName στον πίνακα progress
    static function checkIfProgressNameExists($progressName) {
        MyDB::createConnection();

        $sql = 'SELECT progressName FROM progress WHERE progressName=?';
        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($progressName));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC)) {

            $result=true;
        }

        else $result=false;


        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργεί ένα $progressName στον πίνακα progress
    static function createProgressName($progressName, $progressValue) {
        MyDB::createConnection();

        $sql = 'INSERT INTO progress (progressName, progressValue) VALUES(?,?)';
        $stmt = MyDB::$conn->prepare($sql);


        if($stmt->execute(array($progressName, $progressValue)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Καταχωρεί το ποσοστό εξέλιξης progress
    static function updatePercentProgress($progress) {
        $progressUpdateArray=array ($progress, 'progressInPercent');
        MyDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Ενημερώνει με 1 (true) ή 0 (false) το killCommand του πίνακa progress
    static function setKillCommand($theCommand) {
        $progressUpdateArray=array ($theCommand, 'killCommand');
        MyDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);

        return true;
    }

    // Θέτει τιμή στο currentSong
    static function setCurrentSong($theSong) {
        $progressUpdateArray=array ($theSong, 'currentSong');
        MyDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Δίνει timespamp τιμή στο lastMomentAlive του πίνακa progress
    static function setLastMomentAlive($operation) {
        if($operation==true)
            $theTimestamp = time();
        else $theTimestamp='';

        $progressUpdateArray=array ($theTimestamp, 'lastMomentAlive');
        MyDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Επιστρέφει το killCommand από τον πίνακα progress
    static function getKillCommand() {
        if($result=MyDB::getTableFieldValue('progress', 'progressName=?', 'killCommand', 'progressValue'))
            return $result;
        else return false;
    }


    // Επιστρέφει το currentSong από τον πίνακα progress
    static function getCurrentSong() {
        if($result=MyDB::getTableFieldValue('progress', 'progressName=?', 'currentSong', 'progressValue'))
            return $result;
        else return false;
    }

    // Επιστρέφει το lastMomentAlive από τον πίνακα progress
    static function getLastMomentAlive() {
        if($result=MyDB::getTableFieldValue('progress', 'progressName=?', 'lastMomentAlive', 'progressValue'))
            return $result;
        else return false;
    }

    // Επιστρέφει το ποσοστό εξέλιξης progress
    static function getPercentProgress() {
        if($result=MyDB::getTableFieldValue('progress', 'progressName=?', 'progressInPercent', 'progressValue'))
            return $result;
        else return false;
    }


    // Κάνει τους έλεγχους του progress και του τερματισμού
    static function setProgress($progressPercent) {
        self::updatePercentProgress($progressPercent); // Το ποσοστό του progress


        if(self::getKillCommand()=='1') { // Αν killCommand είναι 1 τότε σταματούμε την εκτέλεση του script
            self::setKillCommand('0');  // Το επαναφέρουμε πρώτα σε 0 για το μέλλον
            exit();
        }

    }

    // Καταχωρεί το ποσοστό εξέλιξης progress
    static function updateRestoreRunning($value) {
        $progressUpdateArray=array ($value, 'restoreRunning');
        MyDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

}