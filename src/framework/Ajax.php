<?php
/**
 *
 * File: Ajax.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 27/09/2017
 * Time: 17:39
 *
 * Όλες οι ajax μέθοδοι του framework
 *
 */

namespace apps4net\framework;

use apps4net\parrot\app\OWMP;
use apps4net\parrot\app\SyncFiles;

class Ajax extends Controller
{

    /**
     * Παίρνει backup της βάσης
     */
    public function backupDatabase()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        global $backupTables;

        // H λίστα με τις manual playlists
        $manualPlaylists = MyDB::clearArray(MyDB::getTableArray('manual_playlists', 'table_name', null, null, null, null, null));

        // Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup. Ενώνει τα 2 παραπάνω arrays
        $backup = new BackupDB();
        $backup->tables = array_merge($backupTables, $manualPlaylists);

        if ($backup->backupDatabase()) {
            $jsonArray = array('success' => true, 'filename' => $backup->createdFilename, 'fullPath' => $backup->createdFullPath);
            Logs::insertLog('Backup of the database with success'); // Προσθήκη της κίνησης στα logs
        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Ελέγχει αν ο χρήστης υπάρχει στην βάση κι επιστρέφει true or false
     */
    public function checkIfUserExists()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        if(isset($_GET['username']))
            $username=ClearString($_GET['username']);

        $user = new User();

        if ($user->checkIfUserExists($username)) {
            $jsonArray = array('success' => true);
        } else {
            $jsonArray=array( 'success'=>false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Ελέγχει την ώρα που πέρασε από το τελευταίο timestamp που καταχωρήθηκε
     * ώστε να ξέρει αν τρέχει ακόμη το script
     */
    public function checkLastMomentAlive()
    {
        Page::checkValidAjaxRequest(false);

        //Page::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
        $lastMomentAlive = Progress::getLastMomentAlive();  // παίρνει την τιμή του lastMomentAlive
        $progressInPercent = Progress::getPercentProgress(); // Το ποσοστό που βρίσκεται

        if(!$lastMomentAlive=='') { // Αν η τιμή δεν είναι κενό την υπολογίζουμε
            $TimeDifference = time() - $lastMomentAlive;

            //    trigger_error($TimeDifference);

            // Αν έχει να δώσει σημεία ζωής πάνω από 5 δευτερόλεπτα, τότε ο συγχρονισμός έχει λήξει
            // Ή αν είναι το ποσοστό 0
            if ($TimeDifference > 5 || ($progressInPercent==0  && !$lastMomentAlive=='') )
                $jsonArray = array('success' => false);
            else $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => true);  // Αν είναι κενό σημαίνει ότι τρέχει ακόμη τα πρώτα στάδια του
        // συγχρονισμού που δεν μπορεί να στείλει τιμές

        echo json_encode($jsonArray);
    }

    /**
     * Έλεγχος αν έχει γίνει σωστά το login
     */
    public function checkLogin()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        $user = new User();

        if(isset($_GET['username']))
            $username=ClearString($_GET['username']);

        if(isset($_GET['password']))
            $password=ClearString($_GET['password']);

        if (isset($_GET['SavePassword']))
            $SavePassword=$_GET['SavePassword'];

        $login=$user->CheckLogin($username, $password, $SavePassword);

        if($login['success']) {
            $jsonArray=array( 'success'=>true, 'message'=>$login['message']);
        } else {
            $jsonArray=array( 'success'=>false, 'message'=>$login['message']);
        }

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ελέγχει αν ένα url είναι video ή playlist
     */
    public function checkVideoURL()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['url'])) {
            $url = ClearString($_GET['url']);
        }

        $youtubeDL = new VideoDownload();

        $youtubeDL->videoURL = $url;

        // Ελέγχει αν είναι video ή playlist
        if($urlKind=$youtubeDL->checkURLkind()) {

            if ($urlKind == 'video') { // Αν είναι video
                $videoID = $youtubeDL->getYoutubeID();
                $jsonArray = array('success' => true, 'videoKind' => 'video', 'videoID' => $videoID);
            } else {  // Αν είναι playlist
                $playlistItems = $youtubeDL->getYoutubePlaylistItems();
                $jsonArray = array('success' => true, 'videoKind' => 'playlist', 'playlistItems' => $playlistItems);
            }
        }

        echo json_encode($jsonArray);
    }

    /**
     * Σβήνει εγγραφή στο user, user_details, salts
     */
    public function deleteUser()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        $conn = new MyDB();

        $deleteSalts=$conn->deleteRowFromTable ('salts','user_id',$id);
        $deletePlaylists=$conn->deleteRowFromTable ('manual_playlists','user_id',$id);
        $deleteSmartPlaylists=$conn->deleteRowFromTable ('smart_playlists','user_id',$id);
        $deleteUserDetails=$conn->deleteRowFromTable ('user_details','user_id',$id);

        if($deleteSalts==true && $deleteUserDetails==true && $deletePlaylists==true && $deleteSmartPlaylists==true){
            if($conn->deleteRowFromTable ('user','user_id',$id)) {
                $jsonArray = array('success' => 'true');

                Logs::insertLog('User deleted with id '.$id); // Προσθήκη της κίνησης στα logs
            }
            else {
                $jsonArray=array( 'success' => 'false');
            }
        } else {
            $jsonArray=array( 'success' => 'false');
        }

        echo json_encode($jsonArray);
    }

    /**
     * Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου
     */
    public function displayWindow()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['page']))
            $page=ClearString($_GET['page']);

        if(isset($_GET['offset']))
            $offset=ClearString($_GET['offset']);
        else $offset=0;

        if(isset($_GET['step']))
            $step=ClearString($_GET['step']);
        else $step=PLAYLIST_LIMIT;

        if(isset($_GET['search_text']))
            $search_text=ClearString($_GET['search_text']);

        switch ($page) {
            case 1: OWMP::showPlaylistWindow($offset,$step); break;
            case 2: OWMP::showConfiguration(); break;
            case 3: OWMP::showSynchronization(); break;
            case 4: OWMP::showLogs(); break;
            case 5: OWMP::showHelp(); break;
        }
    }

    /**
     * Καθαρίζει την βάση από προσωρινούς πίνακες που δεν χρησιμοποιούνται άλλο
     */
    public function garbageCollection()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['tabID']))
            $tabID=ClearString($_GET['tabID']);

        $tempUserPlaylist=CUR_PLAYLIST_STRING . $tabID;
        $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;

        // Ενημερώνει τον playlist_tables για το table $tempUserPlaylist με την ώρα που έγινε το access
        $theDate = date('Y-m-d H:i:s');
        MyDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempUserPlaylist));
        MyDB::updateTableFields('playlist_tables', 'table_name=?', array('last_alive'), array($theDate, $tempPlayedQueuePlaylist));

        $conn = new MyDB();

        $lastMinutes = strtotime('-30 minutes');
        $theDate = date('Y-m-d H:i:s', $lastMinutes);
        //trigger_error($theDate);
        $playlistTablesToDelete = MyDB::getTableArray('playlist_tables', 'table_name', 'last_alive<?', array($theDate), null, null, null);

        foreach ($playlistTablesToDelete as $item) {
            if(MyDB::checkIfTableExist($item['table_name'])) // Αν υπάρχει το σβήνουμε
                if(MyDB::dropTable($item['table_name'])) {
                    if($conn->deleteRowFromTable ('playlist_tables','table_name',$item['table_name']))
                        $jsonArray = array('success' => true);
                }
        }

//        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Παίρνει την τιμή του progress
     */
    public function getProgress()
    {
        Page::checkValidAjaxRequest(false);

        if($progressInPercent=Progress::getPercentProgress()) {
            $jsonArray = array('success' => true, 'progressInPercent' => $progressInPercent);
        }
        else {
            $jsonArray=array( 'success'=> false);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Κατεβάζει ένα βίντεο από το YouTube
     */
    public function getYouTube()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        set_time_limit(0);

        $youtubeDL = new VideoDownload();

        $youtubeDL->maxVideoHeight = MAX_VIDEO_HEIGHT;

        if(isset($_GET['id'])) {
            $youtubeDL->videoID = ClearString($_GET['id']);
        }

        if(isset($_GET['mediaKind'])) {
            $youtubeDL->mediaKind = ClearString($_GET['mediaKind']);
        }

        if($result = $youtubeDL->downloadVideo()) {
            // Εγγραφή στην βάση του τραγουδιού που κατέβηκε από το youtube
            $syncFile = new SyncFiles();
            DIR_PREFIX == '/' ?
                $file = $result :
                $file = str_replace(DIR_PREFIX, '', $result);
            $syncFile->file = $file;
            $syncFile->searchIDFiles = true;
            $syncFile->mediaKind = $youtubeDL->mediaKind;
            $syncFile->name = $youtubeDL->title;

            $syncFile->writeTrack();

            $jsonArray = array('success' => true, 'result' => $result, 'imageThumbnail' => $youtubeDL->imageThumbnail,
                'filesToDelete' => $syncFile->deleteFilesString);
        } else {
            $jsonArray=array( 'success'=> false, 'theUrl' => $youtubeDL->videoID);
        }

        echo json_encode($jsonArray);
    }

    /**
     * Εισαγωγή αρχικού χρήστη
     */
    public function registerUser()
    {
        session_start();

        Page::checkValidAjaxRequest(false);

        $user = new User();
        $lang = new Language();

        if(isset($_GET['username']))
            $username = ClearString($_GET['username']);

        if(isset($_GET['password']))
            $password = ClearString($_GET['password']);

        if (isset($_GET['email']))
            $email = ClearString($_GET['email']);

        // Ελέγχει αν υπάρχει admin χρήστης ήδη.
        if(!$user->CheckIfThereIsAdminUser()) {
            $register = $user->CreateUser($username, $email, $password, '1', 'local', null, null);

            if ($register) {
                $jsonArray = array('success' => true);
                Logs::insertLog('User ' . $username . ' registered'); // Προσθήκη της κίνησης στα logs
            } else {
                $jsonArray = array('success' => false);
            }

            // ελέγχει και εισάγει τις αρχικές τιμές στον πίνακα options
            //    Page::startBasicOptions();

            // Δημιουργεί event που σβήνει logs που είναι παλιότερα των 30 ημερών και τρέχει κάθε μέρα
            $eventQuery='DELETE FROM logs WHERE log_date<DATE_SUB(NOW(), INTERVAL 30 DAY)';
            MyDB::createMySQLEvent('logsManage', $eventQuery, '1 DAY');

            //Page::createCrontab(); // Προσθέτει τον demon στο crontab

        } else {
            $jsonArray = array('success' => false);
        }

        echo json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Κάνει restore της database
     */
    public function restoreDatabase()
    {
        session_start();

        global $restoreTables;

        if(file_exists(OUTPUT_FOLDER . TEMP_RESTORE_DATABASE_FILE)) {

            Page::checkValidAjaxRequest(true);

            // Θέτουμε το array με τα tables που θέλουμε να κάνουμε backup
            $backup = new BackupDB();
            $backup->tables = $restoreTables;

            $backup->sqlFilePath = OUTPUT_FOLDER;
            $backup->sqlFile = TEMP_RESTORE_DATABASE_FILE;

            Progress::updateRestoreRunning('1');

            if ($backup->restoreDatabase()) {
                $jsonArray = array('success' => true);
                Progress::updateRestoreRunning('0');
                unlink(OUTPUT_FOLDER . TEMP_RESTORE_DATABASE_FILE);  // Σβήνει το προσωρινό αρχείο
                Logs::insertLog('Restore database from backup file with success'); // Προσθήκη της κίνησης στα logs
            } else {
                $jsonArray = array('success' => false);
            }

        } else {
            $jsonArray = array('success' => false);
            trigger_error('DEN YPARXEI ARXEIO');
        }

        echo json_encode($jsonArray);
    }

    /**
     * Στέλνει kill command στο σχετικό πεδίο στην βάση
     */
    public function sendKillCommand()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(Progress::setKillCommand('1'))
            $jsonArray = array('success' => true);
        else $jsonArray = array('success' => false);

        echo json_encode($jsonArray);
    }

    /**
     * Κάνει update την εφαρμογή χρησιμοποιόντας το git
     */
    public function updateApp()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        //if(isset($_GET['tabID']))
        //    $tabID=ClearString($_GET['tabID']);

        $crypt = new Crypto();
        $sudoPass = $crypt->EncryptText('xxxxxxxx');

        if (Utilities::runGitUpdate($sudoPass)) {
            $jsonArray = array('success' => true);
        } else $jsonArray = array('success' => false);

        echo json_encode($jsonArray);
    }

    /**
     * Ενημερώνει μία εγγραφή στο options
     */
    public function updateOption()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['option_name']))
            $option_name=ClearString($_GET['option_name']);

        if(isset($_GET['option_value']))
            $option_value=ClearString($_GET['option_value']);

        $options = new Options();

        if($options->changeOption($option_name, $option_value)) {
            $jsonArray=array( 'success'=>'true');
            Logs::insertLog('Option '.$option_name.'changed'); // Προσθήκη της κίνησης στα logs
        } else {
            $jsonArray=array( 'success'=>'false');
        }

        echo json_encode($jsonArray);
    }

    /**
     * Ενημερώνει μια εγγραφή στους users ή κάνει νέα εγγραφή
     */
    public function updateUser()
    {
        session_start();

        Page::checkValidAjaxRequest(true);

        if(isset($_GET['id']))
            $id=ClearString($_GET['id']);

        if(isset($_GET['username']))
            $username=ClearString($_GET['username']);

        if(isset($_GET['email']))
            $email=ClearString($_GET['email']);

        if(isset($_GET['password']))
            $password=ClearString($_GET['password']);
        else $password=null;

        if(isset($_GET['usergroup']))
            $usergroup=ClearString($_GET['usergroup']);

        if(isset($_GET['fname']))
            $fname=ClearString($_GET['fname']);
        else $fname='';

        if(isset($_GET['lname']))
            $lname=ClearString($_GET['lname']);
        else $lname='';

        $user = new User();
        //MyDB::createConnection();

        if ($id==0) {  // Αν το id είναι 0 τότε κάνει εισαγωγή

            $IsUserExist=$user->checkIfUserExists($username);  // Ελέγχει αν χρήστης υπάρχει ήδη

            if(!$IsUserExist){
                if($inserted_id=$user->CreateUser($username, $email, $password, $usergroup, 'local', $fname, $lname)) { // Δημιουργεί τον χρήστη
                    $jsonArray = array('success' => true, 'lastInserted' => $inserted_id);
                    Logs::insertLog('User '.$username.' created'); // Προσθήκη της κίνησης στα logs
                }
                else $jsonArray=array( 'success'=>false);
            }
            else $jsonArray=array( 'success'=>false, 'UserExists'=>true);

        } else {   // αλλιώς κάνει update
            if($user->UpdateUser($id, $username, $email, $password, $usergroup, 'local', $fname, $lname)) {  // Ενημερώνει την εγγραφή
                $jsonArray = array('success' => true);
            }
        }

        echo json_encode($jsonArray);
    }

    /**
     * Επιστροφή true για να γίνει έλεγχος αν λειτουργεί το htaccess
     */
    public function checkHTaccess()
    {
        echo 'true';
    }

}