<?php

/**
 * File: RoceanDB.php
 * Created by rocean
 * Date: 17/04/16
 * Time: 01:17
 * DB Class
 */


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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
            return true;
        else return false;

        $stmt->closeCursor();
        $stmt = null;

    }

    // Ελέγχει αν ο χρήστης υπάρχει στην βάση και είναι σωστά τα username, password που έχει δώσει
    function CheckLogin($username, $password, $SavePassword) {

        $this->CreateConnection();

        $sql='SELECT * FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));



        // Αν ο χρήστης username βρεθεί. Αν υπάρχει δηλαδή στην βάση μας
        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            $crypto = new Crypto();

            // Προσθέτει το string στο password που έδωσε ο πιθανός χρήστης
            $HashThePassword=$password.Crypto::$KeyForPasswords;

            $sql='SELECT * FROM salts WHERE user_id=?';
            $salt = self::$conn->prepare($sql);
            $salt->execute(array($item['user_id']));

            // Φέρνει το salt από τον πίνακα salts για τον συγκεκριμένο χρήστη. Ενώνει τα 4 κομμάτια του hashed password
            // που είχαμε σπάσει στο αρχικό του ενιαίο
            if($salt_item=$salt->fetch(PDO::FETCH_ASSOC)) {
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
                    setcookie('username', $item['username'], time()+self::$CookieTime, PROJECT_PATH);
                    setcookie('salt', $user_salt, time()+self::$CookieTime, PROJECT_PATH);

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

    // Έλεγχος αν ο χρήστης είναι logged id, αν υπάρχουν cookies. Η function επιστρέφει true or false
    function CheckCookiesForLoggedUser() {

        if (isset($_COOKIE['username'])) {

            self::CreateConnection();

            // Ψάχνουμε να βρούμε το id του user με το συγκεκριμένο username που έχει στο cookie
            $sql='SELECT user_id FROM user WHERE username=?';

            $stmt = self::$conn->prepare($sql);

            $stmt->execute(array($_COOKIE['username']));

            // Αν βρεθεί το id του user ψάχνουμε τα salt του
            if($item=$stmt->fetch(PDO::FETCH_ASSOC))
            {

                $sql='SELECT salt FROM salts WHERE user_id=?';
                $salt = self::$conn->prepare($sql);
                $salt->execute(array($item['user_id']));

                // Παίρνουμε το salt και το συγκρίνουμε με αυτό που έχει το σχετικό cookie
                if($salt_item=$salt->fetch(PDO::FETCH_ASSOC)) {
                    if($salt_item['salt']==$_COOKIE['salt']) {
//                        $crypto= new Crypto();
                        self::setSession('username',$_COOKIE['username']);  // Ανοίγει το session για τον συγκεκριμένο χρήστη
//                        $_SESSION['username']=$crypto->EncryptText($_COOKIE['username']);
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
                self::$conn = new PDO(self::$connStr, self::$DBuser, self::$DBpass,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            } catch (PDOException $pe) {
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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
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
    static function getTableArray ($table, $fields, $condition, $ParamsArray, $orderBy) {
        
        self::CreateConnection();

        if(!isset($fields)) $sql = 'SELECT * FROM '.$table;
        else $sql = 'SELECT '.$fields.' FROM '.$table;

        if(isset($condition))
            $sql=$sql.' WHERE '.$condition;
        
        if(isset($orderBy))
            $sql=$sql.' ORDER BY '.$orderBy;


        $stmt = self::$conn->prepare($sql);

        if(isset($ParamsArray))
            $stmt->execute($ParamsArray);
        else $stmt->execute();


        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    // Δέχεται το username και επιστρέφει το user group του. Αλλιώς false
    public function getUserGroup($username) {
        self::CreateConnection();

        $sql='SELECT user_group FROM user WHERE username=?';

        $stmt = self::$conn->prepare($sql);

        $stmt->execute(array($username));

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
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

        $sql = 'INSERT INTO options (option_name, option_value, setting, encrypt) VALUES(?,?,?,?)';
        $stmt = RoceanDB::$conn->prepare($sql);

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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))

            $result=$item['option_value'];

        else $result=false;


        if($item['encrypt']==1)
            $result= $crypto->DecryptText($result);

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

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))

            $result=$item['encrypt'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // μετράει τα rows ενός πίνακα
    public function countTable($table) {
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
    // $fields και $values σε array
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

}

