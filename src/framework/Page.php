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

        <BODY>

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


        </BODY>
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
            </div>

            <div id="logprogress">
                <input type="button" id="killCommand_img" class="button_img"
                       title="<?php echo __('kill_process'); ?>"
                       onclick="sendKillCommand();"">
                <progress id="theProgressBar" max="100" value="0">
                </progress>
                <span id="theProgressNumber"></span>
            </div>
            
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

        $conn = new MyDB();
        $UserGroupID=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης


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
                                <li><a <?php if ($counter == $NavActiveItem) echo 'class=active'; ?>
                                        href="?page=<?php echo $counter; ?>"><?php echo $item; ?></a></li>

                                <?php
                            }

                            if ($targetPage == 'window') {
                                ?>
                                <li>
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
            if( !MyDB::checkIfUserIsLegit() ) {
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


    // Εισάγει τις αρχικές τιμές στον πίνακα Options και στον progress
    static function startBasicOptions()
    {

        $conn = new MyDB();

//        if(!$conn->getOption('interval_value'))
//            $conn->createOption('interval_value', '5', 1, 0);

//        if(!$conn->getOption('mail_host'))
//            $conn->createOption('mail_host', 'smtp.gmail.com', 1, 0);
//
//        if(!$conn->getOption('mail_username'))
//            $conn->createOption('mail_username', 'username', 1, 0);
//
//        if(!$conn->getOption('mail_password')) {
//            $conn->createOption('mail_password', '12345678',1,1);
//
//        if(!$conn->getOption('mail_from'))
//            $conn->createOption('mail_from', 'username@mail.com', 1, 0);
//
//        if(!$conn->getOption('mail_from_name'))
//            $conn->createOption('mail_from_name', 'name', 1, 0);

        // Οι αρχικές τιμές στον πίνακα options
        if(!$conn->getOption('convert_alac_files'))
            $conn->createOption('convert_alac_files', 'false', 1, 0);

        if(!$conn->getOption('playlist_limit'))
            $conn->createOption('playlist_limit', '150', 1, 0);

        if(!$conn->getOption('dir_prefix'))
            $conn->createOption('dir_prefix', '/', 1, 0);

        if(!$conn->getOption('syncItunes'))
            $conn->createOption('syncItunes', 'false', 1, 0);

        if(!$conn->getOption('date_format'))
            $conn->createOption('date_format', 'Y-m-d', 1, 0);

        if(!$conn->getOption('icecast_server'))
            $conn->createOption('icecast_server', '0.0.0.0:8000', 1, 0);

        if(!$conn->getOption('icecast_mount'))
            $conn->createOption('icecast_mount', 'listen', 1, 0);

        if(!$conn->getOption('icecast_user'))
            $conn->createOption('icecast_user', 'user', 1, 0);

        if(!$conn->getOption('icecast_pass'))
            $conn->createOption('icecast_pass', 'pass', 1, 1);

        if(!$conn->getOption('icecast_enable'))
            $conn->createOption('icecast_enable', 'false', 1, 0);

        if(!$conn->getOption('jukebox_enable'))
            $conn->createOption('jukebox_enable', 'false', 1, 0);

        if(!$conn->getOption('default_language'))
            $conn->createOption('default_language', 'en', 1, 0);

        if(!$conn->getOption('youtube_api'))
            $conn->createOption('youtube_api', 'AIzaSyArMqCdw1Ih1592YL96a2Vdo5sGo6vsS4A', 1, 0);

        if(!$conn->getOption('play_percentage'))
            $conn->createOption('play_percentage', '20', 1, 0);




        // Οι αρχικές τιμές στον πίνακα progress
        if(!Progress::checkIfProgressNameExists('progressInPercent'))
            Progress::createProgressName('progressInPercent');

        if(!Progress::checkIfProgressNameExists('progressMessage'))
            Progress::createProgressName('progressMessage');

        if(!Progress::checkIfProgressNameExists('killCommand')) {
            Progress::createProgressName('killCommand');
            Progress::setKillCommand('0');
        }

        if(!Progress::checkIfProgressNameExists('lastMomentAlive')) {
            Progress::createProgressName('lastMomentAlive');
            Progress::setLastMomentAlive(true);
        }

        if(!Progress::checkIfProgressNameExists('currentSong')) {
            Progress::createProgressName('currentSong');
        }


        // TODO να σβηστεί μετά από καιρό αυτό. Γράφτηκε Δεκέμβριο 2016
        // Σβήσιμο της εγγραφής που έχει μπει από παλιότερες εκδόσεις
        if($conn->getOption('web_folder_path'))
            $conn->deleteRowFromTable('options', 'option_name', 'web_folder_path');

    }


    // TODO δεν πρέπει να δουλεύει σωστά το update κάποιες φορές
    // Ξαναπαίρνει τιμή το session του username, για να γίνει update
    static function updateUserSession() {
        $conn = new MyDB();

        // Αν υπάρχει session του user τότε ξαναπαίρνει την ίδια τιμή, ώστε να γίνει update
        if (isset($_SESSION["username"])) {
            $conn->setSession('username', $conn->getSession('username'));
        } else {
            if ($conn->CheckCookiesForLoggedUser()) { //  Έλεγχος αν υπάρχει cookie και παίρνει το username από εκεί
                $conn->setSession('username', MyDB::getACookie('username') );
            }

        }

    }

}