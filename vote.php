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


require_once ('libraries/common.inc.php');

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

// Αποθηκεύει την IP σε session για τις περιπτώσεις που αλλάζει συνέχεια η IP του χρήστη (π.χ. σε 3g network)
if(!isset($_SESSION['user_IP'])) {
    $_SESSION['user_IP'] = $_SERVER['REMOTE_ADDR'];
}


// Τίτλος της σελίδας
$MainPage->tittle = APP_NAME;

$scripts=array ('libraries/jquery.min.js', 'libraries/vote.js');    // javascript

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


$MainPage->showFooter(true, false, false);

?>

<input type="button" class="myButton" name="getVotes" id="getVotes"
       value="get Votes" onclick="getSongVotes();">

    <div id="votesList">
        <div id="votesListText"></div>
        <div id="browseButtons">
            <input type="button" id="closeVotes" name="closeVotes" class="myButton" value="<?php echo __('close_text'); ?>" onclick="closeVotesWindow();" >
        </div>
    </div>

<span id="playlistCount"><?php echo $_SESSION['$countThePlaylist'].' items'; ?> </span>