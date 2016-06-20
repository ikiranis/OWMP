<?php
/**
 * File: MainPage.php
 * Created by rocean
 * Date: 20/05/16
 * Time: 18:31
 */




function DisplayMainPage() {
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
    

        <section>
            <article>
            <?php
                switch ($NavActiveItem) {
                    case 1: OWMP::showDashboard(); break;
                    case 2: OWMP::showConfiguration(); break;
                }

            ?>
            </article>
        </section>


        <nav>

            <?php echo Page::NavList($NavActiveItem); ?>


            <div id="languages">
                <?php echo $languages_text; ?>
            </div>
        </nav>


 
    
    <?php
}