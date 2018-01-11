<?php
/**
 * File: vote.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 27/12/16
 * Time: 23:23
 *
 * Σελίδα με την λίστα που ψηφίζουν οι χρήστες πιο τραγούδι θα παίξει επόμενο
 *
 */

use apps4net\framework\Language;
use apps4net\framework\Page;
use apps4net\parrot\app\PlaylistSearch;

require_once('src/boot.php');

//  Αν είναι απενεργοποιημένο το jukebox τότε σταματάει την εκτέλεση της σελίδας
if(!JUKEBOX_ENABLE) {
    die(__('service_disabled'));
}

$lang = new Language();

$phrasesForJavascript=json_encode($lang->getPhrasesTable());

$languages_text=$lang->print_languages('lang_id',' ',true,false);

// έλεγχος αν έχει πατηθεί link για αλλαγής της γλώσσας
if (isset($_GET['ChangeLang'])) {
    $targetPage='Location:vote.php';

    $lang->change_lang($_GET['ChangeLang']);

    header($targetPage);
}

session_start();

?>

    <script type="text/javascript">

        var AJAX_path="<?php echo AJAX_PATH; ?>";  // ο κατάλογος των AJAX files

        // Τα κείμενα του site παιρνούνται στην javascript
        var phrases=<?php echo $phrasesForJavascript; ?>;
    
    </script>
    
<?php

$MainPage = new Page();
$playlist = new PlaylistSearch();

// Αποθηκεύει την IP σε session για τις περιπτώσεις που αλλάζει συνέχεια η IP του χρήστη (π.χ. σε 3g network)
if(!isset($_SESSION['user_IP'])) {
    $_SESSION['user_IP'] = $_SERVER['REMOTE_ADDR'];
}


// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('src/javascript/framework/jquery.min.js', 'src/javascript/app/vote.js');    // javascript

$css='styles/vote.css';   // css

$MainPage->setScript($scripts);
$MainPage->setCSS($css);

$MainPage->showHeader();

?>

<div id="languages">
    <?php echo $languages_text; ?>
</div>



<div id="currentSong">
    <span id="playing_now"><?php echo __('current_song').': '; ?></span>
    <span id="currentSongName"></span>
    <span id="currentSongArtist"></span>
</div>

<section>
    <article>
        <div id="playlist_container">

                    <?php

                        $_SESSION['PlaylistCounter']=0;

                    
                        if($_SESSION['PlaylistCounter']==0) {
                            $_SESSION['condition']=null;   // Μηδενίζει το τρέχον search query
                            $_SESSION['arrayParams']=null;

                            $playlist->fieldsArray = null;
                            $playlist->offset = 0;
                            $playlist->step = PLAYLIST_LIMIT;
                            $playlist->duplicates = null;
                            $playlist->mediaKind = null;
                            $playlist->tabID = null;
                            $playlist->loadPlaylist = true;
                            $playlist->votePlaylist = true;
                            $playlist->getPlaylist();
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

?>

<input type="button" class="myButton" name="getVotes" id="getVotes"
       value="get Votes" onclick="getSongVotes();">

    <div id="votesList">
        <div id="votesListText"></div>
        <div id="browseButtons">
            <input type="button" id="closeVotes" name="closeVotes" class="myButton" value="<?php echo __('close_text'); ?>" onclick="closeVotesWindow();" >
        </div>
    </div>

<span id="playlistCount"><?php echo $_SESSION['countThePlaylist'].' items'; ?> </span>