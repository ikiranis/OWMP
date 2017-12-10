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
 * ΠΡΟΣΟΧΗ στην σειρά που δηλώνονται τα $this->tables για backup και για restore. Στο backup πρέπει να δηλώνονται πρώτα
 * τα parent tables όταν υπάρχουν αλληλοεξαρτήσεις. Η αντίστροφη σειρά πρέπει να υπάρχει στο restore, γιατί πρέπει
 * να σβήνει πρώτα τα child tables.
 *
 */

namespace apps4net\framework;

class BackupDB extends MyDB
{
    public $tables = array();  // Το array με τα tables της βάσης που θα κάνουμε backup
    public $sqlFilePath; // To path που βρίσκεται το backup της βάσης
    public $sqlFile; // Το αρχείο που βρίσκεται το backup της βάσης
    private $sql;   // To query που θα εκτελεστεί
    public $createdFilename;
    public $createdFullPath;

    // Επιστρέφει το string που δημιουργεί τον πίνακα $table
    static function getTableCreateString($table)
    {
        self::createConnection();

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

    // Επιστρέφει το query για το drop του $table
    // @param: string $table = Ο πίνακας που θα σβηστεί
    // @return: string
    static function getDropTableString($table)
    {
        return 'DROP TABLE IF EXISTS '.$table;
    }


    // Επιστρέφει το insert string για το $tableRow  του πίνακα $table, σύμφωνα και με τα $tableFields
    static function getInsertStringForTableRow($table, $tableRow, $tableFields)
    {
        $insertString='INSERT INTO '.$table.' (';

        // Προσθήκη των fields στο string
        foreach ($tableFields as $field) {
            $insertString.= $field.',';
        }
        $insertString = Utilities::cutLastString($insertString,',');
        $insertString.= ') VALUES (';

        // Προσθήκη των values στο string
        foreach ($tableRow as $value) {
            if($value=='') {  // Αν είναι κενό το αντικαθιστούμε με null για να μην χτυπάει το insert
                $insertString.= 'null,';
            } else {
                $insertString.= '\''.addslashes($value).'\',';
            }
        }
        $insertString = Utilities::cutLastString($insertString,',');
        $insertString.= ');';

        return $insertString;

    }


    // Παίρνει backup της βάσης
    // @return: string $filename = το filename στο οποίο έχει σωθεί το backup
    public function backupDatabase()
    {
        set_time_limit(0);

        Progress::setProgress(0); // Μηδενίζει το progress

        // Σύνδεση στην βάση
        self::createConnection();

        // Θέτει το αρχείο στο οποίο θα εξαχθεί το backup
        $filename = BACKUP_FILE_PREFIX . date('YmdHis') . '.sql';
        $file = new FilesIO(OUTPUT_FOLDER, $filename, 'write');

        // Παίρνουμε την λίστα όλων των πινάκων στην βάση, αν δεν έχουμε δώσει μια συγκεκριμένη ήδη στο $this->tables
        if(!isset($this->tables)) {
            $this->tables=self::getDatabaseTablesList();
        }

        $file->insertRow("## ****** CREATE QUERIES ******");

        $totalInserts=0; // Τα συνολικά inserts που είναι να γίνουν

        // Δημιοργούμε τα drop και create table strings και τα προσθέτουμε στο $data
        foreach ($this->tables as $table) {
            $createTableString = self::getTableCreateString($table);
            $dropTableString = self::getDropTableString($table);

            // Γράφει την σχετικές εγγραφές στο αρχείο. Πρώτο το drop, μετά το create
            $file->insertRow("\n\n".$dropTableString.";\n");
            $file->insertRow($createTableString.";\n\n");

            $totalInserts+=MyDB::countTable($table); // Προσθέτει το μέγεθος του πίνακα στα $totalInserts
        }

        $file->insertRow("\n\n\n\n");

        $file->insertRow("## ****** INSERT QUERIES ******\n\n");

        $general_counter=0;  // Ο γενικός μετρητής για να υπολογίσουμε το ποσοστό του progress
        $progressCounter=0;  // Ο μετρητής για να στέλνει το progress ανα διαστήματα και όχι συνέχεια

        // Διαβάζουμε τα περιεχόμενα κάθε table και δημιουργούμε τα inserts
        foreach ($this->tables as $table) {

            trigger_error('PROCCESING '.$table);

            $file->insertRow("## ****** TABLE: ".$table." ******\n\n");

            // Παίρνουμε τα περιεχόμενα του πίνακα
            $sql = 'SELECT * FROM '.$table;
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();


            // Τα πεδία του πίνακα
            $tableFields = self::getTableFields($table, null);

            // Δημιουργία του insert string
            while($tableRow=$stmt->fetch(\PDO::FETCH_ASSOC)) {
                $file->insertRow(self::getInsertStringForTableRow($table, $tableRow, $tableFields)."\n");

                // Υπολογίζουμε και στέλνουμε το progress
                if($progressCounter>1000) { // ανα 100 items ενημερώνει το progress
                    $progressPercent = intval(($general_counter / $totalInserts) * 100);
                    Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
                    Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό
                    $general_counter++;
                    $progressCounter=0;
                } else {
                    $progressCounter++;
                    $general_counter++;
                }
            }

            $file->insertRow("\n\n\n\n");

            $stmt->closeCursor();
            $stmt = null;

        }

        trigger_error('END OF SCRIPT');

        // Επιστρέφει το αρχείο στο οποίο έχει σωθεί το backup
        $this->createdFilename = $filename;
        $this->createdFullPath = Utilities::removeURLDoubleSlashes(OUTPUT_FOLDER.$filename);
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
        try {
            self::$conn->query($this->sql);
        } catch (\PDOException $pe) {
            trigger_error('PROBLEM WITH QUERY: ' . $this->sql. ' ----> '.$pe->getMessage() );
        }
    }

    // Καθαρίζει όλα τα tables που έχουμε επιλέξει στο $this->tables
    public function clearTables()
    {
        foreach ($this->tables as $table) {
            trigger_error($table);
            if(MyDB::checkIfTableExist($table)) { // Αν υπάρχει το table, το καθαρίζουμε
                if(!MyDB::dropTable($table)) {
                    trigger_error('PROBLEM WITH DELETE '.$table);
                }
            }
        }
    }


    // Κάνει restore την βάση όπως έχει αποθηκευτεί στο αρχείο $this->sqlFile, από την παραπάνω μέθοδο
    public function restoreDatabase()
    {
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        // Θέτουμε το error mode ώστε να πετάει exception στα errors του PDO::query
        self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        Progress::setProgress(0); // Μηδενίζει το progress

        // Ανοίγουμε το αρχείο που περιέχει το backup, για ανάγνωση
        $file = new FilesIO($this->sqlFilePath, $this->sqlFile, 'read');

        $this->sql='';

        $general_counter=0;  // Ο γενικός μετρητής για να υπολογίσουμε το ποσοστό του progress
        $progressCounter=0;  // Ο μετρητής για να στέλνει το progress ανα διαστήματα και όχι συνέχεια

        // Σβήνουμε πρώτα όλα τα tables που έχουμε επιλέξει στο $this->tables
        $this->clearTables();

        $totalQueries = $file->getLines(); // Το σύνολο των γραμμών που υπάρχουν στο αρχείο

        // Το διαβάζουμε γραμμή-γραμμή, όσο δεν έχει φτάσει στο τέλος του
        while ( (($line = fgets($file->handle)) !== false) ) {

            // Αν δεν είναι κενή γραμμή ή σχόλιο
            if( ($line!=="\n") && (!preg_match('/##/', $line)) ) {

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

            // Υπολογίζουμε και στέλνουμε το progress
            if($progressCounter>100) { // ανα 100 items ενημερώνει το progress
                $progressPercent = intval(($general_counter / $totalQueries) * 100);
                Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
                Progress::setProgress($progressPercent);  // στέλνει το progress και ελέγχει τον τερματισμό
                $general_counter++;
                $progressCounter=0;
            } else {
                $progressCounter++;
                $general_counter++;
            }

        }


        $file->closeTheFile();

        return true;
    }

}