<?php

/**
 * File: index.php
 * Created by rocean
 * Date: 17/04/16
 * Time: 01:17
 */

require_once ('libraries/common.inc.php');

// έλεγχος αν έχει πατηθεί link για αλλαγής της γλώσσας
if (isset($_GET['ChangeLang'])) {
    $targetPage='Location:index.php';

    $lang->change_lang($_GET['ChangeLang']);

    header($targetPage);
}

session_start();

require_once ('login.php');
require_once ('MainPage.php');

RoceanDB::checkMySqlTables();

$MainPage = new Page();




if (isset($_GET['logout'])) {
    RoceanDB::insertLog('User Logout'); // Προσθήκη της κίνησης στα logs
    logout();

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

        // Τα κείμενα του site παιρνούνται στην javascript
        var phrases=<?php echo json_encode($lang->getPhrasesTable()); ?>;

        // Το id του τρέχοντος tab
        var tabID=document.querySelector('#tabID').value;

    </script>

<?php

// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('libraries/jquery.min.js',   // jquery
                'libraries/scripts.js',    // my scripts
    // TODO να φύγει το polyfill κάποια στιγμή που θα το υποστηρίζουν κανονικά όλοι οι browsers
                'libraries/details.js',    // polyfill για το summary/details
                'libraries/jquery.validate.min.js',      // extension του jquery για form validation
                'libraries/nodep-date-input-polyfill.dist.js', // date input type polyfill. https://github.com/brianblakely/nodep-date-input-polyfill
                'libraries/pattern.js');   // extension για το validate. ενεργοποιεί το validation των patterns



if (!isset($_GET['mobile'])) {
    $css = array('styles/basic.css', 'styles/main.css');
} else {
    $css = array('styles/mobile.css', 'styles/main.css');
}

$MainPage->setScript($scripts);
$MainPage->setCSS($css);

$MainPage->showHeader();

//$languages_text=$lang->print_languages('lang_id',' ',true,false);

$logged_in=false;

// Έλεγχος αν υπάρχει cookie. Αν δεν υπάρχει ψάχνει session
if(!$conn->CheckCookiesForLoggedUser()) {
    if (isset($_SESSION["username"]))
    {
        $LoginNameText= '<img id="account_image" src="img/account.png"> <span id="account_name">'.$conn->getSession('username').'</span>';
//        session_regenerate_id(true);

            $userName=$conn->getSession('username');
        
        $logged_in=true;

    }
}
else {
    $LoginNameText= '<img id="account_image" src="img/account.png"> <span id="account_name">'.$_COOKIE["username"].'</span>';
    $logged_in=true;

    $userName=$_COOKIE['username'];
    
    if (!isset($_SESSION["username"]))
        $conn->setSession('username', $_COOKIE["username"]);
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

    $MainPage->showFooter(true,true);

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



