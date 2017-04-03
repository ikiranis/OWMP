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
            if($item['encrypt']==1) {  // Αν είναι encrypted το value το κάνουμε decrypt
                $newArray[$item['option_name']] = $crypto->DecryptText($item['option_value']);
            } else {
                $newArray[$item['option_name']] = $item['option_value'];
            }

        }

        // Ελέγχουμε αν κάποιο option που βρήσκετε στο $this->defaultOptions δεν υπάρχει στην βάση
        // Το δημιουργούμε αν δεν υπάρχει
        foreach ($this->defaultOptions as $option) {
            if(!isset($newArray[$option['option_name']])) {

                if($this->createOption($option['option_name'], $option['option_value'],
                    $option['setting'], $option['encrypt'])) {
                    $newArray[$option['option_name']] = $option['option_value'];
                }

            }

        }

        // TODO να κάνω έλεγχο στην βάση αν χρειάζονται alter στα tables, όπως χρειάζεται στο options

        return $newArray;
    }


    // ΈΧΕΙ ΒΓΕΙ ΕΚΤΟΣ
    // Εισάγει τις αρχικές τιμές στον πίνακα Options και στον progress
    public function startBasicOptions()
    {

        $options = new Options();
        $conn = new MyDB();

//        if(!$conn->getOption('interval_value'))
//            $conn->createOption('interval_value', '5', 1, 0);

//        if(!$conn->getOption('mail_host'))
//            $conn->createOption('mail_host', 'smtp.gmail.com', 1, 0);
//
//        if(!$conn->getOption('mail_username'))
//            $conn->createOption('mail_username', 'username', 1, 0);
//
//        if(!$conn->getOption('mail_password')) {
//            $conn->createOption('mail_password', '12345678',1,1);
//
//        if(!$conn->getOption('mail_from'))
//            $conn->createOption('mail_from', 'username@mail.com', 1, 0);
//
//        if(!$conn->getOption('mail_from_name'))
//            $conn->createOption('mail_from_name', 'name', 1, 0);


        // Οι αρχικές τιμές στον πίνακα options
        if(!$options->getOption('convert_alac_files'))
            $options->createOption('convert_alac_files', 'false', 1, 0);

        if(!$options->getOption('playlist_limit'))
            $options->createOption('playlist_limit', '150', 1, 0);

        if(!$options->getOption('dir_prefix'))
            $options->createOption('dir_prefix', '/', 1, 0);

        if(!$options->getOption('syncItunes'))
            $options->createOption('syncItunes', 'false', 1, 0);

        if(!$options->getOption('date_format'))
            $options->createOption('date_format', 'Y-m-d', 1, 0);

        if(!$options->getOption('icecast_server'))
            $options->createOption('icecast_server', '0.0.0.0:8000', 1, 0);

        if(!$options->getOption('icecast_mount'))
            $options->createOption('icecast_mount', 'listen', 1, 0);

        if(!$options->getOption('icecast_user'))
            $options->createOption('icecast_user', 'user', 1, 0);

        if(!$options->getOption('icecast_pass'))
            $options->createOption('icecast_pass', 'pass', 1, 1);

        if(!$options->getOption('icecast_enable'))
            $options->createOption('icecast_enable', 'false', 1, 0);

        if(!$options->getOption('jukebox_enable'))
            $options->createOption('jukebox_enable', 'false', 1, 0);

        if(!$options->getOption('default_language'))
            $options->createOption('default_language', 'en', 1, 0);

        if(!$options->getOption('youtube_api'))
            $options->createOption('youtube_api', 'AIzaSyArMqCdw1Ih1592YL96a2Vdo5sGo6vsS4A', 1, 0);

        if(!$options->getOption('play_percentage'))
            $options->createOption('play_percentage', '20', 1, 0);




        // TODO να τα περνάω κι αυτά όπως περνάνε τα options

        // Οι αρχικές τιμές στον πίνακα progress
        if(!Progress::checkIfProgressNameExists('progressInPercent'))
            Progress::createProgressName('progressInPercent');

        if(!Progress::checkIfProgressNameExists('progressMessage'))
            Progress::createProgressName('progressMessage');

        if(!Progress::checkIfProgressNameExists('killCommand')) {
            Progress::createProgressName('killCommand');
            Progress::setKillCommand('0');
        }

        if(!Progress::checkIfProgressNameExists('lastMomentAlive')) {
            Progress::createProgressName('lastMomentAlive');
            Progress::setLastMomentAlive(true);
        }

        if(!Progress::checkIfProgressNameExists('currentSong')) {
            Progress::createProgressName('currentSong');
        }



    }

}