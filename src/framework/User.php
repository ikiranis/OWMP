<?php
/**
 *
 * File: User.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 29/03/17
 * Time: 23:11
 *
 * Κλάση που κάνει την διαχείρηση των χρηστών
 *
 */

namespace apps4net\framework;


class User extends MyDB
{

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

                Logs::insertLog('User Login'); // Προσθήκη της κίνησης στα logs

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

        if($inserted_id=$this->insertInto($sql, $arrayParams)) {
            $sql = 'INSERT INTO salts(user_id, salt, algo, cost) VALUES(?,?,?,?)';   // Εισάγει στον πίνακα salts

            $saltArray = array($inserted_id, $hashed_array['salt'], $hashed_array['algo'], $hashed_array['cost'] );

            $this->insertInto($sql, $saltArray);

            $sql = 'INSERT INTO user_details(user_id, fname, lname) VALUES(?,?,?)';  // Εισάγει στον πίνακα user_details

            $detailsArray = array($inserted_id, $fname, $lname);

            $this->insertInto($sql, $detailsArray);

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

                $userSalt=User::getSaltForUser($item['user_id']);

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

}