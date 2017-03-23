<?php

/**
 * File: index.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 */

use apps4net\framework\Language;
use apps4net\framework\RoceanDB;
use apps4net\framework\Page;

require_once ('libraries/common.inc.php');

session_start();

$lang=new Language();

$phrasesForJavascript=json_encode($lang->getPhrasesTable());

// έλεγχος αν έχει πατηθεί link για αλλαγής της γλώσσας
if (isset($_GET['ChangeLang'])) {
    $targetPage='Location:index.php';

    $lang->change_lang($_GET['ChangeLang']);

    header($targetPage);
}


require_once ('login.php');
require_once ('MainPage.php');


RoceanDB::checkMySqlTables(); // Έλεγχος των tables στην βάση

$MainPage = new Page();

// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('libraries/javascript/framework/jquery.min.js',   // jquery
    'libraries/javascript/framework/scripts.js',    // my scripts
    // TODO να φύγει το polyfill κάποια στιγμή που θα το υποστηρίζουν κανονικά όλοι οι browsers
    'libraries/javascript/framework/details.js',    // polyfill για το summary/details
    'libraries/javascript/framework/jquery.validate.min.js',      // extension του jquery για form validation
    'libraries/javascript/framework/nodep-date-input-polyfill.dist.js', // date input type polyfill. https://github.com/brianblakely/nodep-date-input-polyfill
    'libraries/javascript/framework/pattern.js');   // extension για το validate. ενεργοποιεί το validation των patterns



if (!isset($_GET['mobile'])) {
    $css = array('styles/basic.css', 'styles/main.css');
    $_SESSION['mobile']=false;
} else {
    $css = array('styles/mobile.css', 'styles/main.css');
    $_SESSION['mobile']=true;
}

$MainPage->setScript($scripts);
$MainPage->setCSS($css);

$MainPage->showHeader();

// Αποθηκεύει την IP σε session για τις περιπτώσεις που αλλάζει συνέχεια η IP του χρήστη (π.χ. σε 3g network)
if(!isset($_SESSION['user_IP'])) {
    $_SESSION['user_IP'] = $_SERVER['REMOTE_ADDR'];
}


if (isset($_GET['logout'])) {
    RoceanDB::insertLog('User Logout'); // Προσθήκη της κίνησης στα logs
    logout();

    // TODO αν πας να κάνεις logout αμέσως μόλις μπεις, δεν κάνει

}

// δημιουργεί έναν μοναδικό αριθμό που χρησιμοποιείται στην υπόλοιπη εφαρμογή σαν tab id
define('TAB_ID', date('YmdHis'));

// Περνάει βασικές μεταβλητές στην javascript
?>

    <input name='tabID' id='tabID' type='hidden' value='<?php echo TAB_ID; ?>'>

    <script type="text/javascript">

        var AJAX_path="<?php echo AJAX_PATH; ?>";  // ο κατάλογος των AJAX files
        var DIR_PREFIX="<?php echo DIR_PREFIX; ?>";
        var Album_covers_path="<?php echo ALBUM_COVERS_DIR; ?>";
        var WebFolderPath="<?php echo WEB_FOLDER_PATH; ?>";
        var ParrotVersionFile="<?php echo PARROT_VERSION_FILE; ?>";
        var AppVersion="<?php echo APP_VERSION; ?>";
        var changeLogUrl="<?php echo CHANGE_LOG_URL; ?>";
        var TimePercentTrigger=parseInt(<?php echo PLAY_PERCENTAGE; ?>);

        // Τα κείμενα του site παιρνούνται στην javascript
        var phrases=<?php echo $phrasesForJavascript; ?>;

        // Το id του τρέχοντος tab
        var tabID=document.querySelector('#tabID').value;

    </script>

<?php


$logged_in=false;

// Έλεγχος αν υπάρχει cookie. Αν δεν υπάρχει ψάχνει session
if(!$conn->CheckCookiesForLoggedUser()) {
    if (RoceanDB::checkIfUserIsLegit())
    {
        $userName=$conn->getSession('username');
        
        $LoginNameText= '<img id="account_image" src="img/account.png"> <span id="account_name">'.$userName.'</span>';
//        session_regenerate_id(true);
        
        $logged_in=true;

    }
}
else {
    $userName = RoceanDB::getACookie('username');

    $LoginNameText = '<img id="account_image" src="img/account.png"> <span id="account_name">' . $userName . '</span>';
    $logged_in = true;


    if (!isset($_SESSION["username"]))
        $conn->setSession('username', $userName);
}


// Αν είναι login κάποιος χρήστης
if($logged_in) {
    $LoginNameText .= ' <span id=logout><a href=?logout=true title=' . __('logout') . '><img src=img/exit.png></a></span>';

    $timediv='<div id=SystemTime><img src=img/time.png><span id="timetext"></span></div>';
    
    $MainPage->showMainBar($timediv, $LoginNameText);

    // Αν η σελίδα δεν έχει τρέξει την τελευταία μέρα
    if(Page::checkNewPageRunning()) {
        if(!RoceanDB::checkMySQLEventScheduler()) {   // Αν δεν είναι ενεργοποιημένος ήδη ο event scheduler
            RoceanDB::enableMySQLEventScheduler();   // Ενεργοποιεί τα scheduler events στην mysql
        }
    }


    DisplayMainPage();

    $MainPage->showFooter(true,true,true);

}

// Αν δεν είναι login κάποιος χρήστης
if(!$logged_in) {
    if ($conn->CheckIfThereIsUsers())
        showLoginWindow();
    else ShowRegisterUser();
}



// Δημιουργεί event που σβήνει logs που είναι παλιότερα των 30 ημερών και τρέχει κάθε μέρα
//$eventQuery='DELETE FROM logs WHERE log_date<DATE_SUB(NOW(), INTERVAL 30 DAY)';
//RoceanDB::createMySQLEvent('logsManage', $eventQuery, '1 DAY');