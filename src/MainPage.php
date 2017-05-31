<?php
/**
 * File: MainPage.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 20/05/16
 * Time: 18:31
 */

require_once('src/boot.php');

use apps4net\parrot\app\OWMP;
use apps4net\framework\Page;
use apps4net\parrot\app\OWMPElements;


function DisplayMainPage() {
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
                    case 1: OWMP::showPlaylistWindow(0,PLAYLIST_LIMIT,null); break;
                    case 2: OWMP::showConfiguration(); break;
                    case 3: OWMP::showSynchronization(); break;
                    case 4: OWMP::showLogs(); break;
                    case 5: OWMP::showHelp(); break;
                    
                }

            ?>
            </article>
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