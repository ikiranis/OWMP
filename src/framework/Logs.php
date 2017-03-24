<?php
/**
 *
 * File: Logs.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 24/03/17
 * Time: 21:38
 *
 * Διαχείριση των logs
 *
 */

namespace apps4net\framework;

class Logs
{

    // Εισάγει μια εγγραφή στα logs
    // @param string $message: Το μήνυμα που θα προστεθεί στα logs
    // #return bool: True or false
    static function insertLog ($message) {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql = 'INSERT INTO logs (message, ip, user_name, log_date, browser) VALUES(?,?,?,?,?)';
        $stmt = MyDB::$conn->prepare($sql);

        $ip=$_SERVER['REMOTE_ADDR'];  // H ip του χρήστη
        $user_name=$conn->getSession('username');        // το όνομα του χρήστη
        $log_date=date('Y-m-d H:i:s');
        $ua=Utilities::getBrowser();  // Τραβάει τις πληροφορίες για τον browser και το σύστημα του χρήστη

        $browser=$ua['name'] . " " . $ua['version'] . " on " .$ua['platform'];

        if($stmt->execute(array($message, $ip, $user_name, $log_date, $browser)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

}