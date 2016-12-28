<?php
/**
 * File: vote.php
 * Created by rocean
 * Date: 27/12/16
 * Time: 23:23
 *
 * Σελίδα με την λίστα που ψηφίζουν οι χρήστες πιο τραγούδι θα παίξει επόμενο
 *
 */

//set_time_limit(0);
//ini_set('memory_limit','1024M');

require_once ('libraries/common.inc.php');

session_start();

?>

    <script type="text/javascript">

        var AJAX_path="<?php echo AJAX_PATH; ?>";  // ο κατάλογος των AJAX files

        // Τα κείμενα του site παιρνούνται στην javascript
        var phrases=<?php echo json_encode($lang->getPhrasesTable()); ?>;
    
    </script>
    
<?php

$MainPage = new Page();

// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('libraries/jquery.min.js', 'libraries/vote.js');    // javascript

$css='styles/vote.css';   // css

$MainPage->setScript($scripts);
$MainPage->setCSS($css);

$MainPage->showHeader();

?>

<section>
    <article>
        <div id="playlist_container">

                    <?php

                        $_SESSION['PlaylistCounter']=0;

                    
                        if($_SESSION['PlaylistCounter']==0) {
                            $_SESSION['condition']=null;   // Μηδενίζει το τρέχον search query
                            $_SESSION['arrayParams']=null;
                            OWMP::getPlaylist(null,0,PLAYLIST_LIMIT,null,null,null,true,true);
                        }
                        else {
                            ?>

                            <div id="playlist_content"></div>

                            <?php
                        }

                    ?>

        </div>
    </article>
</section>



<?php

$MainPage->showFooter();