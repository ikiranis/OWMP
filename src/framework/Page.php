<?php

/**
 * File: page.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 17/04/16
 * Time: 01:17
 *
 * HTML Page Elements Class
 */

namespace apps4net\framework;

use apps4net\parrot\app\OWMP;
use apps4net\parrot\app\OWMPElements;

class Page
{
    private $tittle;
    private $meta = array();
    private $script = array();
    private $css = array();

    private static $nav_list = array();

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    // Εμφανίζει τα elements της κεντρικής σελίδας
    public function DisplayMainPage() {
        $_SESSION['PlaylistCounter']=0;

        if (isset($_GET['page'])) {
            $NavActiveItem = $_GET['page'];
            Page::setNavActiveItem($_GET['page']);

        } else if (isset($_COOKIE['page'])) {
            $NavActiveItem = $_COOKIE['page'];
            Page::setNavActiveItem($_COOKIE['page']);
        }

        if (!isset($NavActiveItem)) $NavActiveItem = 1;

        global $lang;

        $languages_text = $lang->print_languages('lang_id',' ',true,false);

        ?>


<!--                                <div id="TotalInPlaylist"><span-->
<!--                                            id="TotalNumberInPlaylist">--><?php //echo $_SESSION['countThePlaylist']; ?><!--</span> --><?php //echo __('items_in_playlist'); ?>
<!--                                </div>-->


        <div class="row mainContent w-100 bg-light no-gutters">

            <aside class="col-xl-4 col-md-3 col-sm-12 h-100 px-4 py-3">
                <?php OWMP::showVideo(); ?>
            </aside>

            <section class="col-xl-8 col-md-9 col-sm-12 h-100">
                <article>
                    <?php
                    switch ($NavActiveItem) {
                        case 1:
                            OWMP::showPlaylistWindow(0, PLAYLIST_LIMIT);
                            break;
                        case 2:
                            OWMP::showConfiguration();
                            break;
                        case 3:
                            OWMP::showSynchronization();
                            break;
                        case 4:
                            OWMP::showLogs();
                            break;
                        case 5:
                            OWMP::showHelp();
                            break;
                    }

                    ?>
                </article>

                <div class="o-resultsContainer bgc3 isHidden c8">
                    <div class="o-resultsContainer_text"></div>
                    <input type="button" class="o-resultsContainer_closeContainer myButton"
                           value="<?php echo __('close_text'); ?>"
                           onclick="closeWindow('.o-resultsContainer');">
                </div>

            </section>


        </div>

        <div id="helpContainer" class="bgc3 c8">
            <div id="helpText"></div>
            <input type="button" id="closeHelp" name="closeHelp" class="myButton" value="<?php echo __('close_text'); ?>" onclick="closeHelp();" >
        </div>


        <?php OWMPElements::displayControls('overlay_media_controls', true); ?>

        <?php
    }

    // Εμφάνιση του header
    function showHeader()
    {

    ?>

    <!DOCTYPE html>
    <HTML xmlns="http://www.w3.org/1999/html">
        <head>

<!--            <link id="appIcon" rel="apple-touch-icon" type="image/png" href="">-->
<!--            <link id="theFavIcon" rel="shortcut icon" type="image/png" href="">-->
            <link id="theFavIcon" rel="icon" type="image/png" href="">

            <meta charset="utf-8">

<!--            Για να μπορεί να πηγαίνει σε fullscreen σε mobile συσκευές-->
            <meta name="mobile-web-app-capable" content="yes">

<!--            Για να κάνουν scale τα pixels στις mobile συσκευές-->
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


            <!--            <meta http-equiv="cache-control" content="max-age=0" />-->
            <meta http-equiv="cache-control" content="no-cache" />
            <meta http-equiv="expires" content="0" />
<!--            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />-->
            <meta http-equiv="pragma" content="no-cache" />



<!--            Bootstrap-->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
            <?php
            //  Καθορισμός των css αρχείων.
            if (isset($this->css))
                foreach ($this->css as $a) {
                    echo '<link rel="stylesheet" href="'.$a.'">';
                }

            ?>

            <?php
            //  Καθορισμός των meta. Ζητάει το string μετά το "<meta "
            if (isset($this->meta))
                foreach ($this->meta as $a) {
                    echo "<meta " . $a . ">";
                }
            ?>


            <?php
            //  Καθορισμός των scripts αρχείων. Ζητάει το string μετά το "<script "
            if (isset($this->script))
                foreach ($this->script as $a) {
                    echo '<script src=' . $a . '></script>';
                }

            ?>

            <!--            Bootstrap-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
                    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
                    crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js"
                    integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4"
                    crossorigin="anonymous"></script>


            <title><?php echo $this->tittle; ?></title>


        </head>

        <body>

        < class="container-fluid">

        <?php


    }

    // Δέχεται array από strings ή σκέτο string
    function setMeta($meta)
    {

        if (is_array($meta)) {
            foreach ($meta as $item) {
                $this->meta[] = $item;
            }
        } else $this->meta[] = $meta;
    }

    // Δέχεται array από strings ή σκέτο string
    function setScript($script)
    {
        if (is_array($script)) {
            foreach ($script as $item) {
                $this->script[] = $item;
            }
        } else {
            $this->script[] = $script;
        }
    }

    // Δέχεται array από strings ή σκέτο string
    function setCSS($css)
    {
        if (is_array($css)) {
            foreach ($css as $item) {
                $this->css[] = $item;
            }
        } else {
            $this->css[] = $css;
        }
    }


    // Εμφανίζει τα στοιχεία του footer
    function showFooter($showAppName,$showAppVersion)
    {
        ?>

            <div id="o-progressAnimation_container" class="row fixed-bottom bg-secondary w-100 mx-auto"></div>

            <footer class="row fixed-bottom bg-dark">

                <div class="col-xl-5  col-lg-4 px-4 my-auto w-100 d-none d-lg-block">
                    <?php
                    if ($showAppName) {
                        ?>
                        <span><a href="<?php echo WEB_PAGE_URL; ?>"><?php echo __('footer_text'); ?></a></span>
                        <?php
                    }
                    ?>
                </div>

                <div class="col-xl-5  col-lg-6 col-md-10 my-auto text-white text-right w-100 d-none d-md-block">
                    <?php
                    if ($showAppVersion) {
                        ?>
                        <span id="AppVersionContainer">
                            <span id="AppVersion"><?php echo __('app_version') . ': ' . APP_VERSION; ?></span>
                            <span id="checkCurrentVersion"></span>
                        </span>
                        <?php
                    }
                    ?>
                </div>

                <div class="col-xl-2 col-lg-2  col-md-2 my-auto text-white text-center w-100">
                    <span id="SystemTime"><span id="timetext"></span></span>
                </div>
            </footer>

<!--        --><?php
//            if($showMobileVersion) {
//                ?>
<!--                <div id="mobileVersion">-->
<!--                    <span id="mobileVersionText">-->
<!--                        --><?php
//                        if(!isset($_GET['mobile'])) {
//                            echo '<a href="?mobile=true">'.__('mobile_version').'</a>';
//                        } else {
//                            echo '<a href="'.HTTP_TEXT.$_SERVER["HTTP_HOST"].PROJECT_PATH.'">'.__('desktop_version').'</a>';
//                        }
//                        ?>
<!--                    </span>-->
<!--                </div>-->
<!--        --><?php
//            }
//        ?>

        </div>

        </body>
        </HTML>

        <?php
    }


// Function δημιουργίας φόρμας    
// Δέχεται τιμές όταν καλείται με αυτόν τον τρόπο
// $FormElementsArray= array (
//      array('name' => 'email', 'fieldtext' => 'E-mail', 'type' => 'text'),
//      array('name' => 'password', 'fieldtext' => 'Password', 'type' => 'password')
// );
    function MakeForm($name, $form_elements, $splitToDetails)
    {
        $splitted=false;
        ?>
        <form class="validate-form" id="<?php echo $name; ?>" name="<?php echo $name; ?>">
            <?php
            foreach ($form_elements as $item) {
                if(!isset($item['readonly']))
                    $item['readonly']='no';

                if( (isset($item['allwaysview']) && $item['allwaysview']=='no') && ($splitToDetails==true && $splitted==false) )
                    if($item['disabled']=='yes' || $item['readonly']=='yes') {
                         $splitted=true;
                    ?>
                        <details>
                            <summary>
                                <?php echo __('tag_details'); ?>
                            </summary>


                    <?php
                    }


                ?>
                <div class="formRow" id="<?php echo $item['name']; ?>_div">
                    <label for="<?php echo $item['name']; ?>"><?php if ( ($item['type']=='checkbox')  ) echo $item['fieldtext']; ?></label>
                    <?php
                        if($item['type']=='select') {
                            ?>
                                <select <?php if (!$item['name'] == '') echo 'id=' . $item['name']; ?>
                                        <?php if (!$item['name'] == '') echo 'name=' . $item['name']; ?>
                                    <?php if (isset($item['disabled']) && $item['disabled'] == 'yes') echo ' disabled '; ?>
                                    <?php if (isset($item['readonly']) && $item['readonly'] == 'yes') echo ' readonly '; ?>

                                >
                                    <?php
                                        if (isset($item['options']))
                                            foreach ($item['options'] as $option)
                                            {
                                                ?>
                                                    <option value="<?php echo $option['value']; ?>" >
                                                        <?php echo $option['name']; ?>
                                                    </option>
                                                <?php
                                            }
                                    ?>

                                </select>

                            <?php
                        }
                    else {

                        ?>
                        <input type="<?php echo $item['type']; ?>"
                            <?php if (isset($item['onclick'])) echo 'onClick=' . $item['onclick']; ?>
                            <?php if (isset($item['name'])) echo 'id=' . $item['name']; ?>
                            <?php if (isset($item['name'])) echo 'name=' . $item['name']; ?>
                            <?php if (isset($item['className'])) echo 'class=' . $item['className']; ?>
                            <?php if (isset($item['value'])) echo 'value=' . $item['value']; ?>
                            <?php if (isset($item['maxlength'])) echo 'maxlength=' . $item['maxlength']; ?>
                            <?php if (isset($item['pattern'])) echo 'pattern=' . $item['pattern']; ?>
                            <?php if (isset($item['title'])) echo 'title="' . $item['title'] . '"'; ?>
                            <?php if (isset($item['required']) && $item['required'] == 'yes') echo ' required '; ?>
                            <?php if (isset($item['disabled']) && $item['disabled'] == 'yes') echo ' disabled '; ?>
                            <?php if (isset($item['readonly']) && $item['readonly'] == 'yes') echo ' readonly '; ?>
                            <?php if (isset($item['max'])) echo 'max="' . $item['max'] . '"'; ?>
                            <?php if (isset($item['min'])) echo 'min="' . $item['min'] . '"'; ?>
                            <?php if (isset($item['step'])) echo 'step="' . $item['step'] . '"'; ?>
                            <?php if($item['type']=='range') echo 'oninput="printValue('.$item['name'].','.$item['name'].'_output)"'; ?>
                            <?php if (isset($item['ticks'])) echo 'list=ticks'; ?>
                            <?php echo 'placeholder="' . $item['fieldtext'] . '"'; ?>
                        >
                        <?php

                        if($item['type']=='range') {
                            ?>
                            <output for="<?php echo $item['name']; ?>" id="<?php echo $item['name']; ?>_output"><?php echo $item['value']; ?></output>
                            <?php
                        }

                        if (isset($item['ticks'])) {
                            ?>
                            <datalist id="ticks">
                            <?php
                            foreach ($item['ticks'] as $tick) {
                                ?>
                                    <option> <?php echo $tick; ?> </option>

                                <?php
                            }
                            ?>
                            </datalist>
                            <?php
                        }
                    }

                    ?>
                </div>

                <?php
            }

                if( $splitToDetails==true ) {
                ?>
                            </details>

                <?php
                }


        ?>

        </form>

        <?php
    }

    // TODO να βγάλω το logprogress εκτός να το προσθέτει δυναμικά
    public function showMainBar ($rightSideText) {
        global $lang;

        $languages_text = $lang->print_languages('lang_id',' ',true,false);

        if (isset($_GET['page'])) {
            $NavActiveItem = $_GET['page'];
            Page::setNavActiveItem($_GET['page']);

        } else if (isset($_COOKIE['page'])) {
            $NavActiveItem = $_COOKIE['page'];
            Page::setNavActiveItem($_COOKIE['page']);
        }

        if (!isset($NavActiveItem)) {
            $NavActiveItem = 1;
        }

    ?>
        <nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark" >

                <div class="navbar-brand">
                    <?php echo $languages_text; ?>
                </div>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
                        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <?php
                    Page::NavList($NavActiveItem, 'window');
                ?>

            <div class="navbar-text d-none d-md-block" >
                <span class="o-resultsContainer_iconContainer isHidden">
                    <input type="button" class="o-imageButton--large o-imageButton_toggleResultsContainer"
                           title="<?php echo __('display_activity'); ?>"
                           onclick="toggleResultsContainer();">
                </span>

                <span class="o-resultsContainer_killCommandContainer isHidden">
                    <input type="button" class="o-imageButton--large o-imageButton_killCommand"
                           title="<?php echo __('kill_process'); ?>"
                           onclick="sendKillCommand();">
                </span>

                <?php echo $rightSideText; ?>
            </div>


        </nav>




    <?php
    }

    // Δημιουργεί τον πίνακα των επιλογών με βάση τις τιμές στο αντίστοιχο language file
    public function createNavListArray() {
        for($i=1; $i<=NAV_LIST_ITEMS; $i++) {
            $nav_item='nav_item_'.$i;
            self::$nav_list[$i]=__($nav_item);
        }
    }


    // Τυπώνει την λίστα με τα Nav Items. Αν $targetPage=page αλλάζει τα περιεχόμενα όλης της σελίδας.
    // Αν είναι window μόνο στο κεντρικό window
    /**
     * Τυπώνει την λίστα με τα Nav Items. Αν $targetPage=page αλλάζει τα περιεχόμενα όλης της σελίδας.
     * Αν είναι window μόνο στο κεντρικό window
     *
     * @source https://getbootstrap.com/docs/4.0/components/navbar/
     *
     * @param $NavActiveItem
     * @param $targetPage
     */
    static function NavList ($NavActiveItem, $targetPage) {

//        if (!isset($_COOKIE['page'])) self::setNavActiveItem(1); // Σετάρει το NavActiveItem σε 1, αν δεν έχει κάποια τιμή

        self::createNavListArray(); // Δημιουργεί το array με τα nav items

        $counter=1;

        global $adminNavItems;

        $user = new User();
        $conn = new MyDB();

        $UserGroupID=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης


        ?>

        <div class="collapse navbar-collapse align-items-end bg-dark navbar-dark px-3 py-1" id="navbarNavAltMarkup">
            <div class="navbar-nav nav-pills">
                <?php

                    foreach (self::$nav_list as $item) {

                        // έλεγχος αν ο χρήστης είναι admin σε items που πρέπει να είναι admin
                        if (in_array($counter, $adminNavItems)) {
                            if ($UserGroupID == 1) {
                                $displayOK = true;
                            } else {
                                $displayOK = false;
                            }
                        }  else {
                            $displayOK=true;
                        }

                        if($displayOK) {
                            if ($targetPage == 'page') {
                                ?>
                                    <a href="?page=<?php echo $counter; ?>" class="nav-item nav-link <?php echo ($counter == $NavActiveItem) ? 'active' : ''; ?>">
                                        <?php echo $item; ?>
                                    </a>
                                <?php
                            }

                            if ($targetPage == 'window') {
                                ?>
                                    <a id="navID<?php echo $counter; ?>" class="nav-item nav-link <?php echo ($counter == $NavActiveItem) ? 'active' : ''; ?>"
                                       onclick="DisplayWindow(<?php echo $counter; ?>, null,null);"><?php echo $item; ?></a>
                                <?php
                            }
                        }

                        $counter++;
                    }
                ?>
            </div>
        </div>

        <script>

            var NavLength = <?php echo $counter-1; ?>;

        </script>

        <?php

//        return true;
    }

    static function getNavActiveItem() {
        if(!isset($_COOKIE['page'])) {
            self::setNavActiveItem(1);
            $getTheCookie=1;
        } else {
            $getTheCookie=$_COOKIE['page'];
        }

        return $getTheCookie;
    }

    static function setNavActiveItem($NavActiveItem) {
        $expiration=60*30;
        setcookie('page', $NavActiveItem, time()+$expiration, PROJECT_PATH);

    }


    // Ελέγχει αν η σελίδα έχει τρέξει πρόσφατα. Επιστρέφει false αν η σελίδα έχει τρέξει το
    // τελευταίο μισάωρο (χρόνος ζωής του session). Αν όχι επιστρέφει true
    static function checkNewPageRunning() {

        if(isset($_SESSION['PageRunning'])) { // Αν υπάρχει ήδη η session
            $result = false;
        }
        else {
            $_SESSION['PageRunning']=date('Y-m-d H:i:s');
            $result=true;
        }

        return $result;

    }


    // Δημιουργεί εγγραφή στο crontab. Προσθέτει το demon.php
    static function createCrontab() {
        file_put_contents('/tmp/crontab.txt', '* * * * * php '.$_SERVER['DOCUMENT_ROOT'].'/demon.php'.PHP_EOL);
        shell_exec('crontab /tmp/crontab.txt');

        $output = shell_exec('crontab -l');
        return $output;
    }

    // Επιστρέφει το crontab που ισχύει
    static function getCrontab() {
        $output = shell_exec('crontab -l');

        if($output)
            $result=$output;
        else $result=false;

        return $result;
    }


    //  Επιστρέφει την τρέχουσα έκδοση της εφαρμογής
    static function getCurrentVersion() {
        $html = APP_VERSION_FILE;
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            $result = $decoded['app_version'];
            return $result;
        } else return false;
    }


    // Εμφανίζει εικονίδιο βοήθειας και αν πατηθεί εμφανίζει box με text το $helpText
    static function getHelp($helpText) {
        ?>

            <input type="button" class="help_button app_icon"
                   title="<?php echo __('help_text_icon'); ?>"
                   onclick="getHelp('<?php echo $helpText;  ?>');">

        <?php
    }


    // Έλεγχος αν το ajax request γίνεται από το ίδιο το site και όχι εξωτερικά
    static function checkValidAjaxRequest($checkLogin) {

        $requestAJAXValid = false;

        // Έλεγχος αν είναι login. Αν δεν είναι τερματίζει την εκτέλεση
        if($checkLogin) {
            if( !User::checkIfUserIsLegit() ) {
                die('Invalid AJAX request');
            }
        }


        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
                $requestAJAXValid = true;
            } else {
                $requestAJAXValid = false;
            }


        } else {
            $requestAJAXValid = false;
        }

        if(!$requestAJAXValid) {
            trigger_error('Invalid AJAX request from '.$_SERVER['REMOTE_ADDR']);
            die('Invalid AJAX request');
        }

    }


    // TODO δεν πρέπει να δουλεύει σωστά το update κάποιες φορές
    // Ξαναπαίρνει τιμή το session του username, για να γίνει update
    static function updateUserSession() {
        $conn = new MyDB();
        $user = new User();

        // Αν υπάρχει session του user τότε ξαναπαίρνει την ίδια τιμή, ώστε να γίνει update
        if (isset($_SESSION["username"])) {
            $conn->setSession('username', $conn->getSession('username'));
        } else {
            if ($user->CheckCookiesForLoggedUser()) { //  Έλεγχος αν υπάρχει cookie και παίρνει το username από εκεί
                $conn->setSession('username', MyDB::getACookie('username') );
            }

        }

    }

}