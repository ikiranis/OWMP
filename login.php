<?php

/**
 * File: login.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 */

require_once ('libraries/common.inc.php');


$lang = new Language();




//if (isset($_POST['register'])) {
//
//    $conn = new RoceanDB();
//
//    // Έλεγχος αν συμφωνούν τα 2 passwords
//    if($_POST['password']==$_POST['repeat_password']) {
//        if($conn->CreateUser(ClearString($_POST['username']), ClearString($_POST['email']), ClearString($_POST['password']), 'local')) { // Δημιουργεί τον χρήστη
//            echo '<p>' . __('register_with_success') . '</p>';
//        }
//    }
//    else echo '<p>'.__('not_the_same_password').'</p>';
//
//
//
//}


function logout() {
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

// Εμφάνιση επιλογών login
function showLoginWindow()
{

    $LoginWindow = new Page();
    $lang=new Language();



    ?>
    <main>
    <div id="LoginWindow">


        <?php


        $FormElementsArray = array(
            array('name' => 'username',
                'fieldtext' => __('form_user_name'),
                'type' => 'text',
                'onclick' => null,
                'required' => 'yes',
                'maxlength' => '15',
                'pattern' => '^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$',
                'title' => __('valid_username'),
                'disabled' => 'no',
                'value' => null),
            array('name' => 'password',
                'fieldtext' => __('form_password'),
                'type' => 'password',
                'onclick' => null,
                'required' => 'yes',
                'maxlength' => '15',
                'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                'title' => __('valid_password'),
                'disabled' => 'no',
                'value' => null),
            array('name' => 'SavePassword',
                'fieldtext' => __('form_save_password'),
                'type' => 'checkbox',
                'onclick' => null,
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => 'yes'),
            array('name' => 'submit',
                'fieldtext' => '',
                'type' => 'button',
                'onclick' => 'login();',
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => __('form_login'))
        );

        $LoginWindow->MakeForm('LoginForm', $FormElementsArray, false);
        
        $languages_text=$lang->print_languages('lang_id',' ',true,false);

        ?>

        <div id="languages">
            <?php echo $languages_text; ?>
        </div>


    </div>



    </main>

    <div id="error_container">
        <div id="alert_error"></div>
    </div>

    <?php


}

function ShowRegisterUser()
{
    $RegisterUserWindow = new Page();
    $lang=new Language();


    ?>
    <main>
    <div id="RegisterUserWindow">


        <?php


        $FormElementsArray = array(
            array('name' => 'username',
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
                'fieldtext' => __('form_email'),
                'type' => 'email',
                'onclick' => null,
                'required' => 'yes',
                'maxlength' => '50',
                'title' => __('valid_email'),
                'disabled' => 'no',
                'value' => null),
            array('name' => 'password',
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
        <div id="languages">
            <?php echo $languages_text; ?>
        </div>


    </div>


    </main>


    <?php


}

function DisplayUsers ()
{

    $conn = new RoceanDB();
    $conn->CreateConnection();
    
    $sql = 'SELECT * FROM user';
    
    $stmt = RoceanDB::$conn->prepare($sql);
    
    $stmt->execute();
    
    ?>
        <div id="UsersList">

                <div class="row">
                    <div class="userID">ID</div>
                    <div class="username">Username</div>
                    <div class="email">email</div>
                </div><br>

    
    <?php
    
    while($row=$stmt->fetch(PDO::FETCH_ASSOC))
    {
    ?>

            <div class="row">
                <div class="userID"><?php echo $row['user_id']; ?></div>
                <div class="username"><?php echo $row['username']; ?></div>
                <div class="email"><?php echo $row['email']; ?></div>
            </div><br>

        </div>
    <?php
    }

    $stmt->closeCursor();
    $stmt = null;


}
