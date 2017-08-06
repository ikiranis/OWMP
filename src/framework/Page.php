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

        if(isset($_GET['page'])) {
            $NavActiveItem=$_GET['page'];
            Page::setNavActiveItem($_GET['page']);

        }
        else if(isset($_COOKIE['page'])) {
            $NavActiveItem=$_COOKIE['page'];
            Page::setNavActiveItem($_COOKIE['page']);
        }

        if(!isset($NavActiveItem)) $NavActiveItem=1;

        global $lang;

        $languages_text=$lang->print_languages('lang_id',' ',true,false);

        ?>

        <aside class="bgc2 c7">

            <?php OWMP::showVideo(); ?>

        </aside>


        <section class="bgc2 c7">
            <article>
                <?php
                switch ($NavActiveItem) {
                    case 1: OWMP::showPlaylistWindow(0,PLAYLIST_LIMIT); break;
                    case 2: OWMP::showConfiguration(); break;
                    case 3: OWMP::showSynchronization(); break;
                    case 4: OWMP::showLogs(); break;
                    case 5: OWMP::showHelp(); break;
                }

                ?>
            </article>

            <div class="o-resultsContainer bgc3 isHidden c8">
                <div class="o-resultsContainer_text"></div>
                <input type="button" class="o-resultsContainer_closeContainer myButton"
                       value="<?php echo __('close_text'); ?>"
                       onclick="closeWindow('.o-resultsContainer');" >
            </div>

        </section>

        <nav>

            <?php echo Page::NavList($NavActiveItem,'window'); ?>


            <div id="languages">
                <?php echo $languages_text; ?>
            </div>

            <div id="TotalInPlaylist"><span id="TotalNumberInPlaylist"><?php echo $_SESSION['$countThePlaylist']; ?></span> <?php echo __('items_in_playlist'); ?></div>
        </nav>

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
    <HTML xmlns="http://www.w3.org/1999/html" class="bgc1">
        <head>

<!--            <link id="appIcon" rel="apple-touch-icon" type="image/png" href="">-->
<!--            <link id="theFavIcon" rel="shortcut icon" type="image/png" href="">-->
            <link id="theFavIcon" rel="icon" type="image/png" href="">

            <meta charset="utf-8">

<!--            Για να μπορεί να πηγαίνει σε fullscreen σε mobile συσκευές-->
            <meta name="mobile-web-app-capable" content="yes">

<!--            Για να κάνουν scale τα pixels στις mobile συσκευές-->
            <meta name=viewport content="width=device-width, initial-scale=0.6">
            
<!--            <meta http-equiv="cache-control" content="max-age=0" />-->
            <meta http-equiv="cache-control" content="no-cache" />
            <meta http-equiv="expires" content="0" />
<!--            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />-->
            <meta http-equiv="pragma" content="no-cache" />


            
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

            <title><?php echo $this->tittle; ?></title>


        </head>

        <body class="bgc1">

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
        } else $this->script[] = $script;
    }
        
    // Δέχεται array από strings ή σκέτο string
    function setCSS($css)
    {
        if (is_array($css)) {
            foreach ($css as $item) {
                $this->css[] = $item;
            }
        } else $this->css[] = $css;
    }


    // Εμφανίζει τα στοιχεία του footer
    function showFooter($showAppName,$showAppVersion,$showMobileVersion)
    {
        ?>

        <footer>
            <?php
                if($showAppName) {
            ?>
                    <span><a href="<?php echo WEB_PAGE_URL; ?>"><?php echo __('footer_text'); ?></a></span>
            <?php
                }
            
                if($showAppVersion) {
                    ?>
                    <span id="AppVersionContainer">
                        <span id="AppVersion"><?php echo __('app_version') . ': ' . APP_VERSION; ?></span>
                        <span id="checkCurrentVersion"></span>
                    </span>
                    <?php
                }
            ?>
        </footer>
        
        <?php 
            if($showMobileVersion) {
                ?>
                <div id="mobileVersion">
                    <span id="mobileVersionText">
                        <?php
                        if(!isset($_GET['mobile'])) {
                            echo '<a href="?mobile=true">'.__('mobile_version').'</a>';
                        } else {
                            echo '<a href="'.HTTP_TEXT.$_SERVER["HTTP_HOST"].PROJECT_PATH.'">'.__('desktop_version').'</a>';
                        }
                        ?>
                    </span>
                </div>
        <?php
            }
        ?>


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
    public function showMainBar ($leftSideText,$rightSideText) {
    ?>
        <header>
            <div id="LeftSide">
                <div class="mainbarcontent">
                    <?php echo $leftSideText; ?>
                </div>
                <input type="button" class="o-imageButton o-imageButton_toggleResultsContainer" onclick="toggleResultsContainer();">
                <input type="button" class="o-imageButton o-imageButton_toggleResultsContainer" onclick="initProgressAnimation();">
            </div>

            <div id="logprogress">
                <input type="button" id="killCommand_img" class="button_img"
                       title="<?php echo __('kill_process'); ?>"
                       onclick="sendKillCommand();"">
                <progress id="theProgressBar" max="100" value="0">
                </progress>
                <span id="theProgressNumber"></span>
            </div>

            <div id="o-progressAnimation_container"></div>

            <div class="o-resultsContainer_loadingIcon"></div>
            
            <div id="RightSide" >
                <div class="mainbarcontent">
                    <?php echo $rightSideText; ?>
                </div>
            </div>

            
        </header>


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
    static function NavList ($NavActiveItem, $targetPage) {

//        if (!isset($_COOKIE['page'])) self::setNavActiveItem(1); // Σετάρει το NavActiveItem σε 1, αν δεν έχει κάποια τιμή

        self::createNavListArray(); // Δημιουργεί το array με τα nav items

        $counter=1;

        global $adminNavItems;

        $user = new User();
        $conn = new MyDB();

        $UserGroupID=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης


        ?>
            
            <ul>
                <?php
                    foreach (self::$nav_list as $item) {

                        // έλεγχος αν ο χρήστης είναι admin σε items που πρέπει να είναι admin
                        if (in_array($counter, $adminNavItems)) {
                            if ($UserGroupID == 1) $displayOK = true;
                            else $displayOK = false;
                        }  else $displayOK=true;
                        
                        if($displayOK) {
                            if ($targetPage == 'page') {
                                ?>
                                <li class="c5 bgc2"><a <?php if ($counter == $NavActiveItem) echo 'class=active'; ?>
                                        href="?page=<?php echo $counter; ?>"><?php echo $item; ?></a></li>

                                <?php
                            }

                            if ($targetPage == 'window') {
                                ?>
                                <li class="c5 bgc2">
                                    <a id="navID<?php echo $counter; ?>" <?php if ($counter == $NavActiveItem) echo 'class=active'; ?>
                                       onclick="DisplayWindow(<?php echo $counter; ?>, null,null);"><?php echo $item; ?></a>
                                </li>

                                <?php
                            }
                        }

                        $counter++;
                    }
                ?>        
            </ul>

        <script type="text/javascript">

            var NavLength = <?php echo $counter-1; ?>;

        </script>

        <?php

        return true;
    }
    
    static function getNavActiveItem() {
        if(!isset($_COOKIE['page'])) {
            self::setNavActiveItem(1);
            $getTheCookie=1;
        }
        else $getTheCookie=$_COOKIE['page'];

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