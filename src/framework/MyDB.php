<?php

/**
 * File: MyDB.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 *
 * DB Class
 *
 */

namespace apps4net\framework;

class MyDB
{

    public static $connStr = CONNSTR;
    public static $DBuser = DBUSER;
    public static $DBpass = DBPASS;

    public static $conn = NULL;

    // 30 μέρες
    private static $CookieTime=60*60*24*30;

    // Άνοιγμα της σύνδεσης στην βάση
    static function CreateConnection(){
        if (!self::$conn) {
            try {
                self::$conn = new \PDO(self::$connStr, self::$DBuser, self::$DBpass,
                    array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            } catch (\PDOException $pe) {
                die('Could not connect to the database because: ' .
                    $pe->getMessage());
            }
        }
    }

    // Εκτελεί ένα insert sql query
    public function insertInto($sql, $sqlParams)
    {

        self::CreateConnection();

        $stmt = self::$conn->prepare($sql);

        $stmt->execute($sqlParams);

        $inserted_id=self::$conn->lastInsertId();

        return $inserted_id;

        $stmt->closeCursor();
        $stmt = null;

    }

    // Σετάρει ένα cookie και το σώζει κωδικοποιημένο
    static function setACookie($cookieName, $value) {
        $crypt = new Crypto();
        setcookie($cookieName, $crypt->EncryptText($value), time()+self::$CookieTime, PROJECT_PATH);
    }

    // Διαβάζει ένα cookie και το επιστρέφει αποκωδικοποιημένο
    static function getACookie($cookieName) {
        $crypt = new Crypto();

        return $crypt->DecryptText($_COOKIE[$cookieName]);
    }

    // Επιστρέφει το decrypted text του session $name
    public function getSession($name) {
        $crypto = new Crypto();
        $result= $crypto->DecryptText($_SESSION[$name]);

        return $result;
    }

    // Θέτει την τιμή $text στο session $SessionName
    public function setSession($SessionName, $text) {
        $crypto = new Crypto();
        $_SESSION[$SessionName]=$crypto->EncryptText($text);
    }

    // Επιστρέφει σε array τον πίνακα $table. Δέχεται προεραιτικά συγκεκριμένα fiels σε μορφή string $fields.
    // Επίσης δέχεται $condition (π.χ. id=?) για το WHERE μαζί με τις παραμέτρους σε array για το execute
    // π.χ $playlist = MyDB::getTableArray('music_tags', null, $condition, $arrayParams, 'date_added DESC LIMIT ' . $offset . ',' . $step, 'files', $joinFieldsArray);
    static function getTableArray ($table, $fields, $condition, $ParamsArray, $orderBy, $joinTable, $joinFields) {
        self::CreateConnection();

        if(is_array($table))
            $MainTable=$table[0];
        else $MainTable=$table;

        if(!isset($fields)) {
            $sql = 'SELECT * FROM '.$MainTable;
        } else {
            $sql = 'SELECT '.$fields.' FROM '.$MainTable;
        }

        if(isset($joinTable)) {
            if(!is_array($table)) {
                $sql = $sql . ' JOIN ' . $joinTable . ' on ' . $MainTable . '.' . $joinFields['firstField'] . '=' . $joinTable . '.' . $joinFields['secondField'];
            } else { // Όταν είναι πολλαπλά tables για join
                $counter=0;

                foreach ($table as $item) {
                    if($counter==0) {
                        $TableToJoin=$joinTable;
                    }
                    else {
                        $TableToJoin=$item;
                    }

                    $sql = $sql . ' JOIN ' . $TableToJoin . ' on ' . $item . '.' . $joinFields['firstField'] . '=' . $joinTable . '.' . $joinFields['secondField'];
                    $counter++;
                }
            }
        }

        if(isset($condition))
            $sql=$sql.' WHERE '.$condition;

        if(isset($orderBy))
            $sql=$sql.' ORDER BY '.$orderBy;

//        trigger_error('SQL   '.$sql);

        $stmt = self::$conn->prepare($sql);

        if(isset($ParamsArray))
            $stmt->execute($ParamsArray);
        else $stmt->execute();


        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργεί και επιστρέφει το query (string) με βάση τις συγκεκριμένες παραμέτρους
    static function createQuery ($table, $fields, $condition, $orderBy, $joinTable, $joinFields) {

        if(!isset($fields)) $sql = 'SELECT * FROM '.$table;
        else $sql = 'SELECT '.$fields.' FROM '.$table;

        if(isset($joinTable))
            $sql=$sql.' JOIN '.$joinTable.' on '.$table.'.'.$joinFields['firstField'].'='.$joinTable.'.'.$joinFields['secondField'];

        if(isset($condition))
            $sql=$sql.' WHERE '.$condition;

        if(isset($orderBy))
            $sql=$sql.' ORDER BY '.$orderBy;


        return $sql;
    }

    // Σβήνει μία εγγραφή από τον $table, όπου το $dbfield=$value
    public function deleteRowFromTable ($table, $dbfield, $value) {
        self::CreateConnection();

        $sql='DELETE FROM '.$table.' WHERE '.$dbfield.'=?';

        $stmt = self::$conn->prepare($sql);

        if($stmt->execute(array($value)))
            $result=true;
        else $result=false;



        $stmt->closeCursor();
        $stmt = null;

        return $result;

    }


    // μετράει τα rows ενός πίνακα
    static function countTable($table) {
        self::CreateConnection();

        $sql = 'SELECT * FROM '.$table;
        $stmt = MyDB::$conn->prepare($sql);


        if($stmt->execute())

            $result=$stmt->rowCount();

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Ενημερώνει fields σε ένα table
    // $table ο πίνακας, $condition σε μορφή 'id=?'
    // $fields και $values σε array. To $fieldsText value το βάζουμε στο τέλος του $values
    // π.χ. updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    static function updateTableFields ($table, $condition, $fields, $values)
    {
        self::CreateConnection();

        $fieldsText='';

        foreach ($fields as $field) {
            $fieldsText = $fieldsText . $field . '=?, ';
        }

        $fieldsText=Utilities::cutLastString($fieldsText,', '); // Κόβει την τελευταία ','

        $sql = 'UPDATE '.$table.' SET '.$fieldsText.' WHERE '.$condition;
        $stmt = MyDB::$conn->prepare($sql);

        if($stmt->execute($values))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Ανάγνωση μιας τιμής ενός $field στον πίνακα $table
    // $table ο πίνακας, $condition σε μορφή 'id=?'. To $id το παιρνάμε στην $condition_value
    static function getTableFieldValue ($table, $condition, $condition_value, $field) {
        self::CreateConnection();

        $sql = 'SELECT '.$field.' FROM '.$table.' WHERE '.$condition;
        $stmt = self::$conn->prepare($sql);

        if(!is_array($condition_value))
            $stmt->execute(array($condition_value));
        else $stmt->execute($condition_value);

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item[$field];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // μετατρέπει τον δισδιάστατο πίνακα, που παράγει το PDO, σε μονοδιάστατο
    static function clearArray($someArray){
        $newArray=array();

        foreach($someArray as $some) {
            $newArray[]=$some[0];
        }

        return $newArray;
    }


    // Επιστρέφει την λίστα των πεδίων του $table σε μονοδιάστατο array. Αφαιρεί το $exclude array
    static function getTableFields ($table, $exclude) {

        self::CreateConnection();

        if(!isset($exclude)) {
            $exclude = array();
        }

        $sql = 'SHOW COLUMNS FROM '.$table;

        $stmt = self::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        foreach ($result as $item) {   // Φτιάχνει τον μονοδιάστατο πίνακα, αρκεί να μην είναι πεδίο που είναι στα $exclude
            if(!in_array($item['Field'], $exclude))
                $simpleResult[] = $item['Field'];
        }

        return $simpleResult;
    }


    // Επιστρέφει τον τύπο ενός field
    static function getTableFieldType ($table, $field) {

        self::CreateConnection();

        $sql = 'SHOW COLUMNS FROM '.$table.' WHERE Field=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($field));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['Type'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;


        return $result;
    }


    // Αντιγράφει ένα array σε αντίστοιχο table
    static function copyArrayToTable ($arrayToCopy, $table) {
        self::CreateConnection();

        $sql = 'INSERT INTO '.$table.' (id, file_id) VALUES(?,?)';
        $stmt = self::$conn->prepare($sql);

        foreach ($arrayToCopy as $item) {
            $stmt->execute(array($item['id'], $item['file_id']));
        }


        $stmt->closeCursor();
        $stmt = null;

    }


    // Ελέγχει αν είναι ενεργοποιημένος ο event scheduler
    static function checkMySQLEventScheduler() {
        self::CreateConnection();

        $sql = 'select user from INFORMATION_SCHEMA.PROCESSLIST where user=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array('event_scheduler'));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=true;

        else $result=false;


        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Θέτει τον event scheduler της mysql σε ON
    static function enableMySQLEventScheduler() {
        self::CreateConnection();

        $sql = 'SET GLOBAL event_scheduler = ON;';
        $stmt = self::$conn->prepare($sql);

        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργεί ένα event στην βάση
    // $timeInterval της μορφής '1 MINUTE', '1 ΜΟΝΤΗ' κοκ
    static function createMySQLEvent($eventName, $eventQuery, $timeInterval ) {
        self::CreateConnection();

        $event='CREATE EVENT '.$eventName.
            ' ON SCHEDULE EVERY '.$timeInterval.
            ' ON COMPLETION PRESERVE '.
            ' DO '.$eventQuery.
            ';';

        $stmt = self::$conn->prepare($event);

        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;

    }


    // Σβήνει παλιότερες εγγραφές από έναν πίνανα πριν τις $days μέρες
    static function deleteTableBeforeNDays($table, $dateField, $days) {
        self::CreateConnection();

        $sql = 'DELETE FROM '.$table.' WHERE '.$dateField.'<timestamp(date_sub(NOW(), INTERVAL '.$days.' DAY))';
        $stmt = self::$conn->prepare($sql);

        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Σβήνει τα πάντα από το $table
    static function deleteTable($table) {
        self::CreateConnection();

        $sql = 'TRUNCATE '.$table;
        $stmt = self::$conn->prepare($sql);


        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Σβήνει τελείως ένα $table
    static function dropTable($table) {
        self::CreateConnection();

        $sql = 'drop table '.$table;
        $stmt = self::$conn->prepare($sql);


        if($stmt->execute())

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Αντιγράφει τα $fields σε νέο $table, με βάση του select $query και τα $arrayParams
    static function copyFieldsToOtherTable($fields, $table, $query, $arrayParams) {
        self::CreateConnection();

        // Disable keys for faster bulk insert (works for MyISAM/InnoDB)
        self::$conn->exec('SET autocommit=0;');
        self::$conn->exec('SET unique_checks=0;');
        self::$conn->exec('SET foreign_key_checks=0;');

        // Truncate table instead of delete for speed
        $truncateSql = 'TRUNCATE TABLE ' . $table;
        self::$conn->exec($truncateSql);

        $sql = 'INSERT INTO '.$table.' ('.$fields.') '.$query;
        $stmt = self::$conn->prepare($sql);

        $result = $stmt->execute($arrayParams);

        $stmt->closeCursor();
        $stmt = null;

        // Re-enable keys
        self::$conn->exec('SET unique_checks=1;');
        self::$conn->exec('SET foreign_key_checks=1;');
        self::$conn->exec('COMMIT;');
        self::$conn->exec('SET autocommit=1;');

        return $result;
    }


    // Ελέγχει αν ο πίνακας $table υπάρχει και επιστρέφει true or false
    static function checkIfTableExist($table) {
        self::CreateConnection();

        $sql = 'DESCRIBE '.$table;
        $stmt = self::$conn->prepare($sql);


        if($stmt->execute())

            $result = true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργεί ένα table με βάση το $sql script
    static function runQuery($sql) {
        self::createConnection();

        $stmt = MyDB::$conn->prepare($sql);

        if($stmt->execute()) {
            $result = true;
        } else {
            $result=false;
        }

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ελέγχει αν υπάρχουν τα tables της βάσης και ότι δεν υπάρχει το δημιουργεί
    static function checkMySqlTables ()
    {
        global $mySqlTables;  // To array με τα tables και τα creation strings

        // Ελέγχει κάθε ένα table αν υπάρχει
        foreach ($mySqlTables as $item) {
            if(!self::checkIfTableExist($item['table'])) {
                self::runQuery($item['sql']); // αν δεν υπάρχει το δημιουργεί
            }
        }


    }

    // Ελέγχει αν χρειάζονται αλλαγές σε κάποιους τύπους πεδίων
    static function checkMySqlForTypeChanges()
    {
        global $mySqlChanges;  // To array με τις αλλαγές που χρειάζονται

        // Ελέγχει για αλλαγές στα types
        foreach ($mySqlChanges as $item) {
            // Αν το type του πεδίου είναι ίσο με το oldType, τότε τρέχουμε το alter query
            if(self::getTableFieldType($item['table'], $item['field'])==$item['oldType']) {
                self::runQuery($item['sql']);
            }
        }
    }


    // Αντιγράφει έναν πίνακα σε έναν άλλο πίνακα με το ίδιο structure
    static function copyTable($sourceTable, $destinationTable) {
        self::CreateConnection();

        $sql = 'INSERT INTO '.$destinationTable.' SELECT * FROM '.$sourceTable;
        $stmt = self::$conn->prepare($sql);


        if($stmt->execute())

            $result = true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Επιστρέφει τους πίνακες που έχει η βάση δεδομένων, σε array
    static function getDatabaseTablesList() {
        self::CreateConnection();

        $sql = 'SHOW TABLES';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        if($result) {
            // μετατροπή του array σε μονοδιάστατο
            return self::clearArray($result);
        } else {
            return false;
        }
    }

}
