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

    private static $nav_list = array();

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    function showHeader()
    {

        ?>

        <!DOCTYPE html>
        <HTML>
        <head>
            <meta charset="utf-8">

            <link rel="stylesheet" href="styles/main.css">
            

            <title><?php echo $this->tittle; ?></title>

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
                    echo '<script ' . $a . '></script>';
                }
            ?>
            

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

    function showFooter()
    {
        ?>

        <footer>
            <span><?php echo __('footer_text'); ?></span>
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


    function MakeForm($name, $form_elements)
    {
        ?>
        <form class="validate-form" id="<?php echo $name; ?>" name="<?php echo $name; ?>">
            <?php
            foreach ($form_elements as $item) {
                ?>
                
                <div class="formRow">
                    <label for="<?php echo $item['name']; ?>"><?php if($item['type']=='checkbox') echo $item['fieldtext']; ?></label>
                    <input type="<?php echo $item['type']; ?>"
                           <?php if(!$item['onclick']=='') echo 'onClick='.$item['onclick']; ?>
                           <?php if(!$item['name']=='') echo 'id='.$item['name']; ?>
                           <?php if(!$item['name']=='') echo 'name='.$item['name']; ?>
                           <?php if(!$item['value']=='')echo 'value='.$item['value']; ?>
                           <?php if(!$item['maxlength']=='')echo 'maxlength='.$item['maxlength']; ?>
                           <?php if(!$item['pattern']=='')echo 'pattern='.$item['pattern']; ?>
                           <?php if(!$item['title']=='')echo 'title="'.$item['title'].'"'; ?>
                           <?php if($item['required']=='yes') echo ' required '; ?>
                           <?php echo 'placeholder="'.$item['fieldtext'].'"'; ?>
                    >
                </div>
 
                <?php
            }
            ?>

        </form>

        <?php
    }
    
    
    public function showMainBar ($leftSideText,$rightSideText) {
    ?>
        <header>
            <div id="LeftSide">
                <div class="mainbarcontent">
                    <?php echo $leftSideText; ?>
                </div>
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


    // Τυπώνει την λίστα με τα Nav Items.
    static function NavList ($NavActiveItem) {

//        if (!isset($_COOKIE['page'])) self::setNavActiveItem(1); // Σετάρει το NavActiveItem σε 1, αν δεν έχει κάποια τιμή

        self::createNavListArray(); // Δημιουργεί το array με τα nav items

        $counter=1;


        ?>
            
            <ul>
                <?php
                    foreach (self::$nav_list as $item) {
                ?>
                        <li><a <?php if($counter==$NavActiveItem) echo 'class=active'; ?>
                                href="?page=<?php echo $counter; ?>"><?php echo $item; ?></a></li>
                
                <?php

                        $counter++;
                    }
                ?>        
            </ul>

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
        setcookie('page', $NavActiveItem, time()+$expiration);

    }


    // Κόβει το $cut_string που βρίσκεται στο τέλος του $main_string
    static function cutLastString($main_string, $cut_string) {
        $result=substr($main_string,0,-strlen($cut_string));

        return $result;
    }

}