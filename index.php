<?php

/**
 * File: routing.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 */

use apps4net\framework\Language;
use apps4net\framework\MyDB;
use apps4net\framework\User;
use apps4net\framework\Page;
use apps4net\framework\Logs;
use apps4net\framework\Utilities;

require_once('src/boot.php');

session_start();

MyDB::checkMySqlTables(); // Έλεγχος των tables στην βάση
MyDB::checkMySqlForTypeChanges(); // Έλεγχος για αλλαγμένα πεδία στην βάση

$lang = new Language();

// Έλεγχος αν τρέχουν τα routing rules, αναλόγως τον web server
Utilities::checkWebServerForRoutingRules();

$phrasesForJavascript = json_encode($lang->getPhrasesTable());

// έλεγχος αν έχει πατηθεί link για αλλαγής της γλώσσας
if (isset($_GET['ChangeLang'])) {
    $targetPage='Location:index.php';

    $lang->change_lang($_GET['ChangeLang']);

    header($targetPage);
}

$MainPage = new Page();
$user = new User();

// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('https://code.jquery.com/jquery-3.2.1.min.js',   // jquery
    'src/javascript/framework/variables.js',    // Javascript public variables
    'src/javascript/framework/_jqueryExtensions.js',    // jQuery extensions
    'src/javascript/framework/_utilities.js',    // Utility functions
    'src/javascript/framework/_forms.js',    // Forms functions
    'src/javascript/framework/_users.js',    // Users management functions
    'src/javascript/framework/scripts.js',    // framework scripts
    'src/javascript/app/scripts.js',    // app scripts
    'src/javascript/app/_shortcuts.js',       // Shortcut actions
    'src/javascript/app/_video.js',       // Video element management
    'src/javascript/app/_youtube.js',       // Youtube downloading
    'src/javascript/app/_progressAnimation.js',  // Το progress animation
    'src/javascript/app/_uploadFiles.js',       // Uploading αρχείων
    // TODO να φύγει το polyfill κάποια στιγμή που θα το υποστηρίζουν κανονικά όλοι οι browsers
    'src/javascript/framework/details.js',    // polyfill για το summary/details
    'src/javascript/framework/jquery.validate.min.js',      // extension του jquery για form validation
    'src/javascript/framework/nodep-date-input-polyfill.dist.js', // date input type polyfill. https://github.com/brianblakely/nodep-date-input-polyfill
    'src/javascript/framework/pattern.js');   // extension για το validate. ενεργοποιεί το validation των patterns


$css = array('styles/layouts/basic.css');
$_SESSION['mobile']=false;

// Έλεγχος αν είναι σε mobile ή όχι
//if (!isset($_GET['mobile'])) {
//    $css = array('styles/layouts/basic.css');
//    $_SESSION['mobile']=false;
//} else {
//    $css = array('styles/layouts/mobile.css');
//    $_SESSION['mobile']=true;
//}

$MainPage->setScript($scripts);
$MainPage->setCSS($css);

$MainPage->showHeader();

// Αποθηκεύει την IP σε session για τις περιπτώσεις που αλλάζει συνέχεια η IP του χρήστη (π.χ. σε 3g network)
if(!isset($_SESSION['user_IP'])) {
    $_SESSION['user_IP'] = $_SERVER['REMOTE_ADDR'];
}


if (isset($_GET['logout'])) {
    Logs::insertLog('User Logout'); // Προσθήκη της κίνησης στα logs
    $user->logout();
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
        var ParrotVersionFile="<?php echo APP_VERSION_FILE; ?>";
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
if(!$user->CheckCookiesForLoggedUser()) {
    if (User::checkIfUserIsLegit())
    {
        $userName=$conn->getSession('username');
        
        $LoginNameText= '<img id="account_image" src="img/account.png"> <span id="account_name">'.$userName.'</span>';
//        session_regenerate_id(true);
        
        $logged_in=true;

    }
}
else {
    $userName = MyDB::getACookie('username');

    $LoginNameText = '<img id="account_image" src="img/account.png"> <span id="account_name">' . $userName . '</span>';
    $logged_in = true;

    if (!isset($_SESSION["username"]))
        $conn->setSession('username', $userName);
}


// Αν είναι login κάποιος χρήστης
if($logged_in) {
    $LoginNameText .= ' <span id=logout><a href=?logout=true title=' . __('logout') . '><img src=img/exit.png></a></span>';

//    $timediv = '<span id="SystemTime"><span id="timetext"></span></span>';

    $MainPage->showMainBar($LoginNameText);

    // Αν η σελίδα δεν έχει τρέξει την τελευταία μέρα
    if(Page::checkNewPageRunning()) {
        if(!MyDB::checkMySQLEventScheduler()) {   // Αν δεν είναι ενεργοποιημένος ήδη ο event scheduler
            MyDB::enableMySQLEventScheduler();   // Ενεργοποιεί τα scheduler events στην mysql
        }
    }

    $MainPage->DisplayMainPage();

    $MainPage->showFooter(true,true,true);

}

// Αν δεν είναι login κάποιος χρήστης
if(!$logged_in) {
    if ($user->CheckIfThereIsUsers())
        $user->showLoginWindow();
    else $user->ShowRegisterUser();
}

// Δημιουργεί event που σβήνει logs που είναι παλιότερα των 30 ημερών και τρέχει κάθε μέρα
//$eventQuery='DELETE FROM logs WHERE log_date<DATE_SUB(NOW(), INTERVAL 30 DAY)';
//MyDB::createMySQLEvent('logsManage', $eventQuery, '1 DAY');