<?php

/**
 * File: page.php
 * Created by rocean
 * Date: 17/04/16
 * Time: 01:17
 * HTML Page Elements Class
 */


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
            <meta charset="utf-8">

<!--            Για να μπορεί να πηγαίνει σε fullscreen σε mobile συσκευές-->
            <meta name="mobile-web-app-capable" content="yes">
<!--            Για να κάνουν scale τα pixels στις mobile συσκευές-->
            <meta name=viewport content="width=device-width, initial-scale=1">
            
<!--            <meta http-equiv="cache-control" content="max-age=0" />-->
<!--            <meta http-equiv="cache-control" content="no-cache" />-->
<!--            <meta http-equiv="expires" content="0" />-->
<!--            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />-->
<!--            <meta http-equiv="pragma" content="no-cache" />-->


            
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

    function showFooter($showAppName,$showAppVersion)
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
                <progress id="theProgressBar" name="theProgressBar" max="100" value="0">
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

        $conn = new RoceanDB();
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


    // Κόβει το $cut_string που βρίσκεται στο τέλος του $main_string
    static function cutLastString($main_string, $cut_string) {
        $result=substr($main_string,0,-strlen($cut_string));

        return $result;
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



    //year    = $diff->format('%y');
    //month    = $diff->format('%m');
    //day      = $diff->format('%d');
    //hour     = $diff->format('%h');
    //min      = $diff->format('%i');
    //sec      = $diff->format('%s');
    // Επιστρέφει την διαφορά της $endDate με την $startDate και επιστρέφει τιμή αναλόγως το $returnedFormat
    static function dateDifference($startDate, $endDate, $returnedFormat) {
        $d_start    = new DateTime($startDate);
        $d_end      = new DateTime($endDate); // Τα παίρνουμε σε αντικείμενα
        $diff = $d_start->diff($d_end);   // Υπολογίζουμε την διαφορά

        $difference      = $diff->format($returnedFormat);    // στο format βάζουμε αναλόγως σε τι θέλουμε να πάρουμε την διαφορά

        return $difference;
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
    
    // Επιστρέφει την μετατροπή των δευτερολέπτων σε λεπτά:δευτερόλεπτα
    static function seconds2MinutesAndSeconds($timeInSeconds) {
        $timeInMinutes=(int)($timeInSeconds/60);
        $newTimeInSeconds=(int)($timeInSeconds%60);

        if($timeInMinutes<10) $timeInMinutes='0'.$timeInMinutes;
        if($newTimeInSeconds<10) $newTimeInSeconds='0'.$newTimeInSeconds;

        $timeArray= $timeInMinutes.' : '.$newTimeInSeconds;

        return $timeArray;
    }


    // Ελέγχει αν υπάρχει το $progressName στον πίνακα progress
    static function checkIfProgressNameExists($progressName) {
        $conn = new RoceanDB();
        $conn::CreateConnection();

        $sql = 'SELECT progressName FROM progress WHERE progressName=?';
        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($progressName));

        if($item=$stmt->fetch(PDO::FETCH_ASSOC)) {

            $result=true;
        }

        else $result=false;


        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }
    
    // Δημιουργεί ένα $progressName στον πίνακα progress
    static function createProgressName($progressName) {
        $conn = new RoceanDB();
        $conn::CreateConnection();

        $sql = 'INSERT INTO progress (progressName) VALUES(?)';
        $stmt = RoceanDB::$conn->prepare($sql);


        if($stmt->execute(array($progressName)))

            $result=true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Καταχωρεί το ποσοστό εξέλιξης progress
    static function updatePercentProgress($progress) {
        $progressUpdateArray=array ($progress, 'progressInPercent');
        RoceanDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Ενημερώνει με 1 (true) ή 0 (false) το killCommand του πίνακa progress
    static function setKillCommand($theCommand) {
        $progressUpdateArray=array ($theCommand, 'killCommand');
        RoceanDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Θέτει τιμή στο currentSong
    static function setCurrentSong($theSong) {
        $progressUpdateArray=array ($theSong, 'currentSong');
        RoceanDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }

    // Δίνει timespamp τιμή στο lastMomentAlive του πίνακa progress
    static function setLastMomentAlive($operation) {
        if($operation==true)
            $theTimestamp = time();
        else $theTimestamp='';
        
        $progressUpdateArray=array ($theTimestamp, 'lastMomentAlive');
        RoceanDB::updateTableFields('progress', 'progressName=?', array('progressValue'), $progressUpdateArray);
    }
    
    // Επιστρέφει το killCommand από τον πίνακα progress
    static function getKillCommand() {
        if($result=RoceanDB::getTableFieldValue('progress', 'progressName=?', 'killCommand', 'progressValue'))
            return $result;
        else return false;
    }


    // Επιστρέφει το currentSong από τον πίνακα progress
    static function getCurrentSong() {
        if($result=RoceanDB::getTableFieldValue('progress', 'progressName=?', 'currentSong', 'progressValue'))
            return $result;
        else return false;
    }

    // Επιστρέφει το lastMomentAlive από τον πίνακα progress
    static function getLastMomentAlive() {
        if($result=RoceanDB::getTableFieldValue('progress', 'progressName=?', 'lastMomentAlive', 'progressValue'))
            return $result;
        else return false;
    }
    
    // Επιστρέφει το ποσοστό εξέλιξης progress
    static function getPercentProgress() {
        if($result=RoceanDB::getTableFieldValue('progress', 'progressName=?', 'progressInPercent', 'progressValue'))
            return $result;
        else return false;
    }

    //  Επιστρέφει την τρέχουσα έκδοση της εφαρμογής
//    static function getCurrentVersion() {
//        $data   = file_get_contents(README_FILE);
//        $data   = explode("\n", $data);
//
//        $CurVersion = substr($data[0], strpos($data[0], ":") + 2);
//
//        return $CurVersion;
//    }


    //  Επιστρέφει την τρέχουσα έκδοση της εφαρμογής
    static function getCurrentVersion() {
        $html = PARROT_VERSION_FILE;
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

}