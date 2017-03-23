<?php

/**
 * File: RoceanDB.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 *
 * DB Class
 *
 */

namespace apps4net\framework;

class RoceanDB
{

    public static $connStr = CONNSTR;
    public static $DBuser = DBUSER;
    public static $DBpass = DBPASS;

    public static $conn = NULL;

    // 30 μέρες
    private static $CookieTime=60*60*24*30;


    // Εκτελεί ένα sql query
    function ExecuteSQL($sql, $sqlParams)
    {

        $this->CreateConnection();

        $stmt = self::$conn->prepare($sql);

        $stmt->execute($sqlParams);

        $inserted_id=self::$conn->lastInsertId();

        return $inserted_id;

        $stmt->closeCursor();
        $stmt = null;

    }

    // Ψάχνει αν υπάρχουν users στηνν βάση. Επιστρέφει true or false.
    function CheckIfThereIsUsers () {

        $this->CreateConnection();

        $sql='SELECT user_id FROM user';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute();

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
            return true;
        else return false;

        $stmt->closeCursor();
        $stmt = null;

    }
    
    
    // Ελέγχει αν υπάρχει logged in user και είναι σωστός
    static function checkIfUserIsLegit() {

        if(isset($_SESSION['username']) && isset($_SESSION['salt'])) {
            $userName = self::getSession('username');
            $userID = self::getUserID($userName);
            $userSalt = self::getSession('salt');
            $userSaltInDB = self::getSaltForUser($userID);

            if ($userSalt == $userSaltInDB) {
                return true;
            } else {
                trigger_error('USER NOT LEGIT');
                return false;
            }
        } else {
            trigger_error('USER NOT LEGIT');
            return false;
        }
        
    }

    // Ψάχνει αν admin user στην βάση. Επιστρέφει true or false.
    static function CheckIfThereIsAdminUser () {

        self::CreateConnection();

        $sql='SELECT user_id FROM user WHERE user_group=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array('1'));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
            return true;
        else return false;

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

    // Ελέγχει αν ο χρήστης υπάρχει στην βάση και είναι σωστά τα username, password που έχει δώσει
    function CheckLogin($username, $password, $SavePassword) {

        $this->CreateConnection();

        $sql='SELECT * FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));



        // Αν ο χρήστης username βρεθεί. Αν υπάρχει δηλαδή στην βάση μας
        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {

            // Προσθέτει το string στο password που έδωσε ο πιθανός χρήστης
            $HashThePassword=$password.Crypto::$KeyForPasswords;

            $sql='SELECT * FROM salts WHERE user_id=?';
            $salt = self::$conn->prepare($sql);
            $salt->execute(array($item['user_id']));

            // Φέρνει το salt από τον πίνακα salts για τον συγκεκριμένο χρήστη. Ενώνει τα 4 κομμάτια του hashed password
            // που είχαμε σπάσει στο αρχικό του ενιαίο
            if($salt_item=$salt->fetch(\PDO::FETCH_ASSOC)) {
                $combined_password=$salt_item['algo'].$salt_item['cost'].$salt_item['salt'].$item['password'];

                // Κρατάμε το salt για χρήση παρακάτω
                $user_salt=$salt_item['salt'];

            }

            // Κάνει τον έλεγχο του ενωμένου, πλέον, hashed password με τον hashed password που έδωσε ο πιθανός χρήστης
            // Αν ταιριάζουν τότε ο χρήστης γίνεται authenticated. Αλλιώς επιστρέφει "Λάθος password"
            if (password_verify($HashThePassword, $combined_password)) {


                // Αν ο χρήστης έχει επιλέξει να τον θυμάται η εφαρμογή ότι είναι logged in
                if($SavePassword=='true') {

                    // Χρησιμοποιούμε 2 cookies. Στο ένα έχουμε το username και στο άλλο το salt του χρήστη
                    // Τα Cookies θα μείνουν ανοιχτά για self::$CookieTime χρόνο
//                    setcookie('username', $item['username'], time()+self::$CookieTime, PROJECT_PATH);
                    self::setACookie('username', $item['username']);
                    self::setACookie('salt', $user_salt);
//                    setcookie('salt', $user_salt, time()+self::$CookieTime, PROJECT_PATH);

                }
                else {
                    if (isset($_COOKIE['username'])) {
                        unset($_COOKIE['username']);
                        setcookie('username','',-1);
                        unset($_COOKIE['salt']);
                        setcookie('salt','',-1);
                    }


                }

                self::setSession('username',$item['username']);
                self::setSession('salt',$user_salt);

                self::insertLog('User Login'); // Προσθήκη της κίνησης στα logs

//                $_SESSION["username"]=$crypto->EncryptText($item['username']);

                // Επιστρέφει την επιτυχία (ή όχι) στο array $result με ανάλογο μήνυμα
                $result = array ('success'=>true, 'message'=>__('user_is_founded'));

//                echo '<p>Βρέθηκε ο χρήστης: '.$crypto->DecryptText($_SESSION["username"]).'</p>';


            }
            else {
                $result = array ('success'=>false, 'message'=>__('wrong_password'));

            }

        }
        else {
            $result = array ('success'=>false, 'message'=>__('user_dont_exist'));

        }


        return $result;

//        $stmt->closeCursor();
//        $stmt = null;

    }

    // Εισάγει τον νέο χρήστη στην βάση
    function CreateUser($username, $email, $password, $usergroup, $agent, $fname, $lname)
    {
        self::CreateConnection();

        $sql = 'INSERT INTO user(username, email, password, user_group, agent) VALUES(?,?,?,?,?)';
        
        $crypto = new Crypto();

        $hashed_array=$crypto->EncryptPassword($password);

//        echo '<p>'.$hashed_array['hashed_password'].' | '.$hashed_array['algo'].' | '.$hashed_array['cost'].' | '.$hashed_array['salt'].'</p>';

        $EncryptedPassword=$hashed_array['hashed_password'];

        $arrayParams = array($username, $email, $EncryptedPassword, $usergroup, $agent);

        if($inserted_id=$this->ExecuteSQL($sql, $arrayParams)) {
            $sql = 'INSERT INTO salts(user_id, salt, algo, cost) VALUES(?,?,?,?)';   // Εισάγει στον πίνακα salts

            $saltArray = array($inserted_id, $hashed_array['salt'], $hashed_array['algo'], $hashed_array['cost'] );

            $this->ExecuteSQL($sql, $saltArray);

            $sql = 'INSERT INTO user_details(user_id, fname, lname) VALUES(?,?,?)';  // Εισάγει στον πίνακα user_details

            $detailsArray = array($inserted_id, $fname, $lname);

            $this->ExecuteSQL($sql, $detailsArray);

            return $inserted_id;

        }
        else return false;
        
    }

    // Ενημερώνει την εγγραφή ενός χρήστη
    function UpdateUser($id, $username, $email, $password, $usergroup, $agent, $fname, $lname)
    {
        self::CreateConnection();

        if(!$password==null) {  // Αν δεν υπάρχει $password

            $crypto = new Crypto();

            $hashed_array=$crypto->EncryptPassword($password);

            $EncryptedPassword=$hashed_array['hashed_password'];

            $sql = 'UPDATE user SET username=?, email=?, password=?, agent=?, user_group=? WHERE user_id=?';

            $arrayParams = array($username, $email, $EncryptedPassword, $agent, $usergroup, $id);

        }
        else {
            $sql = 'UPDATE user SET username=?, email=?, agent=?, user_group=? WHERE user_id=?';
            $arrayParams = array($username, $email, $agent, $usergroup, $id);

        }


        $stmt = self::$conn->prepare($sql);

        if($stmt->execute($arrayParams)) {
            $result=true;

            $sql = 'UPDATE user_details SET fname=?, lname=? WHERE user_id=?';  // Εισάγει στον πίνακα user_details

            $detailsArray = array($fname, $lname, $id);

            $stmt3 = self::$conn->prepare($sql);

            if ($stmt3->execute($detailsArray))  $result=true;
            else $result=false;

            if (!$password==null) {

                $sql = 'UPDATE salts SET salt=?, algo=?, cost=? WHERE user_id=?';   // Εισάγει στον πίνακα salts

                $saltArray = array($hashed_array['salt'], $hashed_array['algo'], $hashed_array['cost'], $id);

                $stmt2 = self::$conn->prepare($sql);

                if ($stmt2->execute($saltArray)) $result=true;
                else $result=false;

            }


        }
        else $result=false;
        
        return $result;
        

    }

    // Επιστρέφει το salt για τον $userID
    static function getSaltForUser($userID) {
        self::CreateConnection();

        $sql='SELECT salt FROM salts WHERE user_id=?';
        $salt = self::$conn->prepare($sql);
        $salt->execute(array($userID));

        // Παίρνουμε το salt και το συγκρίνουμε με αυτό που έχει το σχετικό cookie
        if($salt_item=$salt->fetch(\PDO::FETCH_ASSOC)) {
            $result = $salt_item['salt'];
        }
        else $result=false;

        return $result;

        $stmt->closeCursor();
        $stmt = null;

    }

    // Έλεγχος αν ο χρήστης είναι logged id, αν υπάρχουν cookies. Η function επιστρέφει true or false
    function CheckCookiesForLoggedUser() {

        if (isset($_COOKIE['username'])) {

            self::CreateConnection();

            // Ψάχνουμε να βρούμε το id του user με το συγκεκριμένο username που έχει στο cookie
            $sql='SELECT user_id FROM user WHERE username=?';

            $stmt = self::$conn->prepare($sql);

            $stmt->execute( array ( self::getACookie('username') ) );

            // Αν βρεθεί το id του user ψάχνουμε τα salt του
            if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
            {

                $userSalt=self::getSaltForUser($item['user_id']);

                // Παίρνουμε το salt και το συγκρίνουμε με αυτό που έχει το σχετικό cookie
                if($userSalt) {
                    if($userSalt==self::getACookie('salt') ) {
                        self::setSession( 'username', self::getACookie('username') );  // Ανοίγει το session για τον συγκεκριμένο χρήστη
                        self::setSession( 'salt', $userSalt );
                        return true;
                    }
                    else return false;
                }
                // Η function επιστρέφει true αν συμφωνεί το salt που έχει στο cookie, με αυτό που υπάρχει στην βάση
                // Αλλιώς επιστρέφει false
            }
            else return false;
            
        }
        else return false;

        $stmt->closeCursor();
        $stmt = null;

    }

    // Άνοιγμα της σύνδεσης στην βάση
    function CreateConnection(){
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

    // Δέχεται το username και επιστρέφει το user id του. Αλλιώς false
    public function getUserID($username) {
        self::CreateConnection();

        $sql='SELECT user_id FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=$item['user_id'];
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;
        
        return $result;
    }

    // Επιστρέφει true αν ο $username υπάρχει στην βάση
    public function checkIfUserExists ($username) {
        self::CreateConnection();

        $sql='SELECT user_id FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=true;
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
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
    // π.χ $playlist = RoceanDB::getTableArray('music_tags', null, $condition, $arrayParams, 'date_added DESC LIMIT ' . $offset . ',' . $step, 'files', $joinFieldsArray);
    static function getTableArray ($table, $fields, $condition, $ParamsArray, $orderBy, $joinTable, $joinFields) {
        self::CreateConnection();

        if(is_array($table))
            $MainTable=$table[0];
        else $MainTable=$table;

        if(!isset($fields)) $sql = 'SELECT * FROM '.$MainTable;
        else $sql = 'SELECT '.$fields.' FROM '.$MainTable;

        if(isset($joinTable)) {
            if(!is_array($table))
                $sql = $sql . ' JOIN ' . $joinTable . ' on ' . $MainTable . '.' . $joinFields['firstField'] . '=' . $joinTable . '.' . $joinFields['secondField'];
            else { // Όταν είναι πολλαπλά tables για join
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


    // Δέχεται το username και επιστρέφει το user group του. Αλλιώς false
    public function getUserGroup($username) {
        self::CreateConnection();

        $sql='SELECT user_group FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=$item['user_group'];
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δέχεται το user id και επιστρέφει το πλήρες όνομα του. Αλλιώς false
    public function getUserName($id) {
        self::CreateConnection();

        $sql='SELECT fname, lname FROM user_details WHERE user_id=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($id));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=$item['fname'].' '.$item['lname'];
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
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

    // Αλλάζει το $value ενός $option
    public function changeOption ($option, $value) {
        $crypto = new Crypto();
        self::CreateConnection();

        $sql = 'UPDATE options SET option_value=? WHERE option_name=?';
        $stmt = RoceanDB::$conn->prepare($sql);

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
    public function createOption ($option, $value, $setting, $encrypt) {
        self::CreateConnection();
        $crypto = new Crypto();

        $sql = 'INSERT INTO options (option_name, option_value, setting, encrypt) VALUES(?,?,?,?)';
        $stmt = RoceanDB::$conn->prepare($sql);

        if($encrypt==1)
            $value= $crypto->EncryptText($value);

        if($stmt->execute(array($option, $value, $setting, $encrypt)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ανάγνωση ενός option
    public function getOption ($option) {
        $crypto = new Crypto();
        self::CreateConnection();

        $sql = 'SELECT encrypt,option_value FROM options WHERE option_name=?';
        $stmt = RoceanDB::$conn->prepare($sql);

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
    public function getOptionEncrypt ($option) {
        self::CreateConnection();

        $sql = 'SELECT encrypt FROM options WHERE option_name=?';
        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($option));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['encrypt'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Καθαρίζει τα options από διπλοεγγραφές
    static function clearOptions() {
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

    // μετράει τα rows ενός πίνακα
    static function countTable($table) {
        self::CreateConnection();

        $sql = 'SELECT * FROM '.$table;
        $stmt = RoceanDB::$conn->prepare($sql);
        

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

        $fieldsText=page::cutLastString($fieldsText,', '); // Κόβει την τελευταία ','



        $sql = 'UPDATE '.$table.' SET '.$fieldsText.' WHERE '.$condition;
        $stmt = RoceanDB::$conn->prepare($sql);

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
        foreach($someArray as $some)
            $newArray[]=$some[0];
        
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

    // Εισάγει μια εγγραφή στα logs
    static function insertLog ($message) {
        self::CreateConnection();

        $sql = 'INSERT INTO logs (message, ip, user_name, log_date, browser) VALUES(?,?,?,?,?)';
        $stmt = self::$conn->prepare($sql);

        $ip=$_SERVER['REMOTE_ADDR'];  // H ip του χρήστη
        $user_name=self::getSession('username');        // το όνομα του χρήστη
        $log_date=date('Y-m-d H:i:s');
        $ua=self::getBrowser();  // Τραβάει τις πληροφορίες για τον browser και το σύστημα του χρήστη

        $browser=$ua['name'] . " " . $ua['version'] . " on " .$ua['platform'];

        if($stmt->execute(array($message, $ip, $user_name, $log_date, $browser)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
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

        $sql = 'INSERT INTO '.$table.' ('.$fields.') '.$query;
        $stmt = self::$conn->prepare($sql);

        if(self::deleteTable($table)) { // πρώτα σβήνει τα τρέχοντα περιεχόμενα του $table
        
            if($stmt->execute($arrayParams))

                $result=true;

            else $result=false;

            $stmt->closeCursor();
            $stmt = null;

            return $result;
        }

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
    static function createTable($sql) {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $stmt = RoceanDB::$conn->prepare($sql);


        if($stmt->execute())

            $result = true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Ελέγχει αν υπάρχουν τα tables της βάσης και ότι δεν υπάρχει το δημιουργεί
    static function checkMySqlTables () {
        global $mySqlTables;  // To table με τα tables και τα creation strings

        foreach ($mySqlTables as $item) {  // Ελέγχει κάθε ένα table αν υπάρχει
            if(!self::checkIfTableExist($item['table'])) {
                self::createTable($item['sql']); // αν δεν υπάρχει το δημιουργεί
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