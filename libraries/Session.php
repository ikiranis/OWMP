<?php
/**
 * File: Session.php
 * Created by rocean
 * Date: 21/04/16
 * Time: 20:14
 * Sessions handler class
 * Based on Source code and more info from http://php.net/manual/en/function.session-set-save-handler.php
 */


class SysSession implements SessionHandlerInterface
{

 
    public function open($savePath, $sessionName)
    {

        $conn = new RoceanDB();
        $conn->CreateConnection();

        if($conn){
            return true;
        }else{
            return false;
        }
    }

    public function close()
    {
        return true;
        
    }

    public function read($id)
    {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql='SELECT Session_Data FROM Session WHERE Session_Id = ? AND Session_Time > ?';


        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($id,date('Y-m-d H:i:s')));

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            return $row['Session_Data'];

        }else{
            return "";

        }
    }

    public function write($id, $data)
    {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $DateTime = date('Y-m-d H:i:s');
        $NewDateTime = date('Y-m-d H:i:s',strtotime($DateTime.' + 30 minutes'));
        
        

            $sql='REPLACE INTO Session (Session_Id, Session_Time, Session_Data) VALUES (?,?,?)';

            $stmt = RoceanDB::$conn->prepare($sql);


            $stmt->execute(array($id,$NewDateTime,$data));


        if($stmt){
            return true;
        }else{
            return false;
        }

        $stmt->closeCursor();
        $stmt = null;
    }

    public function destroy($id)
    {

        $conn = new RoceanDB();
        $conn->CreateConnection();


        $sql='DELETE FROM Session WHERE Session_Id =?';

        $stmt = RoceanDB::$conn->prepare($sql);


        $stmt->execute(array($id));



        if($stmt){
            return true;
        }else{
            return false;
        }

        $stmt->closeCursor();
        $stmt = null;
    }

    public function gc($maxlifetime)
    {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql='DELETE FROM Session WHERE ((UNIX_TIMESTAMP(Session_Time)+?) < ?)';

        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($maxlifetime,time()));


        if($stmt){
            return true;

        }else{
            return false;

        }

        $stmt->closeCursor();
        $stmt = null;
    }

 

}

/**
 * Garbage Collector
 * @see session.gc_divisor      100
 * @see session.gc_maxlifetime 1440
 * @see session.gc_probability    1
 * @usage execution rate 1/100
 *        (session.gc_probability/session.gc_divisor)
 * Πιθανότητα για να τρέξει η gc()
 */

ini_set('session.gc_maxlifetime',60);
ini_set('session.gc_divisor',100);
ini_set('session.gc_probability',100);

$handler = new SysSession();
session_set_save_handler($handler, true);




