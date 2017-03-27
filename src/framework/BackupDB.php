<?php

/**
 *
 * File: BackupDB.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 12/03/17
 * Time: 21:51
 *
 * Κλάση που περιέχει μεθόδους για backup και restore της βάσης
 *
 * TODO να φτιαχτεί η μέθοδος για restore
 * TODO να σου δίνει επιλογή να κατεβάσεις το αρχείο
 *
 */

namespace apps4net\framework;


class BackupDB extends MyDB
{
    public $tables = array();  // Το array με τα tables της βάσης που θα κάνουμε backup
    public $sqlFile; // Το αρχείο που βρίσκεται το backup της βάσης
    private $sql;   // To query που θα εκτελεστεί

    // Επιστρέφει το string που δημιουργεί τον πίνακα $table
    static function getTableCreateString($table)
    {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql = 'SHOW CREATE TABLE '.$table;

        $stmt = self::$conn->prepare($sql);

        $stmt->execute();

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result = $item['Create Table'];
        } else {
            $result = false;
        }

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Επιστρέφει το insert string για το $tableRow  του πίνακα $table, σύμφωνα και με τα $tableFields
    static function getInsertStringForTableRow($table, $tableRow, $tableFields)
    {
        $insertString='INSERT INTO '.$table.' (';

        // Προσθήκη των fields στο string
        foreach ($tableFields as $field) {
            $insertString.= $field.',';
        }
        $insertString = page::cutLastString($insertString,',');
        $insertString.= ') VALUES (';

        // Προσθήκη των values στο string
        foreach ($tableRow as $value) {
            $insertString.= '\''.addslashes($value).'\',';
        }
        $insertString = page::cutLastString($insertString,',');
        $insertString.= ');';

        return $insertString;

    }


    // Παίρνει backup της βάσης
    public function backupDatabase()
    {
        set_time_limit(0);

        // Σύνδεση στην βάση
        $conn = new MyDB();
        $conn->CreateConnection();


        // Θέτει το αρχείο στο οποίο θα εξαχθεί το backup
        $filename = BACKUP_FILE_PREFIX . date('YmdHis') . '.sql';
        $file = new FilesIO(OUTPUT_FOLDER, $filename);

        // Παίρνουμε την λίστα όλων των πινάκων στην βάση, αν δεν έχουμε δώσει μια συγκεκριμένη ήδη στο $this->tables
        if(!isset($this->tables)) {
            $this->tables=self::getDatabaseTablesList();
        }

        $file->insertRow("# ****** CREATE QUERIES ******");

        // Δημιοργούμε τα create tabe strings και τα προσθέτουμε στο $data
        foreach ($this->tables as $table) {
            $createTableString = self::getTableCreateString($table);

            // Γράφει την σχετική εγγραφή στο αρχείο
            $file->insertRow("\n\n".$createTableString.";\n\n");
        }

        $file->insertRow("\n\n\n\n");

        $file->insertRow("# ****** INSERT QUERIES ******\n\n");

        // Διαβάζουμε τα περιεχόμενα κάθε table και δημιουργούμε τα inserts
        foreach ($this->tables as $table) {

            trigger_error('PROCCESING '.$table);

            $file->insertRow("# ****** TABLE: ".$table." ******\n\n");

            // Παίρνουμε τα περιεχόμενα του πίνακα
            $sql = 'SELECT * FROM '.$table;
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();


            // Τα πεδία του πίνακα
            $tableFields = self::getTableFields($table, null);

            // Δημιουργία του insert string
            while($tableRow=$stmt->fetch(\PDO::FETCH_ASSOC)) {
                $file->insertRow(self::getInsertStringForTableRow($table, $tableRow, $tableFields)."\n");
            }

            $file->insertRow("\n\n\n\n");
            $stmt->closeCursor();
            $stmt = null;

        }

        trigger_error('END OF SCRIPT');
        return true;
    }


    // Καθαρίζει το query από string που δεν χρειάζονται
    public function cleanQuery()
    {
        $this->sql=str_replace("\n",'',$this->sql); // αφαιρεί τα \n
    }


    // Εκτελεί το query $this->query
    public function executeQuery()
    {
//        trigger_error($this->sql);

//        $stmt = self::$conn->prepare($sql);


        self::$conn->query($this->sql);

//        if($item=$stmt->fetch(\PDO::FETCH_ASSOC)) {
//            $result = true;
//        } else {
//            $result = false;
//        }

//        $stmt->closeCursor();
//        $stmt = null;

//        return $result;

    }

    // Σβήνει όλα τα tables που έχουμε επιλέξει στο $this->tables
    public function dropTables()
    {
        foreach ($this->tables as $table) {
            if(MyDB::checkIfTableExist($table)) { // Αν υπάρχει το table, το σβήνουμε
                MyDB::dropTable($table);
            }
        }
    }


    // Κάνει restore την βάση όπως έχει αποθηκευτεί στο αρχείο $this->sqlFile, από την παραπάνω μέθοδο
    public function restoreDatabase()
    {
        set_time_limit(0);

        $handle = fopen($this->sqlFile, "r"); // Άνοιγμα του αρχείου


        if ($handle) {  // Αν υπάρχει το αρχείο
            $this->sql='';
//            $counter=0;

            // Σβήνουμε πρώτα όλα τα tables που έχουμε επιλέξει στο $this->tables
            $this->dropTables();

            // Το διαβάζουμε γραμμή-γραμμή, όσο δεν έχει φτάσει στο τέλος του
            while ( (($line = fgets($handle)) !== false) ) {

                // Αν δεν είναι κενή γραμμή ή σχόλιο
                if( ($line!=="\n") && (!preg_match('/#/', $line)) ) {

                    // Αν δεν έχει ερωτηματικό, άρα δεν έχει τελειώσει το query
                    if (!preg_match('/;/', $line)) {
                        $this->sql.=$line;
                    } else { // Αλλιώς κλείνουμε το query και το εκτελούμε
                        $this->sql.=$line;
                        $this->cleanQuery();
                        $this->executeQuery();  //  Εκτελεί το query

                        $this->sql='';
                    }
                }

//                $counter++;
            }


            fclose($handle);
        } else {
            die('Problem with file');
        }

        return true;
    }

}