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

        self::createConnection();

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

        self::createConnection();

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

        } else {
            return false;
        }

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

    // Κάνει logout
    public function logout() {
        // remove all session variables
        session_unset();

        // destroy the session
        session_destroy();

        // unset cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-1000, PROJECT_PATH);
            }
        }

        header('Location:index.php');
    }

    // Εμφάνιση οθόνης για login
    public function showLoginWindow()
    {

        $LoginWindow = new Page();
        $lang=new Language();

        ?>
<!--        <main>-->
            <div class="row h-100 fixed-top bg-light">

                <div id="LoginWindow" class="col-lg-5 col-11 mx-auto my-auto text-center py-4 bg-warning">

                    <?php

                    ?>

                        <form id="LoginForm" name="LoginForm">

                            <div class="form-group w-100">
                                <label for="username" class="sr-only"><?php echo __('form_user_name'); ?></label>
                                <input type="text" class="form-control" id="username" name="username"
                                       maxlength="15" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$"
                                       title="<?php echo __('valid_username'); ?>"
                                       placeholder="<?php echo __('form_user_name'); ?>" required>
                            </div>

                            <div class="form-group w-100">
                                <label for="password" class="sr-only"><?php echo __('form_password'); ?></label>
                                <input type="password" class="form-control" id="password" name="password"
                                       maxlength="15" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                       title="<?php echo __('valid_username'); ?>"
                                       placeholder="<?php echo __('form_password'); ?>" required>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="SavePassword" name="SavePassword"
                                       value="yes">
                                <label for="SavePassword" class="form-check-label"><?php echo __('form_save_password'); ?></label>
                            </div>

                            <input type="button" class="btn btn-dark w-100" id="submit" name="submit" onclick="login();"
                                   value="<?php echo __('form_login'); ?>" >

                        </form>

                    <?php

                    $languages_text=$lang->print_languages('lang_id',' ',true,false);

                    ?>

                    <div id="languages" class="py-2">
                        <?php echo $languages_text; ?>
                    </div>

                </div>

                <div id="error_container">
                    <div class="alert_error bgc9"></div>
                </div>

            </div>

        </div>

        </body>
        </html>

<!--        </main>-->

        <?php

    }

    // Εμφάνιση οθόνης για εγγραφή χρήστη
    public function ShowRegisterUser()
    {
        $RegisterUserWindow = new Page();
        $lang=new Language();

        ?>
        <div  class="row h-100 fixed-top bg-light">

             <div id="RegisterUserWindow" class="col-3 mx-auto my-auto  text-center py-4 bgc2">

                <?php

                $FormElementsArray = array(
                    array('name' => 'username',
                        'className' => 'bgc3',
                        'fieldtext' => __('form_user_name'),
                        'type' => 'text',
                        'onclick' => null,
                        'required' => 'yes',
                        'maxlength' => '15',
                        'pattern' => '^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$',
                        'title' => __('valid_username'),
                        'disabled' => 'no',
                        'value' => null),
                    array('name' => 'email',
                        'className' => 'bgc3',
                        'fieldtext' => __('form_email'),
                        'type' => 'email',
                        'onclick' => null,
                        'required' => 'yes',
                        'maxlength' => '50',
                        'title' => __('valid_email'),
                        'disabled' => 'no',
                        'value' => null),
                    array('name' => 'password',
                        'className' => 'bgc3',
                        'fieldtext' => __('form_password'),
                        'type' => 'password',
                        'onclick' => null,
                        'required' => 'yes',
                        'maxlength' => '15',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                        'title' => __('valid_password'),
                        'disabled' => 'no',
                        'value' => null),
                    array('name' => 'repeat_password',
                        'className' => 'bgc3',
                        'fieldtext' => __('form_repeat_password'),
                        'type' => 'password',
                        'onclick' => null,
                        'required' => 'yes',
                        'maxlength' => '15',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                        'title' => __('valid_password'),
                        'disabled' => 'no',
                        'value' => null),
                    array('name' => 'register',
                        'className' => 'bgc5 c2',
                        'fieldtext' => '',
                        'type' => 'button',
                        'onclick' => 'registerUser();',
                        'required' => 'no',
                        'maxlength' => '',
                        'pattern' => '',
                        'title' => '',
                        'disabled' => 'no',
                        'value' => __('form_register'))
                );

                $RegisterUserWindow->MakeForm('RegisterForm', $FormElementsArray, false);

                $languages_text=$lang->print_languages('lang_id',' ',true,false);

                ?>
                <div id="languages" class="py-2">
                    <?php echo $languages_text; ?>
                </div>

            </div>

        </div>

        </div>

        </body>
        </html>

        <?php
    }


}