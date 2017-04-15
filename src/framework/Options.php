<?php
/**
 *
 * File: Options.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 03/04/17
 * Time: 22:04
 *
 * Κλάση διαχείρισης των options
 *
 */

namespace apps4net\framework;

class Options extends MyDB
{
    public $defaultOptions = array(); // Τα defaultOptions που θα καταχωρηθούν στην βάση
    public $defaultProgress = array(); // Τα progress fields που θα προστεθούν στον πίνακα progress
    public $defaultDownloadPaths = array(); // Τα path names που θα έχει το table download_paths

    // Αλλάζει το $value ενός $option
    public function changeOption ($option, $value)
    {
        $crypto = new Crypto();
        self::CreateConnection();

        $sql = 'UPDATE options SET option_value=? WHERE option_name=?';
        $stmt = MyDB::$conn->prepare($sql);

        if(self::getOptionEncrypt($option)==1)
            $value= $crypto->EncryptText($value);

        if($stmt->execute(array($value, $option)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργία ενός option
    public function createOption ($option, $value, $setting, $encrypt)
    {
        self::CreateConnection();
        $crypto = new Crypto();

        $sql = 'INSERT INTO options (option_name, option_value, setting, encrypt) VALUES(?,?,?,?)';
        $stmt = MyDB::$conn->prepare($sql);

        if($encrypt==1)
            $value= $crypto->EncryptText($value);

        try {
            $stmt->execute(array($option, $value, $setting, $encrypt));
            $result = true;
        } catch (\PDOException $pe) {
            $result = false;
            trigger_error($pe->getMessage());
        }


        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ανάγνωση ενός option
    public function getOption ($option)
    {
        $crypto = new Crypto();
        self::CreateConnection();

        $sql = 'SELECT encrypt,option_value FROM options WHERE option_name=?';
        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($option));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC)) {

            $result = $item['option_value'];
            if($result && $item['encrypt']==1)
                $result= $crypto->DecryptText($result);
        }

        else $result=false;


        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ανάγνωση του encrypt πεδίου από τα options
    public function getOptionEncrypt ($option)
    {
        self::CreateConnection();

        $sql = 'SELECT encrypt FROM options WHERE option_name=?';
        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($option));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['encrypt'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Καθαρίζει τα options από διπλοεγγραφές
    static function clearOptions()
    {
        self::CreateConnection();

        $sql = 'DELETE FROM options
                WHERE option_id NOT IN (SELECT * 
                FROM (SELECT MIN(o.option_id)
                            FROM options o
                            GROUP BY o.option_name) x)';

        $stmt = self::$conn->prepare($sql);


        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // TODO να ελέγχει και να σβήνει ότι είναι περιττό
    // Επιστρέφει τα options σε array. Ελέγχει και δημιουργεί όσα options δεν υπάρχουν
    // @return: array $newArray, με key το όνομα του option και value το value του option
    public function getOptionsArray()
    {
        $crypto = new Crypto();

        // Παίρνουμε τα αποτελέσματα του options σε array
        $optionsArray = self::getTableArray('options', null, null, null, null, null, null);
        $newArray = array();

        // Θέτουμε για key το όνομα του option και για value το value του option
        foreach ($optionsArray as $item) {
            $newArray[$item['option_name']] = $item['option_value'];
        }

        // Παίρνει την τιμή του restoreRunning στο progress
        $restoreRunning = MyDB::getTableFieldValue('progress', 'progressName=?', 'restoreRunning', 'progressValue');

        if($restoreRunning=='0') { // Αν δεν τρέχει το restore
            // Ελέγχουμε αν κάποιο option που βρίσκεται στο $this->defaultOptions δεν υπάρχει στην βάση
            // Το δημιουργούμε αν δεν υπάρχει
            foreach ($this->defaultOptions as $option) {
                if (!isset($newArray[$option['option_name']])) {

                    if ($this->createOption($option['option_name'], $option['option_value'],
                        $option['setting'], $option['encrypt'])
                    ) {
                        $newArray[$option['option_name']] = $option['option_value'];
                    }

                }

            }
        }

        return $newArray;
    }


    // TODO να το πάω στο progress class
    // Ελέγχει αν υπάρχουν όλα τα progress fields κι αν δεν υπάρχουν τα δημιουργεί
    public function checkProgressFields()
    {
        // Παίρνουμε τα αποτελέσματα του progress σε array
        $progressArray = self::getTableArray('progress', null, null, null, null, null, null);
        $newArray = array();

        // Θέτουμε για key το όνομα του option και για value το value του option
        foreach ($progressArray as $item) {
            $newArray[] = $item['progressName'];
        }

        // Ελέγχουμε αν κάποιο progress που βρίσκετε στο $this->defaultProgress δεν υπάρχει στην βάση
        // Το δημιουργούμε αν δεν υπάρχει
        foreach ($this->defaultProgress as $progress) {
            if(in_array($progress['progressName'], $newArray) == false) {
                Progress::createProgressName($progress['progressName'], $progress['progressValue']);
            }
        }

    }

    // TODO να το προσθέσω πιθανά σε άλλη κλάση
    // Ελέγχει το download_paths table, αν έχει τιμές κι επιστρέφει τα paths σε array
    public function getDownloadPaths()
    {
        // Παίρνουμε τα αποτελέσματα του download_paths σε array
        $downloadPathsArray = self::getTableArray('download_paths', null, null, null, null, null, null);
        $newArray = array();


        // Θέτουμε για key το όνομα του option και για value το value του option
        foreach ($downloadPathsArray as $item) {
            $newArray[$item['path_name']] = $item['file_path'];
        }

        // Ελέγχουμε αν κάποιο path name που βρίσκετε στο $this->defaultDownloadPaths δεν υπάρχει στην βάση
        // Το δημιουργούμε αν δεν υπάρχει
        foreach ($this->defaultDownloadPaths as $item) {
            if(!isset($newArray[$item])) {
                $conn = new MyDB();
                $sql = 'INSERT INTO download_paths (path_name) VALUES(?)';   // Εισάγει στον πίνακα download_paths
                $pathsArray = array($item);
                $conn->insertInto($sql, $pathsArray);
            }
        }

        return $newArray;

    }


}