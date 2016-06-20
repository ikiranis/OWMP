<?php

/**
 * File: Crypto.php
 * Created by rocean
 * Date: 04/05/16
 * Time: 23:04
 * Class for Crypto Methods
 * Info για την κρυπτογράφηση στην σελίδα http://php.net/manual/en/faq.passwords.php
 * Info για το openssl_encrypt http://blog.turret.io/the-missing-php-aes-encryption-example/
 */

class Crypto
{
    public static $KeyForPasswords='ckY85^8nL%W4U5&38Zb0';

    private static $CryptoCost;


    // Υπολογίζει το cost για την κρυπτογράφηση
    function FindCost () {
        $timeTarget = 0.05; // 50 milliseconds 

        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        self::$CryptoCost=$cost;
    }

    // Κρυπτογραφεί ένα password προστέθοντας σε αυτό και ένα έξτρα key self::$KeyForPasswords
    function EncryptPassword ($password) {

        // Υπολογίζει το cost για την δυσκολία της κρυπτογράφησης αναλόγως το μηχάνημα
        $cost=self::$CryptoCost;
        $options = ['cost' => $cost];

        // Προσθέτει στο password ακόμη ένα string
        $HashThePassword=$password.self::$KeyForPasswords;


        // Μετατρέπει το password σε hash
        $password_hashed=password_hash($HashThePassword, PASSWORD_BCRYPT, $options);
        
        // Σπάει το hashed password στα 4 κομμάτια του
        $password_algo = substr($password_hashed, 0, 4);
        $password_cost = substr($password_hashed, 4, 3);
        $salt = substr($password_hashed, 7, 22);
        $hashed_password = substr($password_hashed, 29, 31);

        // Επιστρέφει τα 4 κομμάτια σε ένα array για να τα επιστρέψει πίσω η method
        $hashed_array = array('algo'=>$password_algo, 'cost'=>$password_cost, 'salt'=>$salt, 'hashed_password'=>$hashed_password);

        return $hashed_array;
    }


    // Κρυπτογραφεί οποιοδήποτε text και επιστρέφει το κρυπτογραφημένο hash
    function EncryptText ($text) {
        $encryption_key = hash('md5',self::$KeyForPasswords); // hashing του $KeyForPasswords
//        echo '<p>Hash key: '.$encryption_key.'      '.strlen($encryption_key).'</p>';
        $iv = bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')));   // Δημιουργία τυχαίου $iv
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', $encryption_key, 0, $iv);   // Δημιουργεί το encrypted text
//        echo '<p>iv:'.$iv.'</p>';

        $encrypted = $encrypted .':'.$iv;   //προσθέτει μια άνω κάτω τελεία και το $iv στο τέλος του encrypted text

        return $encrypted; // Επιστρέφει το κρυπτογραφημένο text


    }

    // Αποκρυπτογράφηση του κειμένο. Επιστρέφει το αποκρυπτογραφημένο
    function DecryptText ($text) {
        $encryption_key = hash('md5',self::$KeyForPasswords); // hashing του $KeyForPasswords

        $parts = explode(':', $text); // Σπάει το encrypted text στα 2 για να πάρει το $iv να κάνει την αποκρυπτογράφηση
//        echo $parts[0].' '.$parts[1];
        $DecryptedText = openssl_decrypt($parts[0], 'aes-256-cbc', $encryption_key, 0, $parts[1]);  // Αποκρυπτογραφεί το text
//        echo '<p>Decrypted: '.$DecryptedText.'</p>';
        return $DecryptedText; // Επιστρέφει το αποκρυπτογραφημένο κείμενο
    }
    


}

$crypt = new Crypto();
$crypt->FindCost();
