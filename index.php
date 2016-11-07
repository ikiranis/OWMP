<?php

/**
 * File: index.php
 * Created by rocean
 * Date: 17/04/16
 * Time: 01:17
 */

require_once ('libraries/common.inc.php');


session_start();

require_once ('login.php');
require_once ('MainPage.php');


$MainPage = new Page();

// έλεγχος αν έχει πατηθεί link για αλλαγής της γλώσσας
if (isset($_GET['ChangeLang'])) {
    $targetPage='Location:index.php';

    $lang->change_lang($_GET['ChangeLang']);

    header($targetPage);
}

if (isset($_GET['logout'])) {
    RoceanDB::insertLog('User Logout'); // Προσθήκη της κίνησης στα logs
    logout();

}

// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('src=libraries/jquery.min.js',   // jquery
    'src=libraries/scripts.js',    // my scripts
    'src=libraries/details.js',    // polyfile για το summary/details
    'src=libraries/jquery.validate.min.js',      // extension του jquery για form validation
    'src=libraries/pattern.js');   // extension για το validate. ενεργοποιεί το validation των patterns

$MainPage->setScript($scripts);

$MainPage->showHeader();

//$languages_text=$lang->print_languages('lang_id',' ',true,false);

$logged_in=false;

// Περνάει βασικές μεταβλητές στην javascript
?>

    <script type="text/javascript">

        var AJAX_path="<?php echo AJAX_PATH; ?>";  // ο κατάλογος των AJAX files
        var DIR_PREFIX="<?php echo DIR_PREFIX; ?>";
        var Album_covers_path="<?php echo ALBUM_COVERS_DIR; ?>";
        var WebFolderPath="<?php echo WEB_FOLDER_PATH; ?>";
        var ParrotVersionFile="<?php echo PARROT_VERSION_FILE; ?>";
        var AppVersion="<?php echo APP_VERSION; ?>";

    </script>

<?php

// Έλεγχος αν υπάρχει cookie. Αν δεν υπάρχει ψάχνει session
if(!$conn->CheckCookiesForLoggedUser()) {
    if (isset($_SESSION["username"]))
    {
        $LoginNameText= '<img id=account_image src=img/account.png> <span id=account_name>'.$conn->getSession('username').'</span>';
//        session_regenerate_id(true);

        $logged_in=true;

    }
}
else {
    $LoginNameText= '<img id=account_image src=img/account.png> <span id=account_name>'.$_COOKIE["username"].'</span>';
    $logged_in=true;
    
    if (!isset($_SESSION["username"]))
        $conn->setSession('username', $_COOKIE["username"]);
}


if($logged_in)
    $LoginNameText.=' <span id=logout><a href=?logout=true title='.__('logout').'><img src=img/exit.png></a></span>';

$timediv='<div id=SystemTime><img src=img/time.png><span id="timetext"></span></div>';

if($logged_in) {
    $MainPage->showMainBar($timediv, $LoginNameText);

    // Αν η σελίδα δεν έχει τρέξει την τελευταία μέρα
    if(Page::checkNewPageRunning()) {
        if(!RoceanDB::checkMySQLEventScheduler()) {   // Αν δεν είναι ενεργοποιημένος ήδη ο event scheduler
            RoceanDB::enableMySQLEventScheduler();   // Ενεργοποιεί τα scheduler events στην mysql
        }
    }
}



if(!$logged_in) {
    if ($conn->CheckIfThereIsUsers())
        showLoginWindow();
    else ShowRegisterUser();
}


if($logged_in) DisplayMainPage();


if($logged_in)
    $MainPage->showFooter();



// Δημιουργεί event που σβήνει logs που είναι παλιότερα των 30 ημερών και τρέχει κάθε μέρα
//$eventQuery='DELETE FROM logs WHERE log_date<DATE_SUB(NOW(), INTERVAL 30 DAY)';
//RoceanDB::createMySQLEvent('logsManage', $eventQuery, '1 DAY');



